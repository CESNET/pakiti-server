<?php

/**
 * @author Jakub Mlcak
 */
class CveTagDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveTag $cveTag)
    {
        $sql = "insert into CveTag set
            cveName='" . $this->db->escape($cveTag->getCveName()) . "',
            tagName='" . $this->db->escape($cveTag->getTagName()) . "',
            `reason`='" . $this->db->escape($cveTag->getReason()) . "',
            infoUrl='" . $this->db->escape($cveTag->getInfoUrl()) . "',
            enabled=" . $this->db->escape($cveTag->isEnabled()) . ",
            modifier='" . $this->db->escape($cveTag->getModifier()) . "'
        ";
        $this->db->query($sql);

        # Set the newly assigned id
        $cveTag->setId($this->db->getLastInsertedId());
    }

    public function update(CveTag $cveTag)
    {
        $sql = "update CveTag set
            cveName='" . $this->db->escape($cveTag->getCveName()) . "',
            tagName='" . $this->db->escape($cveTag->getTagName()) . "',
            `reason`='" . $this->db->escape($cveTag->getReason()) . "',
            infoUrl='" . $this->db->escape($cveTag->getInfoUrl()) . "',
            enabled=" . $this->db->escape($cveTag->isEnabled()) . ",
            modifier='" . $this->db->escape($cveTag->getModifier()) . "'
            where id='" . $this->db->escape($cveTag->getId()) . "'
        ";
        $this->db->query($sql);
    }

    public function getIds($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        $select = "CveTag.id";
        $from = "CveTag";
        $join = null;
        $where = null;
        $order = "CveTag.cveName DESC";
        $limit = null;
        $offset = null;

        if ($orderBy != null) {
            $order = "CveTag.".$this->db->escape($orderBy)."";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getById($id)
    {
        $sql = "select id as _id, cveName as _cveName, tagName as _tagName, `reason` as _reason, infoUrl as _infoUrl, `timestamp` as _timestamp, enabled as _enabled, modifier as _modifier from CveTag
            where id=".$this->db->escape($id);
        return $this->db->queryObject($sql, "CveTag");
    }

    public function getIdsByCveName($cveName)
    {
        $sql = "select id from CveTag
            where cveName='".$this->db->escape($cveName)."'
            and enabled='1'";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getIdByCveNameTagName($cveName, $tagName)
    {
        $sql = "select id from CveTag
            where cveName='".$this->db->escape($cveName)."'
            and tagName='".$this->db->escape($tagName)."'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function deleteById($id)
    {
        $sql = "delete from CveTag
            where id=".$this->db->escape($id);
        $this->db->query($sql);
    }

    public function getTagNames()
    {
        $sql = "select distinct(CveTag.tagName) from CveTag";
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
