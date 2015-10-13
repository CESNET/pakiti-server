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

final class DbManager {
  private $_dbLink;

  function __construct() {
    $this->dbConnect();
  }
  
  /*
   * Transaction methods
   */
  public function begin() {
    if (!$this->_dbLink->query("begin")) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("SQL transaction begin failed: " . $this->_dbLink->error);
    }
    Utils::log(LOG_DEBUG, "Starting transaction", __FILE__, __LINE__);
  }
  public function commit() {
    if (!$res = $this->_dbLink->query("commit")) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("SQL transaction commit failed: " . $this->_dbLink->error);
    }
    Utils::log(LOG_DEBUG, "Commiting transaction", __FILE__, __LINE__);
  }
  
  public function rollback() {
    if (!$res = $this->_dbLink->query("rollback")) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("SQL transaction rollback failed: " . $this->_dbLink->error);
    }
    Utils::log(LOG_DEBUG, "Transaction rollback!", __FILE__, __LINE__);
  }
    
  /*
   * Escape SQL parameters
   */
  public function escape($string) {
    return $this->_dbLink->real_escape_string(htmlspecialchars($string));
  }

  /*
   * Used only for INSERT, UPDATE and DELETE
   */
  public function query($sql) {
    $this->rawQuery($sql);
  }
  
  /*
   * Returns single value
   */
  public function queryToSingleValue($sql) {
    $res = $this->rawQuery($sql);
    $row = $this->rawSingleRowFetch($res);
    if ($row == null || !isset($row[0])) {
      return null;
    }
    return $row[0];
  }
  
  /*
   * Returns single row
   */
  public function queryToSingleRow($sql) {
    $res = $this->rawQuery($sql);
    return $this->rawSingleRowFetch($res);
  }
  
  /* 
   * Returns single object
   */
  public function queryObject($sql, $class, $params = null) {
    $res = $this->rawQuery($sql);
    return $this->rawSingleObjectFetch($res, $class, $params);
  }
  
  /* 
   * Returns multiple objects
   */
  public function queryObjects($sql, $class, $params = null) {
    $res = $this->rawQuery($sql);
    return $this->rawMultiObjectFetch($res, $class, $params);
  }
  public function queryToMultiRow($sql) {
    $res = $this->rawQuery($sql);
    return $this->rawMultiRowFetch($res);
  }
  
  public function queryToSingleValueMultiRow($sql) {
    $res = $this->rawQuery($sql); 
    return $this->rawSingleValueMultiRowFetch($res);
  }
  
  /*
   * Get last inserted id
   */
  public function getLastInsertedId() {
    return $this->_dbLink->insert_id;
  }
  
  /*
   * Get the number of affected rows
   */
  public function getNumberOfAffectedRows() {
    return $this->_dbLink->affected_rows;
  }
  
  /*************************************
   * Protected methods
   *************************************/
  protected function dbConnect() {
    # Create DB connection
    $this->_dbLink = new mysqli(Config::$DB_HOST,Config::$DB_USER, Config::$DB_PASSWORD);
    if ($this->_dbLink->connect_errno != 0) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cannot establish connection with the database [host=" . Config::$DB_HOST . ",user=" . Config::$DB_USER . "], error: " . mysqli_connect_error());
    }  
    
    # Select the DB
    if (!$this->_dbLink->select_db(Config::$DB_NAME)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cannot select database [database=" . Config::$DB_NAME . "], probably database hasn't been initialized yet, run bin/initDB.php, error: " . $this->_dbLink->error);  
    }
    Utils::log(LOG_DEBUG, "Successfully conected to the database [dbName=" . Config::$DB_NAME . ",dbHost=" . Config::$DB_HOST . ",dbUser=" . Config::$DB_USER . "]",
      __FILE__, __LINE__);
  }
  
  /*
   * Raw query to the SQL, just check if the query finished successfuly and return the result
   */
  protected function rawQuery($sql) {
    Utils::log(LOG_DEBUG, "Sql query: " . preg_replace('/\s+/', ' ', $sql), __FILE__, __LINE__);
    if (!$res = $this->_dbLink->query($sql)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("SQL query failed: " . $this->_dbLink->error);
    }
    
    return $res;
  }
  
  /*
   * Single raw row fetch, just check if there are some results
   */
  protected function rawSingleRowFetch($res) {
    if (($row = $res->fetch_row()) == null) {
      return null;
    }
    return $row;
  }
  
	/*
   * Multi raw row fetch, just check if there are some results
   */
  protected function rawMultiRowFetch($res) {
    $ret = array();
    while (($row = $res->fetch_assoc()) != null) {
      array_push($ret, $row);
    }
    
    if (sizeof($ret) > 0) {
      # Free the resources
      mysqli_free_result($res);
    
      return $ret;
    } else {
      return null;
    }
  }
  
/*
   * Multi raw row fetch for single column, just check if there are some results
   */
  protected function rawSingleValueMultiRowFetch($res) {
    $ret = array();
    while (($row = $res->fetch_row()) != null) {
      array_push($ret, $row[0]);
    }
    
    if (sizeof($ret) > 0) {
      # Free the resources
      mysqli_free_result($res);
    
      return $ret;
    } else {
      return null;
    }
  }
  
	/*
   * Single raw object fetch, just check if the fetch was successfull and return the result
   */
  protected function rawSingleObjectFetch($res, $class, $params) {
    if ($params != null) {
      if (($row = $res->fetch_object($class, $params)) == null) {
        return null;
      }
    } else {
    if (($row = $res->fetch_object($class)) == null) {
        return null;
      }
    }
    return $row;
  }

  /*
   * Multiple raw object fetch, just check if the fetch was successfull and return the result
   */
  protected function rawMultiObjectFetch($res, $class, $params) {
    $ret = array();
    if ($params != null) {
      while ($row = $res->fetch_object($class, $params)) {
	array_push($ret, $row);
      }
    } else {
    while ($row = $res->fetch_object($class)) {
	array_push($ret, $row);
      }
    }
    return $ret;
  }
 
}
?>
