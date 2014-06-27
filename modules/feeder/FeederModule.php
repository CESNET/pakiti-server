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

class FeederModule extends DefaultModule {
  private $_version;
  private $_report;
  private $_host;
  private $_pkgs;
  
  public function __construct(Pakiti &$pakiti) {
    parent::__construct($pakiti);

    $this->_host = new Host();
    $this->_report = new Report();
    
    # Set the time, when we received the report
    $this->_report->setReceivedOn(time());
    
    # Get the version of the client
    if (($this->_version = Utils::getHttpVar(Constants::$REPORT_VERSION)) == null) {
      throw new Exception("Client did not send the version!");
    }
    
    # Get the hostname and ip
    $this->_host->setHostname(Utils::getHttpVar(Constants::$REPORT_HOSTNAME));
    $this->_host->setIp(Utils::getHttpVar(Constants::$REPORT_IP));
    
    # Get the hostname and ip of the reporting machine (could be a NAT machine)
    $this->_host->setReporterIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0");
    $this->_host->setReporterHostname(gethostbyaddr($this->_host->getReporterIp()));
    Utils::log(LOG_DEBUG, "Report from [reporterHost=".$this->_host->getReporterHostname().",reporterIp=".$this->_host->getReporterIp()."]",
        __FILE__, __LINE__);
        
    # Is the host proxy?
    if (Utils::getHttpVar(Constants::$REPORT_PROXY) == Constants::$HOST_IS_PROXY) {
      $this->_report->setTroughtProxy(Constants::$HOST_IS_PROXY);
      
      # Check if the proxy is authorized to send the reports
      if (!$this->checkProxyAuthz($this->_host->getReporterHostname(), $this->_host->getReporterIp())) {
        throw new Exception("Proxy " . $this->_host->getReporterHostname() . " is not authorized to send the reports");
      }
      Utils::log(LOG_INFO, "Proxy logging [proxy=" . $this->_host->getReporterHostname()."] for [host=".$this->_host->getHostname()."]");
      
      # If we are in proxy mode, the reporterHostname and reporterIp will be replaced with the real hostname and ip of the client machine.
      $this->_host->setReporterHostname($this->_host->getHostname());
      $this->_host->setReporterIp($this->_host->getIp());
    } else {
      $this->_report->setTroughtProxy(Constants::$HOST_IS_NOT_PROXY);
    }
  }
  
  /*
   * Process the report, stores the data about the host, installed packages and report itself.
   */
  public function processReport() {
    
    # Start the transaction
    $this->getPakiti()->getManager("DbManager")->begin();
    
    try {
      # Parse the data and store the report
      $this->prepareReport();
  
      # Process the list of package, synchronize received list of installed packages with one in the DB
      $this->storePkgs();
      
      # Find vulnerabilities
      
      # Store the report
      $this->storeReport();
    } catch (Exception $e) {
      # Rollback the transaction
      $this->getPakiti()->getManager("DbManager")->rollback();
      throw $e;
    }
    # Commit the transaction
    $this->getPakiti()->getManager("DbManager")->commit();
  }
  
  /*
   * Process all received entries
   */
  public function prepareReport() {
    Utils::log(LOG_DEBUG, "Preparing the report", __FILE__, __LINE__);
    $tag = null;
    $hostGroup = null;
    
    switch ($this->_version) {
      case "4":
        Utils::log(LOG_DEBUG, "Client in version 4", __FILE__, __LINE__);
        # Get the rest of HTTP variables
        # host, os, arch, kernel, site, version, type, pkgs   
        $this->_host->setOs(Utils::getHttpVar(Constants::$REPORT_OS));
        $this->_host->setArch(Utils::getHttpVar(Constants::$REPORT_ARCH));
        $this->_host->setKernel(Utils::getHttpVar(Constants::$REPORT_KERNEL));            
        $this->_host->setType(Utils::getHttpVar(Constants::$REPORT_TYPE));
        break;
    }
    
    # Set the initial information about the report
    $this->_report->setReceivedOn(time());

    # Get the host object from the DB, if the host doesn't exist in the DB, this routine will create it
    $this->_host = $this->getPakiti()->getManager("HostsManager")->getHostFromReport($this->_host);

    # Get the host group
    $hostGroup = new HostGroup();
    $hostGroup->setName(Utils::getHttpVar(Constants::$REPORT_SITE));
    # If the host is already member of the host group, no operation is done
    $this->getPakiti()->getManager("HostGroupsManager")->assignHostToHostGroup($this->_host, $hostGroup);

    # Get the host tag and assign it to the host
    $tag = new Tag();
    $tag->setName(Utils::getHttpVar(Constants::$REPORT_TAG));
    # If the tag is already assigned, no operation is done
    $this->getPakiti()->getManager("TagsManager")->assignTagToHost($this->_host, $tag);

    # Parse the packages list
    $this->_pkgs = $this->parsePkgs(Utils::getHttpVar(Constants::$REPORT_PKGS));

    $this->_report->setNumOfInstalledPkgs(sizeof($this->_pkgs));
  }

