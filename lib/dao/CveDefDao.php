<?php

/**
 * User: Vadym Yanovskyy
 * Date: 7/20/15
 * Time: 2:32 PM
 */
class CveDefDao
{
    private $db;

    public function __construct(DbManager &$dbManager) {
        $this->db = $dbManager;
    }

    /*
     * Stores the CveDef in the DB
     */
    public function create(CveDef &$cveDef) {
        $this->db->query(
            "insert into CveDef set
      	definitionId='".$this->db->escape($cveDef->getDefinitionId())."',
      	title='".$this->db->escape($cveDef->getTitle())."',
      	refUrl='".$this->db->escape($cveDef->getRefUrl())."',
      	vdsSubSourceDefId='".$this->db->escape($cveDef->getVdsSubSourceDefId()). "'");

        # Set the newly assigned id
        $cveDef->setId($this->db->getLastInsertedId());
    }


}