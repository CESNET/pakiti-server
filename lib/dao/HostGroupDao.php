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

class HostGroupDao {
  private $db;
  
  public function __construct(DbManager &$dbManager) {
    $this->db = $dbManager;  
  }
  
  public function create(HostGroup &$hostGroup) {
    Utils::log(LOG_DEBUG, "Creating [hostGroup=" . $hostGroup->getName() . "]", __FILE__, __LINE__);
    $this->db->query(
      "insert into HostGroup set
      	name='".$this->db->escape($hostGroup->getName())."'");
    
    # Set the newly assigned id
    $hostGroup->setId($this->db->getLastInsertedId());
  }
  
  public function getById($id, $userId = -1) {
    if (!is_numeric($id)) return null;

    if($userId != -1){
      $join = "inner join UserHostGroup on HostGroup.id = UserHostGroup.hostGroupId";
      $conditions = "UserHostGroup.userId = $userId and";
    } else {
      $join = "";
      $conditions = "";
    }
    
    return $this->db->queryObject("
      select id as _id, name as _name
      from HostGroup
      $join
      where
      $conditions
      id = $id
    ", "HostGroup");
  }
  
  public function getByName($name) {
    return $this->getBy($name, "name");
  }

  public function getByHostId($hostId) {
    $sql = "select HostGroup.id as _id, HostGroup.name as _name
      from HostHostGroup
      inner join HostGroup on HostHostGroup.hostGroupId = HostGroup.id
      where HostHostGroup.hostId=$hostId";
    return $this->db->queryObjects($sql, "HostGroup");
  }
  
  public function getIdByName($name) {
    $id = $this->db->queryToSingleValue(
    	"select 
    		id
      from 
      	HostGroup 
      where
      	name='".$this->db->escape($name)."'");
    if ($id == null) {
      return -1;
    }
    return $id;
  }
  
  public function getHostGroupsIds($orderBy = null, $pageSize = -1, $pageNum = -1, $userId = -1) {
    $sql = "select HostGroup.id as _id from HostGroup";
    
    if($userId != -1){
      $sql .= " inner join UserHostGroup on HostGroup.id = UserHostGroup.hostGroupId where UserHostGroup.userId = $userId";
    }
    
    if($orderBy == null) {
      $sql .= " order by HostGroup.name";
    } else {
      $sql .= " order by HostGroup.$orderBy";
    }
    
    if ($pageSize != -1 && $pageNum != -1) {
      $offset = $pageSize*$pageNum;
      $sql .= " limit $offset,$pageSize";
    }
    
    return $this->db->queryToSingleValueMultiRow($sql);
  }

  public function getHostGroupsCount() {
    $sql = "select count(id) from HostGroup";
    
    return $this->db->queryToSingleValue($sql);
  }

  public function getHostsCount($hostGroupId) {
    $sql = "select count(hostId) from HostHostGroup where hostGroupId=$hostGroupId";
    
    return $this->db->queryToSingleValue($sql);
  }

  public function getHostsIds($hostGroupId, $orderBy, $pageSize, $pageNum) {  
    // Because os and arch are ids to other tables, we have to do different sorting

     switch ($orderBy) {
      case "os":
        $sql = "select HostHostGroup.hostId from Host, Os, HostHostGroup where Host.osId=Os.id and HostHostGroup.hostId=Host.id and HostHostGroup.hostGroupId=$hostGroupId order by Os.name";
        break;
      case "arch":
        $sql = "select HostHostGroup.hostId from Host, Arch, HostHostGroup where Host.archId=Arch.id and HostHostGroup.hostId=Host.id and HostHostGroup.hostGroupId=$hostGroupId order by Arch.name";
        break;
      case "kernel":
        $sql = "select HostHostGroup.hostId from Host, HostHostGroup where HostHostGroup.hostId=Host.id and HostHostGroup.hostGroupId=$hostGroupId order by Host.kernel";
        break; default:
        $sql = "select HostHostGroup.hostId from HostHostGroup, Host where HostHostGroup.hostId=Host.id and HostHostGroup.hostGroupId=$hostGroupId order by Host.hostname";
    }

    if ($pageSize != -1 && $pageNum != -1) {
      $offset = $pageSize*$pageNum;
      $sql .= " limit $offset,$pageSize";
    }

    return $this->db->queryToSingleValueMultiRow($sql);
  }

  public function update(HostGroup &$hostGroup) {
    Utils::log(LOG_DEBUG, "Updating [hostGroup=" . $hostGroup->getName() . "]", __FILE__, __LINE__);
    $this->db->query(
      "update HostGroup set
      	name='".$this->db->escape($hostGroup->getName())."
      where id=".$hostGroup->getId());
  }
  
  public function delete(HostGroup &$hostGroup) {
    Utils::log(LOG_DEBUG, "Deleting [hostGroup=" . $hostGroup->getName() . "]", __FILE__, __LINE__);
    $this->db->query(
      "delete from HostGroup where id=".$hostGroup->getId());
  }
  
  public function removeHostFromHostGroups($hostId) {
     $this->db->query(
      "delete from HostHostGroup where hostId={$hostId}"); 
  }
  
	 /*
   * We can get the data by ID or name
   */
  protected function getBy($value, $type) {
    $where = "";
    if ($type == "id") {
      $where = "id=".$this->db->escape($value);
    } else if ($type == "name") {
      $where = "name='".$this->db->escape($value)."'";
    } else {
      throw new Exception("Undefined type of the getBy");
    }
    return $this->db->queryObject(
    	"select 
    		id as _id,
		name as _name
      from 
      	HostGroup 
      where
      	$where"
      , "HostGroup");
  }
}
?>
