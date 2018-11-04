<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class OsDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(Os $os)
    {
        $sql = "insert into Os set
            name='" . $this->db->escape($os->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $os->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from Os
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "Os");
    }

    public function getIdByName($name)
    {
        $sql = "select id from Os
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getIds($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        $select = "Os.id";
        $from = "Os";
        $join = null;
        $where = null;
        $order = "Os.name";
        $limit = null;
        $offset = null;

        if ($orderBy != null) {
            $order = "Os.".$this->db->escape($orderBy)."";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function unassignOsGroupsFromOs($osId)
    {
        $sql = "delete from OsOsGroup
            where osId='" . $this->db->escape($osId) . "'";
        $this->db->query($sql);
    }

    public function assignOsToOsGroup($osId, $osGroupId)
    {
        $sql = "insert ignore into OsOsGroup set
            osId='" . $this->db->escape($osId) . "',
            osGroupId='" . $this->db->escape($osGroupId) . "'";
        $this->db->query($sql);
    }
}
