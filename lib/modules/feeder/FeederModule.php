<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class FeederModule extends DefaultModule
{
    private $_version;
    private $_processReportType;

    private $_report;
    private $_host;
    private $_pkgs;

    private $_report_hostname;
    private $_report_ip;
    private $_report_proxy;
    private $_report_os;
    private $_report_arch;
    private $_report_kernel;
    private $_report_type;
    private $_report_site;
    private $_report_tag;
    private $_report_pkgs;

    public function __construct(Pakiti &$pakiti)
    {
        parent::__construct($pakiti);

        $this->_host = new Host();
        $this->_report = new Report();

        # Get the version of the client
        if (($this->_version = Utils::getHttpVar(Constants::$REPORT_VERSION)) == null) {
            //TODO Change version
            //Throw exception if null
            $this->_version = "cern_1";
        }

        $this->_processReportType = Utils::getHttpVar(Constants::$REPORT_REPORT);
        if ($this->_processReportType != Constants::$STORE_ONLY && $this->_processReportType != Constants::$STORE_AND_REPORT && $this->_processReportType != Constants::$REPORT_ONLY) {
            $this->_processReportType = Constants::$STORE_ONLY;
        }

        # Get the hostname and ip of the reporting machine (could be a NAT machine)
        $this->_host->setReporterIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0");
        $this->_host->setReporterHostname(gethostbyaddr($this->_host->getReporterIp()));

        # Map variables in the report to the internal variables
        $this->doReportMapping($this->_version);

        # Set Received time
        $this->_report->setReceivedOn(time());

        # Compute hashes
        $this->_report->setHeaderHash($this->computeReportHeaderHash());
        $this->_report->setPkgsHash($this->computeReportPkgsHash());

        # Get the hostname and ip
        $this->_host->setHostname($this->_report_hostname);
        $this->_host->setIp($this->_report_ip);

        # Is the host proxy?
        if ($this->_report_proxy == Constants::$HOST_IS_PROXY) {
            $this->_report->setTroughtProxy(Constants::$HOST_IS_PROXY);
            $this->_report->setProxyHostname($this->_host->getReporterHostname());

            # Check if the proxy is authorized to send the reports
            if (!$this->checkProxyAuthz($this->_host->getReporterHostname(), $this->_host->getReporterIp())) {
                throw new Exception("Proxy " . $this->_host->getReporterHostname() . " is not authorized to send the reports");
            }
            Utils::log(LOG_DEBUG, "Proxy logging [proxy=" . $this->_host->getReporterHostname() . "] for [host=" . $this->_host->getHostname() . "]");

            # If we are in proxy mode, the reporterHostname and reporterIp will be replaced with the real hostname and ip of the client machine.
            $this->_host->setReporterHostname($this->_host->getHostname());
            $this->_host->setReporterIp($this->_host->getIp());
        } else {
            $this->_report->setTroughtProxy(Constants::$HOST_IS_NOT_PROXY);
        }

        Utils::log(LOG_INFO, "Report from [reporterHost=" . $this->_host->getReporterHostname() . ", reporterIp=" . $this->_host->getReporterIp() . ", clientVersion=" . $this->_version . "]", __FILE__, __LINE__);
    }

    public function getResult()
    {
        if(!Utils::isConnectionSecure()){
            return;
        }

        if($this->_processReportType == Constants::$STORE_ONLY){
            return "";
        }
        if($this->_processReportType == Constants::$REPORT_ONLY){
            $os = $this->_host->getOsName();
            $pkgsIds = array_map(function ($pkg) { return $pkg->getId(); }, $this->_pkgs);
        } else {
            $host = $this->getPakiti()->getManager("HostsManager")->getHostById($this->_host->getId());
            $os = $host->getOs()->getName();
            $pkgsIds = $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsIdsByHostId($host->getId());
        }

        $osGroupsIds = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupsIdsByOsName($os);
        $pkgsWithCve = $this->getPakiti()->getManager("VulnerabilitiesManager")->getVulnerablePkgsWithCveByPkgsIdsAndOsGroupsIds($pkgsIds, $osGroupsIds);

        $result = "";
        foreach ($pkgsWithCve as $pkg) {
            foreach ($pkg['CVE'] as $pkgCve) {
                foreach ($pkgCve->getTag() as $tag) {
                    $result .= $pkg["Pkg"]->getName() . "\t" .
                        $pkg["Pkg"]->getVersionRelease() . "\t" .
                        $pkg["Pkg"]->getArch() . "\t" .
                        $pkgCve->getName() . "\t" .
                        $tag->getTagName() . "\n";
                }
            }
        }
        return $result;
    }

    /*
     * Returns hostname of the reporting machine
     */
    public function getReportHost()
    {
        return $this->_host->getReporterHostname();
    }

    /*
     * Maps variables from the reports (depends on the report version) onto the local variables
     */
    private function doReportMapping($version)
    {
        switch ($version) {
            # Legacy Pakiti client
            case "4":
                $this->_report_hostname = Utils::getHttpVar(Constants::$REPORT_HOSTNAME);
                $this->_report_ip = Utils::getHttpVar(Constants::$REPORT_IP);
                $this->_report_proxy = Utils::getHttpVar(Constants::$REPORT_PROXY);
                $this->_report_os = Utils::getHttpVar(Constants::$REPORT_OS);
                $this->_report_arch = Utils::getHttpVar(Constants::$REPORT_ARCH);
                $this->_report_kernel = Utils::getHttpVar(Constants::$REPORT_KERNEL);
                $this->_report_type = Utils::getHttpVar(Constants::$REPORT_TYPE);
                $this->_report_site = Utils::getHttpVar(Constants::$REPORT_SITE);
                $this->_report_tag = Utils::getHttpVar(Constants::$REPORT_TAG);
                $this->_report_pkgs = Utils::getHttpVar(Constants::$REPORT_PKGS);
                # Report from RPM based OS: version and release are splitted by space. Debian and CERN reports separates version and release by a dash.
                # FIXME, we need set is in a changelog
                $this->_report_pkgs_format = $this->_report_type == "rpm" ? "space" : "dash";
                break;
            default: // cern_1
                # Example of the report:
                ##
                #ip: 128.142.145.197
                #ts: 1412031358
                #arch: x86_64
                #host: dpm-puppet01.cern.ch dpm-puppet01.ipv6.cern.ch
                #kernel: 2.6.32-431.3.1.el6.x86_64
                #packager: rpm
                #site: MYSITE
                #system: Scientific Linux CERN SLC release 6.5 (Carbon)
                ##
                #CERN-CA-certs 0:20140325-2.slc6 noarch

                # Map onto the variables

                # Decrypt the report
                $data = file_get_contents("php://input");
                
                // Throw Exception if input stream are empty
                if($data == ""){
                    throw new Exception("Feeder [reporterHost=" . $this->_host->getReporterHostname() . ",reporterIp=" . $this->_host->getReporterIp() . "] doesn't send any data!");
                }
                
                $tmpFileIn = tempnam("/dev/shm/", "pakiti3_IN_");
                # Store encrypted report into the file and the use openssl smime to decode it
                if (file_put_contents($tmpFileIn, $data) === FALSE) {
                    throw new Exception("Cannot write to the file '$tmpFileIn' during decoding report");
                }
                # If mime type is application/octet-stream try to decrypt data, else use data without decryption
                if (mime_content_type($tmpFileIn) == Constants::$MIME_TYPE_ENCRYPTED_REPORT) {
                    $tmpFileOut = tempnam("/dev/shm/", "pakiti3_OUT_");
                    if (system("openssl smime -decrypt -binary -inform DER -inkey " . Config::$REPORT_DECRYPTION_KEY . " -in $tmpFileIn -out $tmpFileOut") === FALSE) {
                        throw new Exception("Cannot run openssl smime on the file '$tmpFileIn'");
                    }
                    # Clean up
                    unlink($tmpFileIn);
                } else {
                    $tmpFileOut = $tmpFileIn;
                }

                $handle = fopen("$tmpFileOut", "r");
                $lineNumber = 0;
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        $lineNumber++;
                        if ($lineNumber == 1 && trim($line) != "#") {
                            throw new Exception("Bad format of the report, it should start with # '$tmpFileOut'");
                        }
                        if ($lineNumber > 1 && trim($line) == "#") {
                            # We have reached end of header
                            break;
                        }
                        # Get field name and value separatedly
                        $fields = explode(':', $line, 2);
                        switch (trim($fields[0])) {
                            case "ip":
                                $this->_report_ip = trim($fields[1]);
                                break;
                            case "arch":
                                $this->_report_arch = trim($fields[1]);
                                break;
                            # Get only the first hostname in the list, CERN sends all possible hostnames of the host
                            case "host":
                                $this->_report_hostname = trim($fields[1]);
                                break;
                            case "kernel":
                                $this->_report_kernel = trim($fields[1]);
                                break;
                            case "packager":
                                $this->_report_type = trim($fields[1]);
                                break;
                            case "site":
                                $this->_report_site = trim($fields[1]);
                                break;
                            case "tag":
                                $this->_report_tag = trim($fields[1]);
                                break;
                            case "system":
                                $this->_report_os = trim($fields[1]);
                                break;
                        }
                    }

                    while (($line = fgets($handle)) !== false) {
                        if (trim($line) == "#" || empty($line)) continue;
                        # Store packages into the internal variable
                        $this->_report_pkgs .= $line;
                    }
                } else {
                    // error opening the file.
                    throw new Exception("Cannot open file with the report '$tmpFileOut'");
                }
                fclose($handle);
                unlink($tmpFileOut);
                break;
        }
    }

    /*
     * Process the report, stores the data about the host, installed packages and report itself.
     */
    public function processReport()
    {
        if($this->_processReportType != Constants::$REPORT_ONLY){

            # If host want save report to database
            if(!$this->isHostSentNewData()){
                # If host doesn't sent new data
                $this->processReportWithSameData();
            } else {
                # If host sent new data, process report one by one
                $this->processReportWithNewData();
            }

        } else {
            # If host want only check for vulnerabilities, process one by one (because of storing pkgs)
            $this->processReportWithoutSavingToDtb();
        }
    }

    public function processReportWithSameData()
    {
        $dbManager = $this->getPakiti()->getManager("DbManager");
        $statsManager = $this->getPakiti()->getManager("StatsManager");

        # Start the transaction
        $dbManager->begin();
        try {
            # Store report
            $this->storeReport();

            # Statistics
            $statsManager->add("savedReports", 1);
            $statsManager->add("sameReports", 1);
            $statsManager->add("checkedPkgs", $this->_report->getNumOfInstalledPkgs());
        } catch (Exception $e) {
            # Rollback the transaction
            $dbManager->rollback();
            throw $e;
        }

        # Commit the transaction
        $dbManager->commit();
    }

    public function processReportWithNewData()
    {
        $dbManager = $this->getPakiti()->getManager("DbManager");
        $statsManager = $this->getPakiti()->getManager("StatsManager");

        # Parse the data
        $this->prepareReport();

        # Acquire semaphore
        $semaphore = sem_get(1);
        sem_acquire($semaphore);

        # Start the transaction
        $dbManager->begin();
        try {
            # Store Host
            $this->storeHost();

            # Store packages
            $this->storePkgs();

            # Store report
            $this->storeReport();

            # Statistics
            $statsManager->add("savedReports", 1);
            $statsManager->add("checkedPkgs", $this->_report->getNumOfInstalledPkgs());
        } catch (Exception $e) {
            # Rollback the transaction
            $dbManager->rollback();
            throw $e;
        }

        # Commit the transaction
        $dbManager->commit();

        # Release semaphore
        sem_release($semaphore);
    }

    public function processReportWithoutSavingToDtb()
    {
        $dbManager = $this->getPakiti()->getManager("DbManager");
        $statsManager = $this->getPakiti()->getManager("StatsManager");

        # Parse the data
        $this->prepareReport();

        # Acquire semaphore
        $semaphore = sem_get(1);
        sem_acquire($semaphore);

        # Start the transaction
        $dbManager->begin();
        try {

            # Store packages
            $this->storePkgs();

            # Statistics
            $statsManager->add("unsavedReports", 1);
            $statsManager->add("checkedPkgs", $this->_report->getNumOfInstalledPkgs());
        } catch (Exception $e) {
            # Rollback the transaction
            $dbManager->rollback();
            throw $e;
        }

        # Commit the transaction
        $dbManager->commit();

        # Release semaphore
        sem_release($semaphore);
    }

    /*
     * Process all received entries
     */
    public function prepareReport()
    {
        Utils::log(LOG_DEBUG, "Preparing the report", __FILE__, __LINE__);

        # Set host variables Kernel, Type
        $this->_host->setKernel($this->_report_kernel);
        $this->_host->setType($this->_report_type);
        
        # Parse the packages list
        $this->_pkgs = $this->parsePkgs($this->_report_pkgs, $this->_host->getType(), $this->_host->getKernel(), $this->_version);

        # Guess DomainName
        $this->_host->setDomainName($this->guessDomain($this->_host->getHostname()));

        # Guess OsName
        $this->_host->setOsName($this->guessOs($this->_report_os, $this->_pkgs));

        # Set ArchName
        $this->_host->setArchName($this->_report_arch);

        # Set the initial information about the report (using _pkgs)
        $this->_report->setNumOfInstalledPkgs(sizeof($this->_pkgs));

        # Set HostGroup (prefer _report_tag against _report_site)
        if($this->_report_tag != null){
            $this->_host->setHostGroupName($this->_report_tag);
        } else if($this->_report_site != null){
            $this->_host->setHostGroupName($this->_report_site);
        } else {
            $this->_host->setHostGroupName(Constants::$NA);
        }

    }

    /*
     * Store Host
     */
    public function storeHost()
    {
        Utils::log(LOG_DEBUG, "Storing host to the DB", __FILE__, __LINE__);

        # Get the hostGroupId
        $hostGroup = new HostGroup();
        $hostGroup->setName($this->_host->getHostGroupName());
        $this->getPakiti()->getManager("HostGroupsManager")->storeHostGroup($hostGroup);

        # Store Host
        $this->getPakiti()->getManager("HostsManager")->storeHost($this->_host);

        # Assign Host to host group
        $this->getPakiti()->getManager("HostGroupsManager")->assignHostToHostGroup($this->_host->getId(), $hostGroup->getId());
    }

    /*
     * Store the report
     */
    public function storeReport()
    {
        Utils::log(LOG_DEBUG, "Storing report to the DB", __FILE__, __LINE__);

        # Get number of CVEs
        $cveCount = $this->getPakiti()->getManager("CveDefsManager")->getCvesCount($this->_host);
        $this->_report->setNumOfCves($cveCount);

        # Get number of installed packages and set to the new report
        if($this->_report->getNumOfInstalledPkgs() == -1){
            $this->_report->setNumOfInstalledPkgs($this->getPakiti()->getManager("PkgsManager")->getInstalledPkgsCount($this->_host));
        }

        # Set time when report was been processed
        $this->_report->setProcessedOn(time());

        # Store report
        $this->_report = $this->getPakiti()->getManager("ReportsManager")->createReport($this->_report, $this->_host);
    }

    public function isHostSentNewData()
    {
        # Get host if exist
        $id = $this->getPakiti()->getManager("HostsManager")->getHostId($this->_host->getHostname(), $this->_host->getIp(), $this->_host->getReporterHostname(), $this->_host->getReporterIp());
        if($id != -1){
            $this->_host = $this->getPakiti()->getManager("HostsManager")->getHostById($id);
        }

        # Get the hashes of the previous report, but only for hosts already stored in the DB
        if ($this->_host->getId() != -1) {
            $lastReportHashes = $this->getPakiti()->getManager("ReportsManager")->getLastReportHashes($this->_host);

            # Check if the hashes are equals
            if (($lastReportHashes != null) 
                && (($lastReportHashes[Constants::$REPORT_LAST_HEADER_HASH] == $this->_report->getHeaderHash()) 
                    && ($lastReportHashes[Constants::$REPORT_LAST_PKGS_HASH] == $this->_report->getPkgsHash()))) {
                # Data sent by the host are the same as stored one, so we do not need to store anything
                Utils::log(LOG_INFO, "Feeder [host=" . $this->_host->getHostname() . "] doesn't send any new data, exiting...", __FILE__, __LINE__);
                return false;
            }
        }

        return true;

    }

    /*
     * Stores the report to the file for further processing (only applied in asynchronous mode).
     * In order to save resources, store directly variables from the HTTP request ($_GET or $_POST).
     */
    public function storeReportToFile()
    {
        # Create temporary file, filename mask: pakiti-report-[host]-[reportHost] and also store the timestamp to the file
        $timestamp = microtime(true);
        # Maximal number of attempts to open the file
        $count = 3;

        $filename = "pakiti-report-" . $this->_host->getHostname() . "-" . $this->_host->getReporterHostname();
        $file = Config::$BACKUP_DIR . "/" . $filename;

        if (!file_exists(Config::$BACKUP_DIR)) {
            if (!mkdir(Config::$BACKUP_DIR, 0775)) {
                Utils::log(LOG_ERR, "Failed to create " . Config::$BACKUP_DIR.  " folder, probably wrong permissions", __FILE__, __LINE__);
                exit;
            }
        }

        Utils::log(LOG_DEBUG, "Storing report [file=" . $file . "]", __FILE__, __LINE__);
        while (($reportFile = fopen($file, "w")) === FALSE) {
            $count--;

            # Wait a bit
            sleep(1);

            # Try to create the file three times, if the operation is not successfull, then throw the exception
            if ($count == 0) {
                Utils::log(LOG_ERR, "Error creating the file", __FILE__, __LINE__);
                throw new Exception("Cannot create the file containing host report [host=" . $this->_host->getHostname() .
                    ", reporterHostname=" . $this->_host->getReporterHostname() . "]");
            }
            Utils::log(LOG_ERR, "Cannot create the file, trying again ($count attempts left)", __FILE__, __LINE__);
        }

        switch ($this->_version) {
            case "4":
                # Prepare the header
                $header = Constants::$REPORT_TYPE . "=\"" . $this->_report_type . "\"," .
                    Constants::$REPORT_HOSTNAME . "=\"" . $this->_report_hostname . "\"," .
                    Constants::$REPORT_OS . "=\"" . $this->_report_os . "\"," .
                    Constants::$REPORT_TAG . "=\"" . $this->_report_tag . "\"," .
                    Constants::$REPORT_KERNEL . "=\"" . $this->_report_kernel . "\"," .
                    Constants::$REPORT_ARCH . "=\"" . $this->_report_arch . "\"," .
                    Constants::$REPORT_SITE . "=\"" . $this->_report_site . "\"," .
                    Constants::$REPORT_VERSION . "=\"" . $this->_version . "\"," .
                    Constants::$REPORT_TIMESTAMP . "=\"" . $timestamp . "\"" .
                    "\n";

                # Store the data
                if (fwrite($reportFile, $header . Utils::getHttpVar(Constants::$REPORT_PKGS)) == FALSE) {
                    throw new Exception("Cannot write to the file '$file'");
                }
                break;
        }

        # Finally close the handler
        fclose($reportFile);
    }

     /*
     * Store packages
     */
    public function storePkgs()
    {
        Utils::log(LOG_DEBUG, "Storing the packages", __FILE__, __LINE__);

        $pkgsManager = $this->getPakiti()->getManager("PkgsManager");
        if($this->_host->getId() != -1){
            $installedPkgs = $pkgsManager->getInstalledPkgs($this->_host);
        }

        $installedPkgsArray = array();
        foreach($installedPkgs as $installedPkg){
            $installedPkgsArray[$installedPkg->getName()][] = $installedPkg;
        }

        $archsManager = $this->getPakiti()->getManager("ArchsManager");
        $archsNames = $archsManager->getArchsNames();
        $newPkgs = array();
        foreach($this->_pkgs as &$pkg){
            # Check if pkg is already in installed pkgs
            if(array_key_exists($pkg->getName(), $installedPkgsArray)){
                foreach($installedPkgsArray[$pkg->getName()] as $key => $installedPkg){
                    if($pkg->getRelease() == $installedPkg->getRelease() 
                    && $pkg->getVersion() == $installedPkg->getVersion() 
                    && $pkg->getArch() == $installedPkg->getArch() 
                    && $pkg->getType() == $installedPkg->getType()){
                        $pkg->setId($installedPkg->getId());
                        break;
                    }
                }
            }
            # If pkg isn't in installed pkgs yet
            if($pkg->getId() == -1){
                if(!in_array($pkg->getArch(), $archsNames)){
                    $arch = new Arch();
                    $arch->setName($pkg->getArch());
                    $archsManager->storeArch($arch);
                    array_push($archsNames, $arch->getName());
                }
                if($pkgsManager->storePkg($pkg)){
                    array_push($newPkgs, $pkg);
                }
            }
        }
        # Calculate Vulnerabilities for new packages
        $vulnerabilitiesManager = $this->getPakiti()->getManager("VulnerabilitiesManager");
        $vulnerabilitiesManager->calculateVulnerabilitiesForPkgs($newPkgs);

        # Assign pkgs with Host
        if($this->_host->getId() != -1){
            $pkgsIds = array_map(function ($pkg) { return $pkg->getId(); }, $this->_pkgs);
            $installedPkgsIds = array_map(function ($pkg) { return $pkg->getId(); }, $installedPkgs);
            $this->getPakiti()->getManager("PkgsManager")->assignPkgsWithHost($pkgsIds, $this->_host->getId(), $installedPkgsIds);
        }
    }

    /*
     * Parse the long string containing list of installed packages.
     */
    protected function parsePkgs($pkgs, $type, $kernel, $version)
    {
        Utils::log(LOG_DEBUG, "Parsing packages", __FILE__, __LINE__);
        $parsedPkgs = array();
        # Remove escape characters
        $pkgs = str_replace("\\", "", $pkgs);

        # Go throught the string, each entry is separated by the new line
        $tok = strtok($pkgs, "\n");
        while ($tok !== FALSE) {
            switch ($version) {
                case "4":
                    if(preg_match("/'(.*)' '(.*)' '(.*)' '(.*)'/", $tok, $entries) == 1){
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = $entries[3];
                        $pkgArch = $entries[4];
                    } else {
                        Utils::log(LOG_INFO, "Package [" . $tok . "] cannot be parsed (omitted)!" , __FILE__, __LINE__);
                    }

                    # If the host uses dpkg we need to split version manually to version and release by the dash.
                    # Suppress warnings, if the version doesn't contain dash, only version will be filled, release will be empty
                    if ($type == Constants::$PACKAGER_SYSTEM_DPKG) {
                        @list ($pkgVersion, $pkgRelease) = explode('-', $pkgVersion);
                    }
                    break;
                default: //cern_1
                    if(preg_match("/(.*)[ \t](.*)-(.*)[ \t](.*)/", $tok, $entries) == 1){
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = $entries[3];
                        $pkgArch = $entries[4];
                    } elseif(preg_match("/(.*)[ \t](.*)[ \t](.*)/", $tok, $entries) == 1){
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = "";
                        $pkgArch = $entries[3];
                    } else {
                        Utils::log(LOG_INFO, "Package [" . $tok . "] cannot be parsed (omitted)!" , __FILE__, __LINE__);
                    }
                    break;
            }

            ## Remove blacklisted packages
            # Remove packages which fits the patterns provided in the configuration
            if (in_array($pkgName, Config::$IGNORE_PACKAGES)) {
                $tok = strtok("\n");
                continue;
            }
            
            # Guess which package represents running kernel
            if (in_array($pkgName, Config::$KERNEL_PACKAGES_NAMES)) {
                # Remove epoch from the version
                $versionWithoutEpoch = Utils::removeEpoch($pkgVersion);
                # Compare result of the uname -r with the package version
                if ($kernel != $versionWithoutEpoch . "-" . $pkgRelease) {
                    # This verion of the kernel isn't booted
                    $tok = strtok("\n");
                    continue;
                }
            }

            # Finally iterate through all regexp which defines packages to ignore
            foreach (Config::$IGNORE_PACKAGES_PATTERNS as &$pkgNamePattern) {
                if (preg_match("/$pkgNamePattern/", $pkgName) == 1) {
                    # Skip this package, because it is in ignore list
                    $tok = strtok("\n");
                    continue;
                }
            }
            unset($pkgNamePattern);

            $pkg = new Pkg();
            $pkg->setName($pkgName);
            $pkg->setArch($pkgArch);
            $pkg->setRelease($pkgRelease);
            $pkg->setVersion($pkgVersion);
            $pkg->setType($type);
            $parsedPkgs[] = $pkg;

            $tok = strtok("\n");
        }

        return $parsedPkgs;
    }

    /*
     * Check whether the proxy is authorized to send the reports on behalf of the host.
     */
    protected function checkProxyAuthz($proxyHostname, $proxyIp)
    {
        Utils::log(LOG_DEBUG, "Checking the proxy authorization", __FILE__, __LINE__);
        switch (Config::$PROXY_AUTHENTICATION_MODE) {
            case Constants::$PROXY_AUTHN_MODE_HOSTNAME:
                if (in_array($proxyHostname, Config::$PROXY_ALLOWED_PROXIES)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case Constants::$PROXY_AUTHN_MODE_IP:
                if (in_array($proxyIp, Config::$PROXY_ALLOWED_PROXIES)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case Constants::$PROXY_AUTHN_MODE_SUBJECT;
                if (in_array(Utils::getServerVar(Constants::$SSL_CLIENT_SUBJECT), Config::$PROXY_ALLOWED_PROXIES)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
        }
    }

    /*
    * Separates the domain name from the hostname
    */
    protected function guessDomain($hostname) {
        Utils::log(LOG_DEBUG, "Guessing the domain name [hostname=$hostname]", __FILE__, __LINE__);
        # Check if $remote_host is really hostname and not only ip
        $ipv4_regex = '/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m';    
        if (preg_match($ipv4_regex, $hostname) > 0) {
            // We have an IPv4, so we cannot do anything more
            return Constants::$NA;
        }
        $ipv6_regex = '/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/';         
        if (preg_match($ipv6_regex, $hostname) > 0) {
            // We have an IPv6, so we cannot do anything more
            return Constants::$NA;
        } 

        # Separate hostname from domain name
        $domain = preg_replace('/^[\w-_]+\.(.*)$/', '\1', $hostname);
        
        # Check if domain ends with .local or .localdomain, these are not valid domains
        if (preg_match('/\.(local|localdomain)$/', $hostname) > 0) {
            return Constants::$NA;
        } else {
            return $domain;
        }
    }
    
    /*
    * Guesses the OS.
    */
    protected function guessOs($osName, $pkgs = array()) {
        Utils::log(LOG_DEBUG, "Guessing the OS", __FILE__, __LINE__);
        
        $osFullName = "unknown";
        # Find the package which represents the OS name/release
        $pkgsArray = array();
        foreach($pkgs as $pkg){
            $pkgsArray[$pkg->getName()][] = $pkg;
        }
        foreach (Constants::$OS_NAMES_DEFINITIONS as $pkgName => &$osTmpName) {
        if (array_key_exists($pkgName, $pkgsArray)) {
            // Iterate over all archs
            foreach ($pkgsArray[$pkgName] as $pkg){
                // Remove epoch if there is one
                $osFullName = $osTmpName . " " . Utils::removeEpoch($pkg->getVersion());
                // we have found OS Name, we can skip the others
                break;
            }
        }
        }
        unset($osTmpName);  

        if ($osFullName == "unknown") {
            # Try to guess the OS name from the data sent by the clent itself?    
            if ($osName != "" || $osName != "unknown") {

                # The Pakiti client has sent the OS name, so canonize it
                foreach (Constants::$OS_NAMES_MAPPING as $pattern => $replacement) {
                    # Apply regex rules on the Os name sent by the client
                    $tmpOsName = preg_replace("/".$pattern."/i", $replacement, $osName, 1, $count);

                    if ($tmpOsName == null) {
                        # Error occured, set the Os name to unknown
                        $osFullName = "unknown";
                    } elseif ($count > 0) {
                        # If there was any replacement $count will contain number of replacements
                        $osFullName = $tmpOsName;
                        break;
                    } 
                }

                # We do not have a rule, so log this OS
                if ($osFullName == "unknown") {
                    $fh = fopen(Constants::$UNKNOWN_OS_NAMES_FILE, 'a');
                    fwrite($fh, date(DATE_RFC822) . ": " . $osName . "\n");
                    fclose($fh);
                }
            }
        }
        return $osFullName; 
    }

    /*
     * Compute hash of the report header (hostname, ip, version, kernel, ...)
     */
    protected function computeReportHeaderHash()
    {
        Utils::log(LOG_DEBUG, "Computing the hash of the report header", __FILE__, __LINE__);
        $header = 
            $this->_report_type .
            $this->_report_hostname .
            $this->_report_os .
            $this->_report_tag .
            $this->_report_kernel .
            $this->_report_arch .
            $this->_report_site;
        return $this->computeHash($header);
    }

    /*
     * Compute hash of the list of packages
     */
    protected function computeReportPkgsHash()
    {
        Utils::log(LOG_DEBUG, "Computing the hash of the list of the packages", __FILE__, __LINE__);
        return $this->computeHash($this->_report_pkgs);
    }

    /*
     * Compute the hash, currently MD5
     */
    protected function computeHash($string)
    {
        return md5($string);
    }

    /*
     * Make diff of the two arrays
     */
    protected function array_compare_recursive($array1, $array2)
    {
        $diff = array();
        foreach ($array1 as $key => &$value) {
            if (!array_key_exists($key, $array2)) {
                $diff[$key] = $value;
            } elseif (is_array($value)) {
                if (!is_array($array2[$key])) {
                    $diff[$key] = $value;
                } else {
                    $new = array_compare_recursive($value, $array2[$key]);
                    if (!empty($new)) {
                        $diff[$key] = $value;
                    };
                };
            } elseif ($array2[$key] !== $value) {
                $diff[$key] = $value;
            };
        };
        unset($value);
        return $diff;
    }
}

?>
