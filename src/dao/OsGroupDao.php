<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class OsGroupDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(OsGroup $osGroup)
    {
        $sql = "insert into OsGroup set
            name='" . $this->db->escape($osGroup->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $osGroup->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from OsGroup
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "OsGroup");
    }

    public function getIdByName($name)
    {
        $sql = "select id from OsGroup
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getIds($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        $select = "OsGroup.id";
        $from = "OsGroup";
        $join = null;
        $where = null;
        $order = "OsGroup.name";
        $limit = null;
        $offset = null;

        if ($orderBy != null) {
            $order = "OsGroup.".$this->db->escape($orderBy)."";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getIdsByOsId($osId)
    {
        $sql = "select OsGroup.id from OsGroup
            left join OsOsGroup on OsGroup.id = OsOsGroup.osGroupId
            where OsOsGroup.osId='" . $this->db->escape($osId) . "'";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function unassignOsesFromOsGroup($osGroupId)
    {
        $sql = "delete from OsOsGroup
            where osGroupId='" . $this->db->escape($osGroupId) . "'";
        $this->db->query($sql);
    }
}