  /*
   * Store the repor
   */
  public function storeReport() {
    Utils::log(LOG_DEBUG, "Storing report to the DB", __FILE__, __LINE__);
    
    $this->_report->setProcessedOn(time());
    
    $this->_report = $this->getPakiti()->getManager("ReportsManager")->createReport($this->_report, $this->_host);
  }
  
  /*
   * Stores the report to the file for further processing (only applied in asynchronous mode). 
   * In order to save resources, store directly variables from the HTTP request ($_GET or $_POST).
   */
  public function storeReportToFile() {
    Utils::log(LOG_DEBUG, "Storing report to file", __FILE__, __LINE__);
    
    # Get the host id from the DB
    $id = $this->getPakiti()->getManager("HostsManager")->getHostId($this->_host);   
    
    # Get the hashes of the previous report, but only for hosts already stored in the DB
    if ($id != -1) {
      $this->_host->setId($id);
      $lastReportHashes = $this->getPakiti()->getManager("ReportsManager")->getLastReportHashes($this->_host);
      $currentReportHeaderHash = $this->computeReportHeaderHash();
      $currentReportPkgsHash = $this->computeReportPkgsHash();
    
      # Check if the hashes are equals
      if (($lastReportHashes != null) && (($lastReportHashes[Constants::$REPORT_LAST_HEADER_HASH] == $currentReportHeaderHash) ||
        ($lastReportHashes[Constants::$REPORT_LAST_PKGS_HASH] == $currentReportPkgsHash))) {
        # Data sent by the host are the same as stored one, so we do not need to store anything
        Utils::log(LOG_DEBUG, "Feeder [host=" . $this->_host->getHostname() . "] doesn't send any new data, exiting...", __FILE__, __LINE__);    
        exit;
      }
    }
    
    # Create temporary file, filename mask: pakiti-report-[host]-[reportHost] and also store the timestamp to the file
    $timestamp = microtime(true);
    # Maximal number of attempts to open the file
    $count = 3;
        
    $filename = "pakiti-report-".$this->_host->getHostname()."-".$this->_host->getReporterHostname();
    $file = Config::$REPORTS_DIR."/".$filename;
    Utils::log(LOG_DEBUG, "Storing report [file=".$file."]", __FILE__, __LINE__);
    while (($reportFile = fopen($file,"w")) === FALSE) {
      $count--;
      
      # Wait a bit
      sleep(1);
      
      # Try to create the file three times, if the operation is not successfull, then throw the exception
      if ($count == 0) {
        Utils::log(LOG_DEBUG, "Error creating the file", __FILE__, __LINE__);
        throw new Exception("Cannot create the file containing host report [host=".$this->_host->getHostname().
        	", reporterHostname=".$this->_host->getReporterHostname()."]");
      }
      Utils::log(LOG_DEBUG, "Cannot create the file, trying again ($count attempts left)", __FILE__, __LINE__);
    }

    switch ($this->_version) {
      case "4": 
              # Prepare the header
              $header = Constants::$REPORT_TYPE."='".Utils::getHttpVar(Constants::$REPORT_TYPE)."',".
                        Constants::$REPORT_HOSTNAME."='".Utils::getHttpVar(Constants::$REPORT_HOSTNAME)."',".
              					Constants::$REPORT_OS."='".Utils::getHttpVar(Constants::$REPORT_OS)."',".
              					Constants::$REPORT_TAG."='".Utils::getHttpVar(Constants::$REPORT_TAG)."',".
              					Constants::$REPORT_KERNEL."='".Utils::getHttpVar(Constants::$REPORT_KERNEL)."',".
              					Constants::$REPORT_ARCH."='".Utils::getHttpVar(Constants::$REPORT_ARCH)."',".
              					Constants::$REPORT_SITE."='".Utils::getHttpVar(Constants::$REPORT_SITE)."',".
              					Constants::$REPORT_VERSION."='".Utils::getHttpVar(Constants::$REPORT_VERSION)."',".
              					Constants::$REPORT_REPORT."='".Utils::getHttpVar(Constants::$REPORT_REPORT)."',".
              					Constants::$REPORT_TIMESTAMP."='".$timestamp."'".
                        "\n";
              
              # Store the data          
              if (fwrite($reportFile, $header . Utils::getHttpVar(Constants::$REPORT_PKGS)) == FALSE) {
                throw new Exception("Cannot write to the file '$file'");
              }
              break;
    }

    # Finally close the handler
    fclose($reportFile);
    
    # Store the hashes into the DB, but only for hosts already stored in the DB
    if ($id != -1) {
      $this->getPakiti()->getManager("ReportsManager")->storeReportHashes($host, currentReportHeaderHash, $currentReportPkgsHash);
    }
  }

