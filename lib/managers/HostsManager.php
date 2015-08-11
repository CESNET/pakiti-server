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

class HostsManager extends DefaultManager {
  
  /*
   * Stores the Host from the report into the DB
   */
  public function storeHostFromReport(Host &$host) {
    Utils::log(LOG_DEBUG, "Storing the host from the report", __FILE__, __LINE__);
    if ($host == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    } 
    
    $this->getPakiti()->getDao("Host")->create($host);
    
    return $host;
  }
  
  /*
   * Try to find host from the report in the DB
   */
  public function getHostFromReport(Host &$host, &$pkgs) {
    Utils::log(LOG_DEBUG, "Getting the host from the report", __FILE__, __LINE__);
    if ($host == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    } 
    
    # Get the osId
    $host->setOsName($this->guessOs($host, $pkgs));
    $osDao = $this->getPakiti()->getDao("Os");
    $osId = $osDao->getIdByName($host->getOsName());
    if ($osId == -1) {
      # Os is missing, so store it
      $os = new Os();
      $os->setName($host->getOsName());
      $osDao->create($os);
      $osId = $os->getId();
    } else {
      $os = $osDao->getById($osId);
    }
    $host->setOsId($osId);
    $host->setOs($os);
    
    # Get the archId
    $archDao = $this->getPakiti()->getDao("Arch");
    $archId = $archDao->getIdByName($host->getArchName());
    if ($archId == -1) {
      # Arch is missing, so store it
      $arch = new Arch();
      $arch->setName($host->getArchName());
      $archDao->create($arch);
      $archId = $arch->getId();
    } else {
      $arch = $archDao->getById($archId);
    }
    $host->setArchId($archId);
    $host->setArch($arch);
    
    # Get the domainId
      # Guess the domain name from the reporterHostname
    $domainName = $this->guessDomain($host->getHostname());
    $domainDao = $this->getPakiti()->getDao("Domain");
    $domainId = $domainDao->getIdByName($domainName);
    if ($domainId == -1) {
      # Domain is missing, so store it
      $domain = new Domain();
      $domain->setName($domainName);
      $domainDao->create($domain);
      $domainId = $domain->getId();
    } else {
      $domain = $domainDao->getById($domainId);
    }
    $host->setDomainId($domainId);
    $host->setDomain($domain);
    
    # Try to find the host in the DB
    $host->setId($this->getHostId($host));
    if ($host->getId() != -1) {
      # Update entries
      $this->getPakiti()->getDao("Host")->update($host);
      return $host;
    } else {
      return $this->storeHostFromReport($host);
    }
  }
  
	/*
   * Try to find host using hostname, reporterHostnem, ip and reporterIp
   */
  public function getHostId(Host &$host) {
    if ($host == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid.");
    }
    
    Utils::log(LOG_DEBUG, "Getting the host ID", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Host")->getId($host);
  }
  
  /*
   * Get the host by its ID
   */
  public function getHostById($id) {
    Utils::log(LOG_DEBUG, "Getting the host by its ID [id=$id]", __FILE__, __LINE__);
    $host =& $this->getPakiti()->getDao("Host")->getById($id);
    if (is_object($host)) {
      $host->setArch($this->getPakiti()->getDao("Arch")->getById($host->getArchId()));
      $host->setOs($this->getPakiti()->getDao("Os")->getById($host->getOsId()));
      $host->setDomain($this->getPakiti()->getDao("Domain")->getById($host->getDomainId()));
    } else return null;
    
    return $host;
  }
  
/*
   * Get the host by its hostname
   */
  public function getHostByHostname($hostname) {
    Utils::log(LOG_DEBUG, "Getting the host by its hostname [hostname=$hostname]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Host")->getByHostname($hostname);  
  }
  
  /*
   * Get all hosts
   */
  public function getHosts($orderBy, $pageSize = -1, $pageNum = -1) {
    Utils::log(LOG_DEBUG, "Getting all hosts", __FILE__, __LINE__);
    $hostsIds =& $this->getPakiti()->getDao("Host")->getHostsIds($orderBy, $pageSize, $pageNum);
    
    $hosts = array();
    if ($hostsIds) {
      foreach ($hostsIds as $hostId) {
        array_push($hosts, $this->getHostById($hostId));
      }
    }
    
    return $hosts;
  }

  /*
   * Get hosts by theirs first letter
   */
  public function getHostsByFirstLetter($firstLetter) {
    Utils::log(LOG_DEBUG, "Getting hosts by firstLetter", __FILE__, __LINE__);
    $hostsIds =& $this->getPakiti()->getDao("Host")->getHostsIdsByFirstLetter($firstLetter);
    
    $hosts = array();
    if(is_array($hostsIds))
    {
        foreach ($hostsIds as $hostId) {
          array_push($hosts, $this->getHostById($hostId));
        }
    }
    
    return $hosts;
  }

  /*
   * Get Host by tag name
   */
  public function getHostsByTagName($tagName)
  {
    $tagId = $this->getPakiti()->getManager("TagsManager")->getTagIdByName($tagName);
    if ($tagId == -1) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("The tag $tagName does not exist");
    }


    $sql = "select Host.id from Host join HostTag on Host.id=HostTag.hostId where
          HostTag.tagId={$tagId}";

    $hostIdsDb =& $this->getPakiti()->getManager("DbManager")->queryToMultiRow($sql);
    $hosts = array();
    if ($hostIdsDb != null) {
      foreach ($hostIdsDb as $hostIdDb) {
        $host = $this->getHostById($hostIdDb["id"]);
        array_push($hosts, $host);
      }
    }
    return $hosts;
  }

