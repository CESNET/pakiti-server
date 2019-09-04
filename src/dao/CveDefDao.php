<?php

/**
 * @author Vadym Yanovskyy
 * @author Jakub Mlcak
 */
class CveDefDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveDef $cveDef)
    {
        $sql = "insert into CveDef set
            definitionId='".$this->db->escape($cveDef->getDefinitionId())."',
            title='".$this->db->escape($cveDef->getTitle())."',
            refUrl='".$this->db->escape($cveDef->getRefUrl())."',
            vdsSubSourceDefId='".$this->db->escape($cveDef->getVdsSubSourceDefId()). "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $cveDef->setId($this->db->getLastInsertedId());
    }

    public function getIdByDefinitionIdTitleRefUrlVdsSubSourceDefId($definitionId, $title, $refUrl, $vdsSubSourceDefId)
    {
        $sql = "select id from CveDef
            where definitionId='".$this->db->escape($definitionId)."'
            and title='".$this->db->escape($title)."'
            and refUrl='".$this->db->escape($refUrl)."'
            and vdsSubSourceDefId='".$this->db->escape($vdsSubSourceDefId)."'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function assignCveToCveDef($cveId, $cveDefId)
    {
        $sql = "insert ignore into CveCveDef set
            cveId = '".$this->db->escape($cveId)."',
            cveDefId = '".$this->db->escape($cveDefId)."'";
        $this->db->query($sql);
    }

    public function assignPkgToCveDef($pkgId, $cveDefId, $osGroupId)
    {
        $sql = "insert ignore into PkgCveDef set
            pkgId = '".$this->db->escape($pkgId)."',
            cveDefId = '".$this->db->escape($cveDefId)."',
            osGroupId = '".$this->db->escape($osGroupId)."'";
        $this->db->query($sql);
    }

    public function removePkg($pkgId)
    {
        $sql = "delete from PkgCveDef where "
            . "pkgId = '" . $this->db->escape($pkgId) . "'";
        $this->db->query($sql);
    }
}
