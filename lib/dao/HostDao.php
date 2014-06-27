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

class HostDao {
  private $db;
  
  public function __construct(DbManager &$dbManager) {
    $this->db = $dbManager;  
  }
  
  public function create(Host &$host) {
    if ($host == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid");
    }
    $this->db->query(
      "insert into Host set
      	hostname='".$this->db->escape($host->getHostname())."',
      	ip='".$this->db->escape($host->getIp())."',
      	reporterIp='".$this->db->escape($host->getReporterIp())."',
      	reporterHostname='".$this->db->escape($host->getReporterHostname())."',
      	kernel='".$this->db->escape($host->getKernel())."',
      	osId=".$this->db->escape($host->getOsId()).",
      	archId=".$this->db->escape($host->getArchId()).",
      	domainId=".$this->db->escape($host->getDomainId()).",
      	lastReportId=".($host->getLastReportId() == -1 ? "NULL" : $this->db->escape($host->getLastReportId())).",
      	type='".$this->db->escape($host->getType())."',
        ownRepositoriesDef=".$this->db->escape($host->getOwnRepositoriesDef()));
    
    # Set the newly assigned id
    $host->setId($this->db->getLastInsertedId());
    Utils::log(LOG_DEBUG, "Host created", __FILE__, __LINE__);
  }
  
  public function getId(Host &$host) {
    if ($host == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid");
    }
    $id = $this->db->queryToSingleValue(
    	"select 
    		id 
      from 
      	Host
      where
      	hostname='".$this->db->escape($host->getHostname())."' and 
      	ip='".$this->db->escape($host->getIp())."' and 
    		reporterIp='".$this->db->escape($host->getReporterIp())."' and 
      	reporterHostname='".$this->db->escape($host->getReporterHostname())."'");
    if ($id == null) {
      return -1;
    }
    return $id;
  }
  
  public function getById($id) {
    # Try to find the host in the DB
    if (!is_numeric($id)) return null;

    return $this->db->queryObject(
    	"select 
    		h.id as _id, 
    		h.hostname as _hostname,
    		h.ip as _ip, 
    		h.reporterIp as _reporterIp,
    		h.reporterHostname as _reporterHostname,
    		h.kernel as _kernel,
    		h.type as _type,
    		h.ownRepositoriesDef as _ownRepositoriesDef,
    		h.osId as _osId,
    		h.archId as _archId,
    		h.domainId as _domainId,
    		h.lastReportId as _lastReportId 
      from 
      	Host h 
      where
      	h.id=$id"
      , "Host");
  }
  
  public function getByHostname($hostname) {
    $hostId = $this->db->queryToSingleValue("select id from Host where hostname='$hostname'");
    return $this->getById($hostId);  
  }
  
  public function getHostsIds($orderBy, $pageSize, $pageNum) {
    // Because os and arch are ids to other tables, we have to do different sorting
    switch ($orderBy) {
      case "os":
        $sql = "select Host.id from Host left join Os on Host.osId=Os.id order by Os.name";
        break;
      case "arch":
        $sql = "select Host.id from Host left join Arch on Host.archId=Arch.id order by Arch.name";
        break;
      case "hostGroup":
	$sql = "select Host.id from Host, HostHostGroup, HostGroup where Host.id=HostHostGroup.hostId and HostHostGroup.hostGroupId=HostGroup.id order by HostGroup.name";
	break;
      default:
        $sql = "select Host.id from Host order by $orderBy"; 
    }
    if ($pageSize != -1 && $pageNum != -1) {
      $offset = $pageSize*$pageNum;
      $sql .= " limit $offset,$pageSize";
    }
    $hostsIds = $this->db->queryToSingleValueMultiRow($sql);
    
    return $hostsIds;
  }

  public function getHostsIdsByFirstLetter($firstLetter) {
    $firstLetter = strtolower($firstLetter);
    $sql = "select Host.id from Host where lower(hostname) like '$firstLetter%'";
    return $this->db->queryToSingleValueMultiRow($sql);
  }

  //public function getHostsByHostName($

  public function getHostsIdsCount() {
    $sql = "select count(Host.id) from Host";
    
    return $this->db->queryToSingleValue($sql);
  }
  
  public function update(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    $dbHost = $this->getById($host->getId());
    if ($dbHost == null) {
      throw new Exception("Host cannot be retreived from the DB");
    }
    
    $entries = array();
    if ($host->getHostname() != $dbHost->getHostname()) {
      $entries['hostname'] = "'".$this->db->escape($host->getHostname())."'";
    }
    if ($host->getIp() != $dbHost->getIp()) {
      $entries['ip'] = "'".$this->db->escape($host->getIp())."'";
    }
    if ($host->getReporterHostname() != $dbHost->getReporterHostname()) {
      $entries['reporterHostname'] = "'".$this->db->escape($host->getReporterHostname())."'";
    }
    if ($host->getReporterIp() != $dbHost->getReporterIp()) {
      $entries['reporterIp'] = "'".$this->db->escape($host->getReporterIp())."'";
    }
    if ($host->getKernel() != $dbHost->getKernel()) {
      $entries['kernel'] ="'". $this->db->escape($host->getKernel())."'";
    }
    if ($host->getOsId() != $dbHost->getOsId()) {
      $entries['osId'] = $this->db->escape($host->getOsId());
    }
    if ($host->getArchId() != $dbHost->getArchId()) {
      $entries['archId'] = $this->db->escape($host->getArchId());
    }
    if ($host->getDomainId() != $dbHost->getDomainId()) {
      $entries['domainId'] = $this->db->escape($host->getDomainId());
    }
    if ($host->getType() != $dbHost->getType()) {
      $entries['type'] = "'".$this->db->escape($host->getType())."'";
    }
    if ($host->getOwnRepositoriesDef() != $dbHost->getOwnRepositoriesDef()) {
      $entries['ownRepositoriesDef'] = "'".$this->db->escape($host->getOwnRepositoriesDef())."'";
    }
    
    if (sizeof($entries) > 0) {
      # Construct SQL query
      $sql = "update Host set";
      $sqle = "";
      foreach ($entries as $column => $value) {
        $sqle .= " $column=$value,";
      }
      # Remove last comma
      $sqle = preg_replace('/(.*),$/', '\1', $sqle);
      
      $sql .= $sqle . " where id=".$host->getId();
      
      $this->db->query($sql);
    
      Utils::log(LOG_DEBUG, "Host updated", __FILE__, __LINE__);
    }
  }
  
  public function delete(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    $this->db->query(
      "delete from Host where id=".$host->getId());
    Utils::log(LOG_DEBUG, "Host deleted", __FILE__, __LINE__);
  }
  
  public function setLastReportId($hostId, $reportId) {
    $this->db->query("update Host set lastReportId=$reportId where id=$hostId");
  }
}
?>
