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

class ArchDao {
  private $db;
  
  public function __construct(DbManager &$dbManager) {
    $this->db = $dbManager;  
  }
  
  /*******************
   * Public functions
   *******************/
  
  /*
   * Stores the arch in the DB
   */
  public function create(Arch &$arch) {
    $this->db->query(
      "insert into Arch set
      	name='".$this->db->escape($arch->getName())."'");
    
    # Set the newly assigned id
    $arch->setId($this->db->getLastInsertedId());
  }
  
  /*
   * Get the arch by its ID
   */
  public function getById($id) {
    if (!is_numeric($id)) return null;
    return $this->getBy($id, "id");
  }
  
  /*
   * Get the arch by its name
   */
  public function getByName($name) {
    return $this->getBy($name, "name");
  }
  
	/*
	 * Get the arch ID by its name
	 */ 
  public function getIdByName($name) {
    $id = $this->db->queryToSingleValue(
    	"select 
    		id
      from 
      	Arch 
      where
      	name='".$this->db->escape($name)."'");
    if ($id == null) {
      return -1;
    }
    return $id;
  }
  
  public function getArchsIds($orderBy, $pageSize, $pageNum) {
    $sql = "select id from Arch order by name";
    
    if ($pageSize != -1 && $pageNum != -1) {
      $offset = $pageSize*$pageNum;
      $sql .= " limit $offset,$pageSize";
    }
    
    return $this->db->queryToSingleValueMultiRow($sql);
  }
  
  /*
   * Update the arch in the DB
   */
  public function update(Arch &$arch) {
    $this->db->query(
      "update Arch set
      	name='".$this->db->escape($arch->getName())."
      where id=".$this->db->escape($arch->getId()));
  }
  
  /*
   * Delete the arch from the DB
   */
  public function delete(Arch &$arch) {
    $this->db->query(
      "delete from Arch where id=".$this->db->escape($arch->getId()));
  }
  
  /*********************
   * Protected functins
   *********************/
  
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
    		id as _id, name as _name
      from 
      	Arch 
      where
      	$where"
      , "Arch");
  }
 
}
?>
