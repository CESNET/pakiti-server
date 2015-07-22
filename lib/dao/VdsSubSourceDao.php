<?php

class VdsSubSourceDao
{
    private $db;

    public function __construct(DbManager &$dbManager) {
        $this->db = $dbManager;
    }

    /*******************
     * Public functions
     *******************/

    /*
     * Stores the vdsSubSource in the DB
     */
    public function create(VdsSubSourceDao &$vdsSubSource) {
        $this->db->query(
            "insert into VdsSubSource set
      	name='".$this->db->escape($vdsSubSource->getName())."',
      	type='".$this->db->escape($vdsSubSource->getType())."',
      	vdsSourceId='".$this->db->escape($vdsSubSource->getVdsSourceId())."'
      	");

        # Set the newly assigned id
        $vdsSubSource->setId($this->db->getLastInsertedId());
    }

    public function getIdByName($name){
        $id = $this->db->queryToSingleValue(
            "select
    		id
      from
      	VdsSubSource
      where
      	name='".$this->db->escape($name)."'");

        if ($id == null) {
            return -1;
        }
        return $id;
    }


//  /*********************
//   * Protected functins
//   *********************/

//  /*
//   * We can get the data by ID or name
//   */
  protected function getBy($value, $type) {
    $where = "";
    if ($type == "id") {
      $where = "id=".$this->db->escape($value);
    } else if ($type == "name") {
      $where = "name='".$this->db->escape($value)."'";
    } else {
      throw new Exception("Undefined type of the getBy");
    }

      $params = array();
      array_push($params, $pakiti);

    $type = $this->db->queryToSingleValue(
      "select `type` from VdsSubSource where $where");

    return $this->db->queryObject(
    	"select
    		id as _id, name as _name
      from
      	VdsSubSource
      where
      	$where", $type, $params);
  }





}