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

class DomainDao {
  private $db;
  
  public function __construct(DbManager &$dbManager) {
    $this->db = $dbManager;  
  }
  
  public function create(Domain &$domain) {
    $this->db->query(
      "insert into Domain set
      	name='".$this->db->escape($domain->getName())."'");
    
    # Set the newly assigned id
    $domain->setId($this->db->getLastInsertedId());
  }
  
  public function getById($id) {
    if (!is_numeric($id)) return null;
    return $this->getBy($id, "id");
  }
  
  public function getByName($name) {
    return $this->getBy($name, "name");
  }
  
  public function getIdByName($name) {
    $id = $this->db->queryToSingleValue(
    	"select 
    		id
      from 
      	Domain 
      where
      	name='".$this->db->escape($name)."'");
    if ($id == null) {
      return -1;
    }
    return $id;
  }
  
  public function getDomainsIds($orderBy, $pageSize, $pageNum) {
    $sql = "select id from Domain order by name";
    
    if ($pageSize != -1 && $pageNum != -1) {
      $offset = $pageSize*$pageNum;
      $sql .= " limit $offset,$pageSize";
    }
    
    return $this->db->queryToSingleValueMultiRow($sql);
  }
  
  public function update(Domain &$domain) {
    $this->db->query(
      "update Domain set
      	name='".$this->db->escape($domain->getName())."
      where id=".$domain->getId());
  }
  
  public function delete(Domain &$domain) {
    $this->db->query(
      "delete from Domain where id=".$domain->getId());
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
      	Domain 
      where
      	$where"
      , "Domain");
  }
}
?>
