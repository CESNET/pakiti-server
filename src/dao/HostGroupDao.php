<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class HostGroupDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(HostGroup $hostGroup)
    {
        $sql = "insert into HostGroup set
            name='".$this->db->escape($hostGroup->getName())."',
            url='".$this->db->escape($hostGroup->getUrl())."',
            contact='".$this->db->escape($hostGroup->getContact())."',
            note='".$this->db->escape($hostGroup->getNote())."'";
        $this->db->query($sql);

        # Set the newly assigned id
        $hostGroup->setId($this->db->getLastInsertedId());
    }

    public function update(HostGroup $hostGroup)
    {
        $sql = "update HostGroup set
            name='".$this->db->escape($hostGroup->getName())."',
            url='".$this->db->escape($hostGroup->getUrl())."',
            contact='".$this->db->escape($hostGroup->getContact())."',
            note='".$this->db->escape($hostGroup->getNote())."'
            where id='".$this->db->escape($hostGroup->getId())."'";
        $this->db->query($sql);
    }

    public function getById($id, $userId = -1)
    {
        $select = "id as _id, name as _name, url as _url, contact as _contact, note as _note";
        $from = "HostGroup";
        $join = null;
        $where[] = "id='".$this->db->escape($id)."'";

        if ($userId != -1) {
            $join[] = "inner join UserHostGroup on HostGroup.id = UserHostGroup.hostGroupId";
            $where[] = "UserHostGroup.userId = '".$this->db->escape($id)."'";
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where);
        return $this->db->queryObject($sql, "HostGroup");
    }

    public function getIdsByHostId($hostId)
    {
        $sql = "select HostGroup.id from HostHostGroup
            inner join HostGroup on HostHostGroup.hostGroupId = HostGroup.id
            where HostHostGroup.hostId = '".$this->db->escape($hostId)."'";
        return $this->db->queryToSingleValueMultiRow($sql, "HostGroup");
    }

    public function getIdByName($name)
    {
        $sql = "select id from HostGroup 
            where name='".$this->db->escape($name)."'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getHostGroupsIds($orderBy = null, $pageSize = -1, $pageNum = -1, $userId = -1)
    {
        $select = "HostGroup.id";
        $from = "HostGroup";
        $join = null;
        $where = null;
        $order = "HostGroup.name";
        $limit = null;
        $offset = null;

        if ($orderBy != null) {
            $order = "HostGroup.".$this->db->escape($orderBy)."";
        }

        if ($userId != -1) {
            $join[] = "inner join UserHostGroup on HostGroup.id = UserHostGroup.hostGroupId";
            $where[] = "UserHostGroup.userId = '".$this->db->escape($userId)."'";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function delete($id)
    {
        $sql = "delete from HostGroup
            where id = '".$this->db->escape($id)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }

    public function removeHostFromHostGroups($hostId)
    {
        $sql = "delete from HostHostGroup where hostId = '".$this->db->escape($hostId)."'";
        $this->db->query($sql);
    }
}
