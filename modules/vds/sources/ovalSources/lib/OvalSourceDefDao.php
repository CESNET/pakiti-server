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

class OvalSourceDefDao {
  private $db;
  
  public function __construct(DbManager &$dbManager) {
    $this->db = $dbManager;  
  }
  
  /*******************
   * Public functions
   *******************/
  
  /*
   * Stores the ovalSourceDef in the DB
   */
  public function create(OvalSourceDef &$ovalSourceDef) {
    $this->db->query(
      "insert into OvalSourceDef set
      	name='".mysql_real_escape_string($ovalSourceDef->getName())."',
      	uri='".mysql_real_escape_string($ovalSourceDef->getUri())."',
      	enabled=".mysql_real_escape_string($ovalSourceDef->getEnabled()).",
      	lastChecked='".mysql_real_escape_string($ovalSourceDef->getLastChecked())."',
      	ovalId=".mysql_real_escape_string($ovalSourceDef->getOvalId()));
    
    # Set the newly assigned id
    $ovalSourceDef->setId($this->db->getLastInsertedId());
  }
  
  /*
   * Get the ovalSourceDef by its ID
   */
  public function getById($id) {
    return $this->getBy(id, "id");
  }
  
  /*
   * Get the ovalSourceDef by its name
   */
  public function getByName($name) {
    return $this->getBy($name, "name");
  }
  
	/*
	 * Get the ovalSourceDef ID by its name
	 */ 
  public function getIdByName($name) {
    $id = $this->db->queryToSingleValue(
    	"select 
    		id
      from 
      	OvalSourceDef 
      where
      	name='".mysql_real_escape_string($name)."'");
    if ($id == null) {
      return -1;
    }
    return $id;
  }
  
  /*
   * Update the ovalSourceDef in the DB
   */
  public function update(OvalSourceDef &$ovalSourceDef) {
    $this->db->query(
      "update OvalSourceDef set
      	name='".mysql_real_escape_string($ovalSourceDef->getName()).",
      	uri='".mysql_real_escape_string($ovalSourceDef->getUri())."',
      	enabled=".mysql_real_escape_string($ovalSourceDef->getEnabled()).",
      	lastChecked='".mysql_real_escape_string($ovalSourceDef->getLastChecked())."',
      	ovalId=".mysql_real_escape_string($ovalSourceDef->getOvalId())." 
      where id=".mysql_real_escape_string($ovalSourceDef->getId()));
  }
  
  /*
   * Delete the ovalSourceDef from the DB
   */
  public function delete(OvalSourceDef &$ovalSourceDef) {
    $this->db->query(
      "delete from OvalSourceDef where id=".mysql_real_escape_string($ovalSourceDef->getId()));
  }
  
  /*
   * Enable the sourceDef
   */
  public function enable(OvalSourceDef &$ovalSourceDef) {
  	$this->db->query(
      "update OvalSourceDef set enabled=".Constants::$ENABLED." where id=".mysql_real_escape_string($ovalSourceDef->getId()));
  }
  
	/*
   * Disable the sourceDef
   */
  public function disable(OvalSourceDef &$ovalSourceDef) {
  	$this->db->query(
      "update OvalSourceDef set enabled=".Constants::$DISABLED." where id=".mysql_real_escape_string($ovalSourceDef->getId()));
  }
  
  /*
   * Get all definitions associated to the OvalSource
   */
  public function getDefs(SourceInterface &$ovalSource) {
    $ids = $this->db->queryToMultiRow(
      "select id from OvalSourceDef where ovalId=".
        mysql_real_escape_string($ovalSource->getId())." and enabled=".Constants::$ENABLED);
    
    if ($ids == null) {
      return null;
    }
    $ret = array();
    foreach ($ids as $id) {
       array_push($ret, $this->getById($id)); 
    }
    
    return $ret;
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
      $where = "id=".mysql_real_escape_string($value);
    } else if ($type == "name") {
      $where = "name='".mysql_real_escape_string($value)."'";
    } else {
      throw new Exception("Undefined type of the getBy");
    }
    return $this->db->queryObject(
    	"select 
    		id as _id, name as _name, uri as _uri, enabled as _enabled,
    		lastChecked as _lastChecked, ovalId as _ovalId 
      from 
      	OvalSourceDef 
      where
      	$where"
      , "OvalSourceDef");
  }
 
}
?>