  /*
   * Get hosts count
   */
  public function getHostsCount() {
    Utils::log(LOG_DEBUG, "Getting hosts count", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Host")->getHostsIdsCount();
  }
  
  /*
   * Delete the host
   */
  public function deleteHost(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    // Related data in the db is delete using delete on cascade
    return $this->getPakiti()->getDao("Host")->delete($host);
  }

  public function setLastReportId(Host &$host, Report &$report) {
    if ($host == null || $host->getId() == -1 || $report == null || $report->getId() == -1) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host or Report pobject is not valid or Host.id or Report.id is not set");
    }
     return $this->getPakiti()->getDao("Host")->setLastReportId($host->getId(), $report->getId()); 
  }
 
  /* 
   * Get list of all oses
   */
  public function getOses($orderBy, $pageSize = -1, $pageNum = -1) {
    Utils::log(LOG_DEBUG, "Getting all oses", __FILE__, __LINE__);
    $osesIds =& $this->getPakiti()->getDao("Os")->getOsesIds($orderBy, $pageSize, $pageNum);

    $oses = array();
    if ($osesIds) {
      foreach ($osesIds as $osId) {
	array_push($oses, $this->getPakiti()->getDao("Os")->getById($osId));
      }
    }

    return $oses;
  }



  /* 
   * Get list of all archs
   */
  public function getArchs($orderBy, $pageSize = -1, $pageNum = -1) {
    Utils::log(LOG_DEBUG, "Getting all archs", __FILE__, __LINE__);
    $archsIds =& $this->getPakiti()->getDao("Arch")->getArchsIds($orderBy, $pageSize, $pageNum);

    $archs = array();
    if ($archsIds) {
      foreach ($archsIds as $archId) {
	array_push($archs, $this->getPakiti()->getDao("Arch")->getById($archId));
      }
    }

    return $archs;
  }

  /* 
   * Get arch id
   */
  public function getArchId($name) {
    Utils::log(LOG_DEBUG, "Getting arch Id by Name", __FILE__, __LINE__);
    $arch =& $this->getPakiti()->getDao("Arch")->getByName($name);

    return $arch->getId();
  }

  /* 
   * Get arch
   */
  public function getArch($name) {
    Utils::log(LOG_DEBUG, "Getting arch by Name", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Arch")->getByName($name);
  }


  /* 
   * Create arch
   */
  public function createArch($name) {
    Utils::log(LOG_DEBUG, "Creating arch $name", __FILE__, __LINE__);
    $arch = new Arch();
    $arch->setName($name);
    $this->getPakiti()->getDao("Arch")->create($arch);

    return $arch;
  }


//  /*
//   * Get osGroup
//   */
//  public function getOsGroup($name) {
//    Utils::log(LOG_DEBUG, "Getting osGroup by Name", __FILE__, __LINE__);
//    return $this->getPakiti()->getDao("OsGroup")->getByName($name);
//  }


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
  protected function guessOs(Host &$host, &$pkgs) {
    Utils::log(LOG_DEBUG, "Guessing the OS", __FILE__, __LINE__);
    
    $osFullName = "unknown";
    # Find the package which represents the OS name/release
    foreach (Constants::$OS_NAMES_DEFINITIONS as $pkgName => &$osName) {
      if (array_key_exists($pkgName, $pkgs)) {
        // Remove epoch if there is one
        $osFullName = $osName . " " . Utils::removeEpoch($pkgs[$pkgName]["pkgVersion"]);
      }
    }
    unset($osName);  

    if ($osFullName == "unknown") {
      # Try to guess the OS name from the data sent by the clent itself?    
      if ($host->getOsName() != "" || $host->getOsName() != "unknown") {

	# The Pakiti client has sent the OS name, so canonize it
	foreach (Constants::$OS_NAMES_MAPPING as $pattern => $replacement) {
	  # Apply regex rules on the Os name sent by the client
	  $tmpOsName = preg_replace("/".$pattern."/i", $replacement, $host->getOsName(), 1, $count);

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
	  fwrite($fh, date(DATE_RFC822) . ": " . $host->getOsName() . "\n");
	  fclose($fh);
	}
      }
    }
    return $osFullName; 
  }
}
?>
