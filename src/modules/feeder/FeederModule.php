<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class FeederModule extends DefaultModule
{
    private $_protocolVersion;
    private $_reportProcessMode;

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

    public function __construct(Pakiti $pakiti)
    {
        parent::__construct($pakiti);

        $this->_host = new Host();
        $this->_report = new Report();

        /*
          Get the version of the protocol used by the client:
          version 4 == format used by legacy Pakiti2 clients
          version 5 == format introduced to support wLCG middleware reporter
        */
        $this->_protocolVersion = Utils::getHttpVar(Constants::$PROTOCOL_VERSION);
        if ($this->_protocolVersion === null) {
            $this->_protocolVersion = Utils::getHttpVar(Constants::$REPORT_VERSION); /* backwards compatibility with pakiti2 clients */
            if ($this->_protocolVersion === null) {
                $this->_protocolVersion = "5";
            }
        }

        $this->_reportProcessMode = Constants::$STORE_ONLY;

        $mode = Utils::getHttpVar(Constants::$PROTOCOL_PROCESSING_MODE);
        if ($mode === null) {
            $mode = Utils::getHttpVar(Constants::$REPORT_REPORT);
        }

        /* backwards compatibility with pakiti2 clients */
        if ($mode != null) {
            if ($mode == "0") {
                $mode = Constants::$STORE_ONLY;
            } elseif ($mode == "1") {
                $mode = Constants::$STORE_AND_REPORT;
            } elseif ($mode == "2") {
                $mode = Constants::$REPORT_ONLY;
            }

            if (! in_array($mode, array(Constants::$STORE_ONLY, Constants::$STORE_AND_REPORT, Constants::$REPORT_ONLY))) {
                throw new Exception("Unsupported processing mode requested ('" . $mode . "')");
            }

            $this->_reportProcessMode = $mode;
        }

        # Get the hostname and ip of the reporting machine (could be a NAT machine)
        $this->_host->setReporterIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0");
        $this->_host->setReporterHostname(gethostbyaddr($this->_host->getReporterIp()));

        # Map variables in the report to the internal variables
        $this->doReportMapping($this->_protocolVersion);

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

        Utils::log(LOG_INFO, "Report from [reporterHost=" . $this->_host->getReporterHostname() . ", reporterIp=" . $this->_host->getReporterIp() . ", clientVersion=" . $this->_protocolVersion . "]", __FILE__, __LINE__);
    }

    public function getResult()
    {
        if (!Utils::isConnectionSecure()) {
            return;
        }
        if ($this->_reportProcessMode == Constants::$STORE_ONLY) {
            return "";
        }
        if ($this->_reportProcessMode == Constants::$REPORT_ONLY) {
            $pkgs = $this->_pkgs;
            $osId = $this->_host->getOsId();
        } else {
            $host = $this->getPakiti()->getManager("HostsManager")->getHostById($this->_host->getId());
            $pkgs = $this->getPakiti()->getManager("PkgsManager")->getPkgs(null, -1, -1, $host->getId());
            $osId = $host->getOsId();
        }

        $cvesManager = $this->getPakiti()->getManager("CvesManager");
        $cveTagsManager = $this->getPakiti()->getManager("CveTagsManager");

        $result = "";
        foreach ($pkgs as $pkg) {
            $cvesNames = $cvesManager->getCvesNamesForPkgAndOs($pkg->getId(), $osId, true);
            foreach ($cvesNames as $cveName) {
                $cveTags = $cveTagsManager->getCveTagsByCveName($cveName);
                foreach ($cveTags as $cveTag) {
                    $result .= $pkg->getName() . "\t" .
                        $pkg->getVersionRelease() . "\t" .
                        $pkg->getArchName() . "\t" .
                        $cveName . "\t" .
                        $cveTag->getTagName() . "\n";
                }
            }
        }
        return $result;
    }

    /**
     * Returns hostname of the reporting machine
     */
    public function getReportHost()
    {
        return $this->_host->getReporterHostname();
    }

    /**
     * Maps variables from the reports (depends on the report version) onto the local variables
     */
    private function doReportMapping($protocol_version)
    {
        switch ($protocol_version) {
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

            case "5":
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
                if ($data == "") {
                    throw new Exception("Feeder [reporterHost=" . $this->_host->getReporterHostname() . ",reporterIp=" . $this->_host->getReporterIp() . "] hasn't sent any data!");
                }
                
                $tmpFileIn = tempnam("/dev/shm/", "pakiti3_IN_");
                # Store encrypted report into the file and the use openssl smime to decode it
                if (file_put_contents($tmpFileIn, $data) === false) {
                    throw new Exception("Cannot write to the file '$tmpFileIn' during decoding report");
                }
                # If mime type is application/octet-stream try to decrypt data, else use data without decryption
                if (mime_content_type($tmpFileIn) == Constants::$MIME_TYPE_ENCRYPTED_REPORT) {
                    $tmpFileOut = tempnam("/dev/shm/", "pakiti3_OUT_");
                    if (system("openssl smime -decrypt -binary -inform DER -inkey " . Config::$REPORT_DECRYPTION_KEY . " -in $tmpFileIn -out $tmpFileOut") == false) {
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
                        if (trim($line) == "#" || empty($line)) {
                            continue;
                        }
                        # Store packages into the internal variable
                        $this->_report_pkgs .= $line;
                    }
                } else {
                    # error opening the file.
                    throw new Exception("Cannot open file with the report '$tmpFileOut'");
                }
                fclose($handle);
                unlink($tmpFileOut);
                break;

            default:
                throw new Exception("Unsupported protocol version sent by the client ($protocol_version)");
                break;
        }
    }

    /**
     * Process the report, stores the data about the host, installed packages and report itself.
     */
    public function processReport()
    {
        if ($this->_reportProcessMode == Constants::$REPORT_ONLY) {
            # If the client wants to only check vulnerabilities, process one by one (because of storing pkgs)
            $this->processReportWithoutSavingToDtb();
        } else {
            if (!$this->isHostSentNewData()) {
                $this->processReportWithSameData();
            } else {
                $this->processReportWithNewData();
            }
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
            # Store OS
            $os = new Os();
            $os->setName($this->_report_os);
            $this->getPakiti()->getManager("OsesManager")->storeOs($os);
            $this->_host->setOsId($os->getId());

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

    /**
     * Process all received entries
     */
    public function prepareReport()
    {
        Utils::log(LOG_DEBUG, "Preparing the report", __FILE__, __LINE__);

        # Set host variables Kernel
        $this->_host->setKernel($this->_report_kernel);

        # Parse the packages list
        $this->_pkgs = $this->parsePkgs($this->_report_pkgs, $this->_report_type, $this->_report_kernel,
                                        $this->_report_arch, $this->_protocolVersion);

        # Set the initial information about the report (using _pkgs)
        $this->_report->setNumOfInstalledPkgs(sizeof($this->_pkgs));
    }

    /**
     * Store Host
     */
    public function storeHost()
    {
        Utils::log(LOG_DEBUG, "Storing host to the DB", __FILE__, __LINE__);

        # Store Os
        $os = new Os();
        $os->setName($this->_report_os);
        $this->getPakiti()->getManager("OsesManager")->storeOs($os);
        $this->_host->setOsId($os->getId());

        # Store arch
        $arch = new Arch();
        $arch->setName($this->_report_arch);
        $this->getPakiti()->getManager("ArchsManager")->storeArch($arch);
        $this->_host->setArchId($arch->getId());

        # Store pkgType
        $pkgType = new PkgType();
        $pkgType->setName($this->_report_type);
        $this->getPakiti()->getManager("PkgTypesManager")->storePkgType($pkgType);
        $this->_host->setPkgTypeId($pkgType->getId());

        # Store domain
        $domain = new Domain();
        $domain->setName($this->guessDomain($this->_report_hostname));
        $this->getPakiti()->getManager("DomainsManager")->storeDomain($domain);
        $this->_host->setDomainId($domain->getId());

        # Store Host
        $this->getPakiti()->getManager("HostsManager")->storeHost($this->_host);

        # Store host group
        $hostGroup = new HostGroup();
        if ($this->_report_site != null) {
            $hostGroup->setName($this->_report_site);
        } else {
            $hostGroup->setName(Constants::$NA);
        }
        $this->getPakiti()->getManager("HostGroupsManager")->storeHostGroup($hostGroup);

        # Assign Host to host group
        $this->getPakiti()->getManager("HostGroupsManager")->removeHostFromHostGroups($this->_host->getId());
        $this->getPakiti()->getManager("HostGroupsManager")->assignHostToHostGroup($this->_host->getId(), $hostGroup->getId());
    }

    /**
     * Store the report
     */
    public function storeReport()
    {
        Utils::log(LOG_DEBUG, "Storing report to the DB", __FILE__, __LINE__);

        # Get number of CVEs
        $this->_report->setNumOfCves(sizeof($this->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($this->_host->getId(), null)));
        $this->_report->setNumOfCvesWithTag(sizeof($this->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($this->_host->getId(), true)));

        # Get number of installed packages and set to the new report
        if ($this->_report->getNumOfInstalledPkgs() == -1) {
            $this->_report->setNumOfInstalledPkgs($this->getPakiti()->getManager("PkgsManager")->getPkgsCount($this->_host->getId()));
        }

        # Set HostGroup
        $this->_report->setHostGroup($this->_report_site);

        # Set Source
        $this->_report->setSource($this->_report_tag);

        # Set time when report was been processed
        $this->_report->setProcessedOn(time());

        # Store report
        $this->_report = $this->getPakiti()->getManager("ReportsManager")->createReport($this->_report, $this->_host);
    }

    public function isHostSentNewData()
    {
        # Get host if exist
        $id = $this->getPakiti()->getManager("HostsManager")->getHostId($this->_host->getHostname(), $this->_host->getIp(), $this->_host->getReporterHostname(), $this->_host->getReporterIp());
        if ($id != -1) {
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
                Utils::log(LOG_INFO, "Feeder [host=" . $this->_host->getHostname() . "] hasn't sent any new data, exiting...", __FILE__, __LINE__);
                return false;
            }
        }

        return true;
    }

    /**
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
        while (($reportFile = fopen($file, "w")) === false) {
            $count--;

            # Wait a bit
            sleep(1);

            # Try to create the file three times, if the operation is not successfull, then throw the exception
            if ($count == 0) {
                Utils::log(LOG_ERR, "Error creating the file", __FILE__, __LINE__);
                throw new Exception("Cannot create the file containing host report [host=" . $this->_host->getHostname() . ", reporterHostname=" . $this->_host->getReporterHostname() . "]");
            }
            Utils::log(LOG_ERR, "Cannot create the file, trying again ($count attempts left)", __FILE__, __LINE__);
        }

        switch ($this->_protocolVersion) {
            case "4":
                # Prepare the header
                $header = Constants::$REPORT_TYPE . "=\"" . $this->_report_type . "\"," .
                    Constants::$REPORT_HOSTNAME . "=\"" . $this->_report_hostname . "\"," .
                    Constants::$REPORT_OS . "=\"" . $this->_report_os . "\"," .
                    Constants::$REPORT_TAG . "=\"" . $this->_report_tag . "\"," .
                    Constants::$REPORT_KERNEL . "=\"" . $this->_report_kernel . "\"," .
                    Constants::$REPORT_ARCH . "=\"" . $this->_report_arch . "\"," .
                    Constants::$REPORT_SITE . "=\"" . $this->_report_site . "\"," .
                    Constants::$REPORT_TIMESTAMP . "=\"" . $timestamp . "\"" .
                    "\n";

                # Store the data
                if (fwrite($reportFile, $header . Utils::getHttpVar(Constants::$REPORT_PKGS)) == false) {
                    throw new Exception("Cannot write to the file '$file'");
                }
                break;
        }

        # Finally close the handler
        fclose($reportFile);
    }

    /**
     * Store packages
     */
    public function storePkgs()
    {
        Utils::log(LOG_DEBUG, "Storing the packages", __FILE__, __LINE__);

        $archsManager = $this->getPakiti()->getManager("ArchsManager");
        $pkgTypesManager = $this->getPakiti()->getManager("PkgTypesManager");
        $pkgsManager = $this->getPakiti()->getManager("PkgsManager");

        if ($this->_host->getId() != -1) {
            $installedPkgs = $pkgsManager->getPkgs(null, -1, -1, $this->_host->getId());
        }

        $installedPkgsArray = array();
        foreach ($installedPkgs as $installedPkg) {
            $installedPkgsArray[$installedPkg->getName()][] = $installedPkg;
        }

        $newPkgs = array();
        foreach ($this->_pkgs as $pkg) {
            # Check if pkg is already in installed pkgs
            if (array_key_exists($pkg->getName(), $installedPkgsArray)) {
                foreach ($installedPkgsArray[$pkg->getName()] as $key => $installedPkg) {
                    if ($pkg->getRelease() == $installedPkg->getRelease()
                        && $pkg->getVersion() == $installedPkg->getVersion()
                        && $pkg->getArchName() == $installedPkg->getArchName()
                        && $pkg->getPkgTypeName() == $installedPkg->getPkgTypeName()) {
                        $pkg->setId($installedPkg->getId());
                        break;
                    }
                }
            }
            # If pkg isn't in installed pkgs yet
            if ($pkg->getId() == -1) {

                $arch = new Arch();
                $arch->setName($pkg->getArchName());
                $archsManager->storeArch($arch);
                $pkg->setArchId($arch->getId());

                $pkgType = new PkgType();
                $pkgType->setName($pkg->getPkgTypeName());
                $pkgTypesManager->storePkgType($pkgType);
                $pkg->setPkgTypeId($pkgType->getId());

                if ($pkgsManager->storePkg($pkg)) {
                    array_push($newPkgs, $pkg);
                }
            }
        }
        # Calculate Vulnerabilities for new packages
        $vulnerabilitiesManager = $this->getPakiti()->getManager("VulnerabilitiesManager");
        $vulnerabilitiesManager->calculateVulnerabilitiesForPkgs($newPkgs);

        # Assign pkgs with Host
        if ($this->_host->getId() != -1) {
            $pkgsIds = array_map(function ($pkg) {
                return $pkg->getId();
            }, $this->_pkgs);
            $installedPkgsIds = array_map(function ($pkg) {
                return $pkg->getId();
            }, $installedPkgs);
            $this->getPakiti()->getManager("PkgsManager")->assignPkgsWithHost($pkgsIds, $this->_host->getId(), $installedPkgsIds);
        }
    }

    /**
     * Parse the long string containing list of installed packages.
     */
    protected function parsePkgs($pkgs, $type, $kernel, $arch, $protocol_version)
    {
        Utils::log(LOG_DEBUG, "Parsing packages", __FILE__, __LINE__);
        $parsedPkgs = array();
        # Remove escape characters
        $pkgs = str_replace("\\", "", $pkgs);

        $debian_kernel_found = False;
        $kernel_sent = false;

        # Go throught the string, each entry is separated by the new line
        $tok = strtok($pkgs, "\n");
        while ($tok !== false) {
            switch ($protocol_version) {
                case "4":
                    if (preg_match("/'(.*)' '(.*)' '(.*)' '(.*)'/", $tok, $entries) == 1) {
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = $entries[3];
                        $pkgArch = $entries[4];
                    } else {
                        Utils::log(LOG_INFO, "Package [" . $tok . "] cannot be parsed (omitted)!", __FILE__, __LINE__);
                    }

                    # If the host uses dpkg we need to split version manually to version and release by the dash.
                    # Suppress warnings, if the version doesn't contain dash, only version will be filled, release will be empty
                    if ($type == Constants::$PACKAGER_SYSTEM_DPKG) {
                        $version = $pkgVersion;
                        $pos = strrpos($version, '-');
                        if ($pos !== false) {
                            $pkgVersion = substr($version, 0, $pos);
                            $pkgRelease = substr($version, ($pos + 1) - strlen($version), strlen($version) - ($pos + 1));
                        }
                    }
                    break;
                case "5":
                    if (preg_match("/(.*)[ \t](.*)-([^-]*)[ \t](.*)/", $tok, $entries) == 1) {
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = $entries[3];
                        $pkgArch = $entries[4];
                    } elseif (preg_match("/(.*)[ \t](.*)[ \t](.*)/", $tok, $entries) == 1) {
                        $pkgName = $entries[1];
                        $pkgVersion = $entries[2];
                        $pkgRelease = "";
                        $pkgArch = $entries[3];
                    } else {
                        Utils::log(LOG_INFO, "Package [" . $tok . "] cannot be parsed (omitted)!", __FILE__, __LINE__);
                    }
                    break;
                default:
                    throw new Exception("Unsupported protocol version sent by the client ($protocol_version)");
                    break;
            }

            # Guess which package represents running kernel
            if (in_array($pkgName, Config::$KERNEL_PACKAGES_NAMES)) {
                # Remove epoch from the version
                $versionWithoutEpoch = Utils::removeEpoch($pkgVersion);
                # Compare result of the uname -r with the package version
                # Due to some other strings clued to the kernel version, we are looking for substring
                if (strpos($kernel, $versionWithoutEpoch . "-" . $pkgRelease) === false) {
                    # This verion of the kernel isn't booted
                    $tok = strtok("\n");
                    continue;
                }
                $kernel_sent = true;

                # This is a hack to add a record for debian running kernel named "linux" in order for it to match vulnerability
                # definitions. The client sends the kernel version which is part of the package name.
                if (strpos($pkgName, "linux-image-".$kernel) !== false) {
                    if ($debian_kernel_found) {
                        /* there might be several packages for the kernel, let's act only on the first one */
                        $tok = strtok("\n");
                        continue;
                    }
                    $deb_pkg = new Pkg();
                    $deb_pkg->setName("linux");
                    $deb_pkg->setArchName($pkgArch);
                    $deb_pkg->setRelease($pkgRelease);
                    $deb_pkg->setVersion($pkgVersion);
                    $deb_pkg->setPkgTypeName($type);
                    $parsedPkgs[] = $deb_pkg;

                    $debian_kernel_found = True;
                }
            } else {
                # Remove packages which match the patterns provided in the configuration.
                # Note that kernel-related packages are stored even if they fit the patterns.
                if (in_array($pkgName, Config::$IGNORE_PACKAGES)) {
                    $tok = strtok("\n");
                    continue;
                }

                $found = false;
                foreach (Config::$IGNORE_PACKAGES_PATTERNS as $pkgNamePattern) {
                    if (preg_match("/$pkgNamePattern/", $pkgName) == 1) {
                        $found = true;
                        break;
                    }
                }
                unset($pkgNamePattern);
                if ($found) {
                    $tok = strtok("\n");
                    continue;
                }
            }

            $pkg = new Pkg();
            $pkg->setName($pkgName);
            $pkg->setArchName($pkgArch);
            $pkg->setRelease($pkgRelease);
            $pkg->setVersion($pkgVersion);
            $pkg->setPkgTypeName($type);
            $parsedPkgs[] = $pkg;

            $tok = strtok("\n");
        }

        /* Some deployments don't have kernel packages installed, we generate a fake record for them */
        if (! $kernel_sent) {
            /* XXX The code is RH-specific, I'm affraid */
            $pkg = new Pkg();
            $pkg->setName("kernel");

            $kernel_parts = explode("-", trim($kernel), 2);
            $kernel_version = "0:" . $kernel_parts[0];
            $kernel_release = $kernel_parts[1];
            # If release containst also arch, strip it
            $arch_to_be_removed = ".$arch";
            $arch_len = strlen($arch_to_be_removed);
            if (substr($kernel_release, -$arch_len, $arch_len) == $arch_to_be_removed) {
                 $kernel_release = substr($kernel_release, 0, strlen($kernel_release)-$arch_len);
            }

            $pkg->setArchName($arch);
            $pkg->setRelease($kernel_release);
            $pkg->setVersion($kernel_version);
            $pkg->setPkgTypeName($type);
            $parsedPkgs[] = $pkg;

            Utils::log(LOG_INFO, "Adding fake kernel $kernel_version-$kernel_release for " . $this->_host->getHostname());
        }

        return $parsedPkgs;
    }

    /**
     * Check whether the proxy is authorized to send the reports on behalf of the host.
     */
    protected function checkProxyAuthz($proxyHostname, $proxyIp)
    {
        Utils::log(LOG_DEBUG, "Checking the proxy authorization", __FILE__, __LINE__);
        switch (Config::$PROXY_AUTHENTICATION_MODE) {
            case Constants::$PROXY_AUTHN_MODE_HOSTNAME:
                if (in_array($proxyHostname, Config::$PROXY_ALLOWED_PROXIES)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case Constants::$PROXY_AUTHN_MODE_IP:
                if (in_array($proxyIp, Config::$PROXY_ALLOWED_PROXIES)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case Constants::$PROXY_AUTHN_MODE_SUBJECT:
                if (in_array(Utils::getServerVar(Constants::$SSL_CLIENT_SUBJECT), Config::$PROXY_ALLOWED_PROXIES)) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
    }

    /**
     * Separates the domain name from the hostname
     */
    protected function guessDomain($hostname)
    {
        Utils::log(LOG_DEBUG, "Guessing the domain name [hostname=$hostname]", __FILE__, __LINE__);
        # Check if $remote_host is really hostname and not only ip
        $ipv4_regex = '/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m';
        if (preg_match($ipv4_regex, $hostname) > 0) {
            # We have an IPv4, so we cannot do anything more
            return Constants::$NA;
        }
        $ipv6_regex = '/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/';
        if (preg_match($ipv6_regex, $hostname) > 0) {
            # We have an IPv6, so we cannot do anything more
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

    /**
     * Compute hash of the report header (hostname, ip, kernel, ...)
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

    /**
     * Compute hash of the list of packages
     */
    protected function computeReportPkgsHash()
    {
        Utils::log(LOG_DEBUG, "Computing the hash of the list of the packages", __FILE__, __LINE__);
        return $this->computeHash($this->_report_pkgs);
    }

    /**
     * Compute the hash, currently MD5
     */
    protected function computeHash($string)
    {
        return md5($string);
    }

    /**
     * Make diff of the two arrays
     */
    protected function array_compare_recursive($array1, $array2)
    {
        $diff = array();
        foreach ($array1 as $key => $value) {
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
