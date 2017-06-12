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
class HostsManager extends DefaultManager {

  /**
  * Create if not exist, else update it
  * @return false if already exist
  */
  public function storeHost(Host &$host) {
    Utils::log(LOG_DEBUG, "Storing the host", __FILE__, __LINE__);
    if ($host == null) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid");
    }

    # Get the osId
    $os = new Os();
    $os->setName($host->getOsName());
    $this->getPakiti()->getManager("OsesManager")->storeOs($os);
    $host->setOsId($os->getId());
    $host->setOs($os);

    # Get the archId
    $arch = new Arch();
    $arch->setName($host->getArchName());
    $this->getPakiti()->getManager("ArchsManager")->storeArch($arch);
    $host->setArchId($arch->getId());
    $host->setArch($arch);

    # Get the domainId
    $domain = new Domain();
    $domain->setName($host->getDomainName());
    $this->getPakiti()->getManager("DomainsManager")->storeDomain($domain);
    $host->setDomainId($domain->getId());
    $host->setDomain($domain);

    $new = false;
    $dao = $this->getPakiti()->getDao("Host");
    $hostId = $dao->getIdByHostnameIpReporterHostnameReporterIp($host->getHostname(), $host->getIp(), $host->getReporterHostname(), $host->getReporterIp());
    if ($hostId != -1) {
      $host->setId($hostId);
      # Host exist, so update it
      $this->getPakiti()->getDao("Host")->update($host);
    } else {
      # Host is missing, so store it
      $this->getPakiti()->getDao("Host")->create($host);
      $new = true;
    }
    return $new;
  }

  /*
   * Try to find host using hostname, ip, reporterHostname and reporterIp
   */
  public function getHostId($hostname, $ip, $reporterHostname, $reporterIp) {
    Utils::log(LOG_DEBUG, "Getting the host ID", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Host")->getIdByHostnameIpReporterHostnameReporterIp($hostname, $ip, $reporterHostname, $reporterIp);
  }
  
  /*
   * Get the host by its ID
   */
  public function getHostById($id, $userId = -1) {
    Utils::log(LOG_DEBUG, "Getting the host by its ID [id=$id]", __FILE__, __LINE__);
    $host =& $this->getPakiti()->getDao("Host")->getById($id, $userId);
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
  public function getHosts($orderBy = null, $pageSize = -1, $pageNum = -1, $startsWith = null, $userId = -1, $directlyAssignedToUser = false) {
    Utils::log(LOG_DEBUG, "Getting all hosts", __FILE__, __LINE__);
    $hostsIds = $this->getPakiti()->getDao("Host")->getHostsIds($orderBy, $pageSize, $pageNum, $startsWith, $userId, $directlyAssignedToUser);
    
    $hosts = array();
    foreach ($hostsIds as $hostId) {
      array_push($hosts, $this->getHostById($hostId));
    }
    
    return $hosts;
  }

  public function getHostsByHostGroupId($hostGroupId) {
    Utils::log(LOG_DEBUG, "Getting host IDs by HostGroup ID[".$hostGroupId."]", __FILE__, __LINE__);
    $ids = $this->getPakiti()->getDao("Host")->getIdsByHostGroupId($hostGroupId);

    $hosts = array();
    foreach ($ids as $id) {
      array_push($hosts, $this->getPakiti()->getDao("Host")->getById($id));
    }
    
    return $hosts;
  }

  /*
   * Get hosts count
   */
  public function getHostsCount($userId = -1) {
    Utils::log(LOG_DEBUG, "Getting hosts count", __FILE__, __LINE__);
    return sizeof($this->getPakiti()->getDao("Host")->getHostsIds(null, -1, -1, null, $userId));
  }
  
  /*
   * Delete the host
   */
  public function deleteHost(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    // Related data in the db is delete using delete on cascade
    return $this->getPakiti()->getDao("Host")->delete($host);
  }

  public function setLastReportId(Host &$host, Report &$report) {
    if ($host == null || $host->getId() == -1 || $report == null || $report->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
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
    foreach ($osesIds as $osId) {
      array_push($oses, $this->getPakiti()->getDao("Os")->getById($osId));
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
    foreach ($archsIds as $archId) {
      array_push($archs, $this->getPakiti()->getDao("Arch")->getById($archId));
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



}
?>
