<?php

/*
 * User: Vadym Yanovskyy
 * Date: 7/20/15
 * Time: 3:22 PM
 */
class CveDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    /*
     * Stores the Cve in the DB
     */
    public function create(Cve &$cve)
    {
        $this->db->query(
            "insert into Cve set
      	name='" . $this->db->escape($cve->getName()) . "',
      	cveDefId='" . $this->db->escape($cve->getCveDefId()) . "'");

        # Set the newly assigned id
        $cve->setId($this->db->getLastInsertedId());
    }

    public function getCve($name, $cveDefId){
        return $this->db->queryObject(
            "select
    		id as _id, name as _name, cveDefId as _cveDefId
      from
      	Cve
      where
      	name='" . $this->db->escape($name) . "' AND
      	cveDefId ='" . $this->db->escape($cveDefId) . "'", "Cve");
    }

    public function getCvesByName($name){
        return $this->db->queryObjects(
            "select
    		id as _id, name as _name, cveDefId as _cveDefId
            from
      	      Cve
            where
              name='" . $this->db->escape($name) . "'", "Cve");
    }

    public function getCvesByCveDef(CveDef &$cveDef)
    {
        return $this->db->queryObjects("select id
        as _id, name as _name, cveDefId
        as _cveDefId from Cve where
        Cve.cveDefId={$cveDef->getId()}", "Cve");
    }

    public function getCveNames()
    {
        $sql = "select DISTINCT name from Cve limit 50"; //TODO remove limit
        $cveNamesDb =& $this->db->queryToMultiRow($sql);
        $cveNames = array();
        if ($cveNamesDb != null) {
            foreach ($cveNamesDb as $cveNameDb) {
                array_push($cveNames, $cveNameDb["name"]);
            }
        }
        return $cveNames;
    }

    public function getAllCves()
    {
        return $this->db->queryObjects(
            "select
    		id as _id, name as _name, cveDefId as _cveDefId
            from
      	      Cve", "Cve");
    }



}

