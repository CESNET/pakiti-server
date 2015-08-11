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

    public function getCveDefsByCveName($cveName)
    {
        return $this->db->queryObjects("select id as _id, definitionId as _definitionId,
        vdsSubSourceDefId as _vdsSubSourceDefId, title as _title, refUrl as _refUrl
        from CveDef where id in
        (select cveDefId from Cve where name ='" . $this->db->escape($cveName) . "')", "CveDef");
    }

    public function getCveDefId(CveDef &$cveDef){
        return $this->db->queryToSingleValue("select id from CveDef where
      	definitionId='".$this->db->escape($cveDef->getDefinitionId())."'and
      	title='".$this->db->escape($cveDef->getTitle())."' and
      	refUrl='".$this->db->escape($cveDef->getRefUrl())."' and
      	vdsSubSourceDefId='".$this->db->escape($cveDef->getVdsSubSourceDefId()). "'");
    }


    /**
     * Find CveDef for Vulnerability
     * @param Vulnerability $vul
     * @return CveDef
     */
    public function getCveDefForVulnerability(Vulnerability $vul)
    {
        $sql = "select id as _id, definitionId as _definitionId, title as _title, refUrl as _refUrl, vdsSubSourceDefId as _vdsSubSourceDefId
        from CveDef where CveDef.id=(select Vulnerability.cveDefId from Vulnerability where Vulnerability.id={$vul->getId()})";
        $cveDef = $this->db->queryObject($sql, "CveDef");
        return $cveDef;
    }

}