  /*
   * Process packages.
   */
  public function storePkgs() {
    Utils::log(LOG_DEBUG, "Storing the packages", __FILE__, __LINE__);
    # Load the actually stored packages from the DB, the array is already sorted by the pkgName
    $pkgs =& $this->getPakiti()->getManager("PkgsManager")->getPkgs($this->_host);
    
    $pkgsToAdd = array();
    $pkgsToUpdate = array();
    $pkgsToRemove = array();
    
    // Find packages which should be added or updated
    foreach ($this->_pkgs as $pkgName => &$value) {
      if (!array_key_exists($pkgName,$pkgs)) {
        # Package is missing in the DB
        $pkgsToAdd[$pkgName] =& $value;
      } elseif ($value['pkgVersion'] != $pkgs[$pkgName]['pkgVersion']) {
          $pkgsToUpdate[$pkgName] =& $value;
      } elseif ($value['pkgRelease'] != $pkgs[$pkgName]['pkgRelease']) {
          $pkgsToUpdate[$pkgName] =& $value;
      } elseif ($value['pkgArch'] != $pkgs[$pkgName]['pkgArch']) {
          $pkgsToUpdate[$pkgName] =& $value;
      }
    }
    
    // Find packages which should be deleted
    foreach (array_keys($pkgs) as $pkgName) {
      if (!array_key_exists($pkgName, $this->_pkgs)) {
        array_push($pkgsToRemove, $pkgName);
      }
    }
    
    if (sizeof($pkgsToAdd) > 0) $this->getPakiti()->getManager("PkgsManager")->addPkgs($this->_host, $pkgsToAdd);
    if (sizeof($pkgsToUpdate) > 0) $this->getPakiti()->getManager("PkgsManager")->updatePkgs($this->_host, $pkgsToUpdate);
    if (sizeof($pkgsToRemove) > 0) $this->getPakiti()->getManager("PkgsManager")->removePkgs($this->_host, $pkgsToRemove);
  }
  
