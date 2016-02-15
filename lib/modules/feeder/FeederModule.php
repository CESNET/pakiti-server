<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

class FeederModule extends DefaultModule
{
    private $_version;
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
    private $_report_report;
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

        # Map variables in the report to the internal variables
        $this->doReportMapping($this->_version);

        # Get the hostname and ip
        $this->_host->setHostname($this->_report_hostname);
        $this->_host->setIp($this->_report_ip);

        # Get the hostname and ip of the reporting machine (could be a NAT machine)
        $this->_host->setReporterIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0");
        $this->_host->setReporterHostname(gethostbyaddr($this->_host->getReporterIp()));
        Utils::log(LOG_INFO, "Report from [reporterHost=" . $this->_host->getReporterHostname() . ",reporterIp=" . $this->_host->getReporterIp() . "]",
            __FILE__, __LINE__);

        # Is the host proxy?
        if ($this->_report_proxy == Constants::$HOST_IS_PROXY) {
            $this->_report->setTroughtProxy(Constants::$HOST_IS_PROXY);

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
    }

    public function sendResultsBack()
    {
        $return_string = "";
        $pkgs =& $this->getPakiti()->getManager("VulnerabilitiesManager")->getVulnerablePkgsWithCve($this->_host, "id", -1, -1);
        foreach ($pkgs as $pkg) {
            foreach ($pkg['CVE'] as $pkgCve) {
                $cveTag = $pkgCve->getTag();
                if (!empty($cveTag)) {
                    $return_string = $return_string . str_pad($pkg["Pkg"]->getName(), 30) . str_pad($pkg["Pkg"]->getVersionRelease(), 30) . $pkg["Pkg"]->getArch() . "\n";
                }
            }
        }
        print($return_string);
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
                $this->_report_report = Utils::getHttpVar(Constants::$REPORT_REPORT);
                $this->_report_pkgs = Utils::getHttpVar(Constants::$REPORT_PKGS);
                # Report from RPM based OS: version and release are splitted by space. Debian and CERN reports separates version and release by a dash.
                # FIXME, we need set is in a changelog
                $this->_report_pkgs_format = $this->_report_type == "rpm" ? "space" : "dash";
                break;
            case "cern_1":
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
                $tmpFileIn = tempnam("/dev/shm/", "cern_IN_");
                $tmpFileOut = tempnam("/dev/shm/", "cern_OUT_");
                # Store encrypted report into the file and the use openssl smime to decode it
                if (file_put_contents($tmpFileIn, $data) === FALSE) {
                    throw new Exception("Cannot write to the file '$tmpFileIn' during decoding cern_1 report");
                }
                if (system("openssl smime -decrypt -binary -inform DER -inkey " . Config::$CERN_REPORT_DECRYPTION_KEY . " -in $tmpFileIn -out $tmpFileOut") === FALSE) {
                    throw new Exception("Cannot run openssl smime on the file '$tmpFileIn'");
                }
                # Clean up
                unlink($tmpFileIn);

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
                            case "system":
                                $this->_report_os = trim($fields[1]);
                                break;
                        }
                    }

                    while (($line = fgets($handle)) !== false) {
                        if ($line == "#" || empty($line)) continue;
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

        # Start the transaction
        $this->getPakiti()->getManager("DbManager")->begin();

        try {
            # Parse the data
            $this->prepareReport();

            if (!$this->isHostSentNewData()) {
                return false;
            }

            # Process the list of package, synchronize received list of installed packages with one in the DB
            $this->storePkgs();

            # Store the report
            $this->storeReport();
        } catch (Exception $e) {
            # Rollback the transaction
            $this->getPakiti()->getManager("DbManager")->rollback();
            throw $e;
        }

        # Commit the transaction
        $this->getPakiti()->getManager("DbManager")->commit();

        # Find vulnerabilities
        $this->getPakiti()->getManager("VulnerabilitiesManager")->calculateVulnerablePkgsForSpecificHost($this->_host);

        $cveCount = $this->getPakiti()->getManager("CveDefsManager")->getCvesCount($this->_host);
        $this->_report->setNumOfCves($cveCount);
        $this->getPakiti()->getManager("ReportsManager")->updateReport($this->_report);

        return true;

    }

    /*
     * Process all received entries
     */
    public function prepareReport()
    {
        Utils::log(LOG_DEBUG, "Preparing the report", __FILE__, __LINE__);
        $tag = null;
        $hostGroup = null;

        switch ($this->_version) {
            case "4":
                Utils::log(LOG_DEBUG, "Client in version 4", __FILE__, __LINE__);
                # Get the rest of HTTP variables
                # host, os, arch, kernel, site, version, type, pkgs
                $this->_host->setOsName($this->_report_os);
                $this->_host->setArchName($this->_report_arch);
                $this->_host->setKernel($this->_report_kernel);
                $this->_host->setType($this->_report_type);
                break;
            case "cern_1":
                Utils::log(LOG_DEBUG, "Client in version CERN 1", __FILE__, __LINE__);
                # Get the rest of HTTP variables
                # host, os, arch, kernel, site, version, type, pkgs
                $this->_host->setOsName($this->_report_os);
                $this->_host->setArchName($this->_report_arch);
                $this->_host->setKernel($this->_report_kernel);
                $this->_host->setType($this->_report_type);
                break;
        }

        # Parse the packages list
        $this->_pkgs = $this->parsePkgs($this->_report_pkgs);

        # Set the initial information about the report
        $this->_report->setReceivedOn(time());

        # Get the host object from the DB, if the host doesn't exist in the DB, this routine will create it
        $this->_host = $this->getPakiti()->getManager("HostsManager")->getHostFromReport($this->_host, $this->_pkgs);

        # Get the host group
        $hostGroup = new HostGroup();
        $hostGroup->setName($this->_report_site);
        # If the host is already member of the host group, no operation is done
        $this->getPakiti()->getManager("HostGroupsManager")->assignHostToHostGroup($this->_host, $hostGroup);

        # Get the host tag and assign it to the host
        $tag = new Tag();
        $tag->setName($this->_report_tag);
        # If the tag is already assigned, no operation is done
        $this->getPakiti()->getManager("TagsManager")->assignTagToHost($this->_host, $tag);

        $this->_report->setNumOfInstalledPkgs(sizeof($this->_pkgs));
    }

    /*
     * Store the report
     */
    public function storeReport()
    {
        Utils::log(LOG_DEBUG, "Storing report to the DB", __FILE__, __LINE__);

        $this->_report->setProcessedOn(time());

        $this->_report = $this->getPakiti()->getManager("ReportsManager")->createReport($this->_report, $this->_host);
    }

    public function isHostSentNewData()
    {
        # Get the host id from the DB
        $id = $this->getPakiti()->getManager("HostsManager")->getHostId($this->_host);

        # Get the hashes of the previous report, but only for hosts already stored in the DB
        if ($id != -1) {
            $host = $this->getPakiti()->getManager("HostsManager")->getHostById($id);
            $lastReportHashes = $this->getPakiti()->getManager("ReportsManager")->getLastReportHashes($host);
            $currentReportHeaderHash = $this->computeReportHeaderHash();
            $currentReportPkgsHash = $this->computeReportPkgsHash();

            # Check if the hashes are equals
            if (($lastReportHashes != null) && (($lastReportHashes[Constants::$REPORT_LAST_HEADER_HASH] == $currentReportPkgsHash) ||
                    ($lastReportHashes[Constants::$REPORT_LAST_PKGS_HASH] == $currentReportPkgsHash))
            ) {
                # Data sent by the host are the same as stored one, so we do not need to store anything
                Utils::log(LOG_INFO, "Feeder [host=" . $this->_host->getHostname() . "] doesn't send any new data, exiting...", __FILE__, __LINE__);

                // Recalculate vulnerable pkgs for a host in case a new definitions are appeared
                $this->getPakiti()->getManager("VulnerabilitiesManager")->calculateVulnerablePkgsForSpecificHost($host);

                //Update Last Host Report
                $lastReport = $this->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
                $timestamp = microtime(true);
                $lastReport->setReceivedOn($timestamp);
                $lastReport->setProcessedOn($timestamp);
                $cveCount = $this->getPakiti()->getManager("CveDefsManager")->getCvesCount($host);
                $lastReport->setNumOfCves($cveCount);
                $lastReport->setId(-1);
                // Create new Report from Last Report
                $this->getPakiti()->getManager("ReportsManager")->createReport($lastReport, $host);
                $this->getPakiti()->getManager("DbManager")->commit();
                return false;
            }
        }

        # Store the hashes into the DB, but only for hosts already stored in the DB
        if ($id != -1) {
            $this->getPakiti()->getManager("ReportsManager")->storeReportHashes($this->_host, $currentReportHeaderHash, $currentReportPkgsHash);
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
                    Constants::$REPORT_REPORT . "=\"" . $this->_report_report . "\"," .
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
     * Process packages.
     */
    public function storePkgs()
    {
        Utils::log(LOG_DEBUG, "Storing the packages", __FILE__, __LINE__);
        # Load the actually stored packages from the DB, the array is already sorted by the pkgName
        $pkgs =& $this->getPakiti()->getManager("PkgsManager")->getInstalledPkgsAsArray($this->_host);
        $pkgsToAdd = array();
        $pkgsToUpdate = array();
        $pkgsToRemove = array();

        // Find packages which should be added or updated
        foreach ($this->_pkgs as $pkgName => $pkgArchs) {
            if (!array_key_exists($pkgName, $pkgs)) {
                # Package is missing in the DB
                $pkgsToAdd[$pkgName] = $pkgArchs;

            } else {
                foreach ($pkgArchs as $pkgArch => $versionAndRelease) {

                    if (!array_key_exists($pkgArch, $pkgs[$pkgName])) {
                        $pkgsToAdd[$pkgName][$pkgArch] = array('pkgVersion' => $versionAndRelease["pkgVersion"],
                            'pkgRelease' => $versionAndRelease["pkgRelease"]);

                    } elseif ($versionAndRelease['pkgVersion'] != $pkgs[$pkgName][$pkgArch]['pkgVersion']) {
                        $pkgIdThatShouldBeUpdate = $this->getPakiti()->getManager("PkgsManager")->getPkgId($pkgName,
                            $pkgs[$pkgName][$pkgArch]['pkgVersion'], $pkgs[$pkgName][$pkgArch]['pkgRelease'], $pkgArch);

                        $pkgsToUpdate[$pkgName][$pkgArch] = array('pkgVersion' => $versionAndRelease["pkgVersion"],
                            'pkgRelease' => $versionAndRelease["pkgRelease"], 'pkgIdThatShouldBeUpdate' => $pkgIdThatShouldBeUpdate);

                    } elseif ($versionAndRelease['pkgRelease'] != $pkgs[$pkgName][$pkgArch]['pkgRelease']) {
                        $pkgIdThatShouldBeUpdate = $this->getPakiti()->getManager("PkgsManager")->getPkgId($pkgName,
                            $pkgs[$pkgName][$pkgArch]['pkgVersion'], $pkgs[$pkgName][$pkgArch]['pkgRelease'], $pkgArch);

                        $pkgsToUpdate[$pkgName][$pkgArch] = array('pkgVersion' => $versionAndRelease["pkgVersion"],
                            'pkgRelease' => $versionAndRelease["pkgRelease"], 'pkgIdThatShouldBeUpdate' => $pkgIdThatShouldBeUpdate);
                    }
                }

            }
        }

        // Find packages which should be deleted
        foreach ($pkgs as $pkgName => $pkgArchs) {

            # Check what architecture we should remove
            if (!array_key_exists($pkgName, $this->_pkgs)) {
                foreach ($pkgArchs as $pkgArch => $versionAndRelease) {
                    $pkgsToRemove[$pkgName][$pkgArch] = array('pkgVersion' => $versionAndRelease["pkgVersion"],
                        'pkgRelease' => $versionAndRelease["pkgRelease"]);
                }
            } else {

                foreach ($pkgArchs as $pkgArch => $versionAndRelease) {
                    if (!array_key_exists($pkgArch, $this->_pkgs[$pkgName])) {
                        $pkgsToRemove[$pkgName][$pkgArch] = array('pkgVersion' => $versionAndRelease["pkgVersion"],
                            'pkgRelease' => $versionAndRelease["pkgRelease"]);
                    }
                }
            }
        }

        if (sizeof($pkgsToAdd) > 0) $this->getPakiti()->getManager("PkgsManager")->addPkgs($this->_host, $pkgsToAdd);
        if (sizeof($pkgsToUpdate) > 0) $this->getPakiti()->getManager("PkgsManager")->updatePkgs($this->_host, $pkgsToUpdate);
        if (sizeof($pkgsToRemove) > 0) $this->getPakiti()->getManager("PkgsManager")->removePkgs($this->_host, $pkgsToRemove);
    }

    /*
     * Parse the long string containing list of installed packages.
     */
    protected function parsePkgs(&$pkgs)
    {
        Utils::log(LOG_DEBUG, "Parsing packages", __FILE__, __LINE__);
        $parsedPkgs = array();
        # Remove escape characters
        $pkgs = str_replace("\\", "", $pkgs);

        # Go throught the string, each entry is separated by the new line
        $tok = strtok($pkgs, "\n");
        while ($tok !== FALSE) {
            switch ($this->_version) {
                case "4":
                    preg_match("/'(.*)' '(.*)' '(.*)' '(.*)'/", $tok, $entries);
                    $pkgName = $entries[1];
                    $pkgVersion = $entries[2];
                    $pkgRelease = $entries[3];
                    $pkgArch = $entries[4];

                    # If the host uses dpkg we need to split version manually to version and release by the dash.
                    # Suppress warnings, if the version doesn't contain dash, only version will be filled, release will be empty
                    if ($this->_host->getType() == Constants::$PACKAGER_SYSTEM_DPKG) {
                        @list ($pkgVersion, $pkgRelease) = explode('-', $pkgVersion);
                    }
                    break;
                case "cern_1":
                    preg_match("/(.*)[ \t](.*)-(.*)[ \t](.*)/", $tok, $entries);
                    $pkgName = $entries[1];
                    $pkgVersion = $entries[2];
                    $pkgRelease = $entries[3];
                    $pkgArch = $entries[4];
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
                if ($this->_host->getKernel() != $versionWithoutEpoch . "-" . $pkgRelease) {
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

            # $parsedPkgs['pkgName'] = array ( pkgVersion, pkgRelease, pkgArch );
            $parsedPkgs[$pkgName][$pkgArch] = array('pkgVersion' => $pkgVersion, 'pkgRelease' => $pkgRelease);
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
     * Compute hash of the report header (hostname, ip, version, kernel, ...)
     */
    protected function computeReportHeaderHash()
    {
        Utils::log(LOG_DEBUG, "Computing the hash of the report header", __FILE__, __LINE__);
        switch ($this->_version) {
            case "4":
                $header = Utils::getHttpVar(Constants::$REPORT_TYPE) .
                    Utils::getHttpVar(Constants::$REPORT_HOSTNAME) .
                    Utils::getHttpVar(Constants::$REPORT_OS) .
                    Utils::getHttpVar(Constants::$REPORT_TAG) .
                    Utils::getHttpVar(Constants::$REPORT_KERNEL) .
                    Utils::getHttpVar(Constants::$REPORT_ARCH) .
                    Utils::getHttpVar(Constants::$REPORT_SITE) .
                    Utils::getHttpVar(Constants::$REPORT_VERSION) .
                    Utils::getHttpVar(Constants::$REPORT_REPORT);

                return $this->computeHash($header);
                break;
        }
    }

    /*
     * Compute hash of the list of packages
     */
    protected function computeReportPkgsHash()
    {
        Utils::log(LOG_DEBUG, "Computing the hash of the list of the packages", __FILE__, __LINE__);
        return $this->computeHash(Utils::getHttpVar(Constants::$REPORT_PKGS));
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