  /*
   * Parse the long string containing list of installed packages.
   */
  protected function parsePkgs(&$pkgs) {
    Utils::log(LOG_DEBUG, "Parsing packages", __FILE__, __LINE__);
    $parsedPkgs = array();
    switch ($this->_version) {
      case "4":
        # Remove escape characters
        $pkgs = str_replace ("\\", "", $pkgs);

        # Go throught the string, each entry is separated by the new line
        $tok = strtok($pkgs, "\n");
        while ($tok !== FALSE) {
          preg_match("/'(.*)' '(.*)' '(.*)' '(.*)'/", $tok, $entries);
          $pkgName = $entries[1];
          $pkgVersion = $entries[2];
          $pkgRelease = $entries[3];
          $pkgArch = $entries[4];
          
          # If the host uses dpkg we need to split version manually to version and release by the dash
          # Suppress warnings, if the version doesn't contain dash, only version will be filled, release will be empty
          if ($this->_host->getType() == Constants::$PACKAGER_SYSTEM_DPKG) {
            @list ($pkgVersion, $pkgRelease) = explode('-',$pkgVersion);   
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
            if ($this->_host->getKernel() != $versionWithoutEpoch."-".$pkgRelease) {
              # This verion of the kernel isn't booted
              $tok = strtok("\n");
              continue;  
            }
          }
          
          # Finally iterate through all regexp which defines packages to ignore
          foreach (Config::$IGNORE_PACKAGES_PATTERNS as &$pkgNamePattern) { 
            if (preg_match("/$pkgNamePattern/",$pkgName) == 1) {
              # Skip this package, because it is in ignore list
              $tok = strtok("\n");
              continue;
            }
           }
           unset($pkgNamePattern);
          
          # $parsedPkgs['pkgName'] = array ( pkgVersion, pkgRelease, pkgArch );
          $parsedPkgs[$pkgName] = array ( 'pkgVersion' => $pkgVersion, 'pkgRelease' => $pkgRelease, 'pkgArch' => $pkgArch );
          $tok = strtok("\n");
        }
        break;
    }
    
    return $parsedPkgs;
  }
  
  /*
   * Check whether the proxy is authorized to send the reports on behalf of the host.
   */
  protected function checkProxyAuthz($proxyHostname, $proxyIp) {
    Utils::log(LOG_DEBUG, "Checking the proxy authorization", __FILE__, __LINE__);
    switch (Config::$PROXY_AUTHENTICATION_MODE) {
      case Constants::$PROXY_AUTHN_MODE_HOSTNAME: 
        if (in_array(Utils::getHttpVar(Constants::$REPORT_HOSTNAME), Config::$PROXY_ALLOWED_PROXIES)) {
          return TRUE;
        } else {
          return FALSE;
        }
        break;
      case Constants::$PROXY_AUTHN_MODE_IP:
        if (in_array(Utils::getHttpVar(Constants::$REPORT_IP), Config::$PROXY_ALLOWED_PROXIES)) {
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
  protected function computeReportHeaderHash() {
    Utils::log(LOG_DEBUG, "Computing the hash of the report header", __FILE__, __LINE__);
     switch ($this->_version) {
      case "4":
        $header = Utils::getHttpVar(Constants::$REPORT_TYPE).
                  Utils::getHttpVar(Constants::$REPORT_HOSTNAME).
              		Utils::getHttpVar(Constants::$REPORT_OS).
              		Utils::getHttpVar(Constants::$REPORT_TAG).
              		Utils::getHttpVar(Constants::$REPORT_KERNEL).
              		Utils::getHttpVar(Constants::$REPORT_ARCH).
              		Utils::getHttpVar(Constants::$REPORT_SITE).
              		Utils::getHttpVar(Constants::$REPORT_VERSION).
              		Utils::getHttpVar(Constants::$REPORT_REPORT);
              		
              		return $this->computeHash($header);
              		break;
     }
  }
  
  /* 
   * Compute hash of the list of packages
   */
  protected function computeReportPkgsHash() {
    Utils::log(LOG_DEBUG, "Computing the hash of the list of the packages", __FILE__, __LINE__);
    return $this->computeHash(Utils::getHttpVar(Constants::$REPORT_PKGS));
  }
  
  /*
   * Compute the hash, currently MD5
   */
  protected function computeHash($string) {
    return md5($string);  
  }
  
  /* 
   * Make diff of the two arrays
   */
  protected function array_compare_recursive($array1, $array2) {
    $diff = array();
    foreach ($array1 as $key => &$value) {
      if (!array_key_exists($key,$array2)) {
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