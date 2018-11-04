<?php

/**
 * @author Jakub Mlcak
 */
class UserDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(User $user)
    {
        $sql = "insert into User set
            uid='".$this->db->escape($user->getUid())."',
            name='".$this->db->escape($user->getName())."',
            email='".$this->db->escape($user->getEmail())."',
            admin=".$this->db->escape($user->isAdmin());
        $this->db->query($sql);

        # Set the newly assigned id
        $user->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, uid as _uid, name as _name, email as _email, admin as _admin, `timestamp` as _timestamp from User
            where id='".$this->db->escape($id)."'";
        return $this->db->queryObject($sql, "User");
    }

    public function getIdByUid($uid)
    {
        $sql = "select id from User
            where uid='".$this->db->escape($uid)."'";
        $id = $this->db->queryToSingleValue($sql);

        if ($id == null) {
            return -1;
        }
        return $id;
    }

    public function getIds()
    {
        $sql = "select id from User";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function update(User $user)
    {
        $sql = "update User set 
            uid='".$this->db->escape($user->getUid())."',
            name='".$this->db->escape($user->getName())."',
            email='".$this->db->escape($user->getEmail())."',
            admin=".$this->db->escape($user->isAdmin())."
            where id='".$this->db->escape($user->getId())."'";
        $this->db->query($sql);
    }

    public function delete($id)
    {
        $sql = "delete from User
            where id='".$this->db->escape($id)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }

    public function assignHostToUser($userId, $hostId)
    {
        $sql = "insert ignore into UserHost set
            userId='".$this->db->escape($userId)."',
            hostId='".$this->db->escape($hostId)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }

    public function assignHostGroupToUser($userId, $hostGroupId)
    {
        $sql = "insert ignore into UserHostGroup set
            userId='".$this->db->escape($userId)."',
            hostGroupId='".$this->db->escape($hostGroupId)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }

    public function unassignHostToUser($userId, $hostId)
    {
        $sql = "delete from UserHost
            where userId='".$this->db->escape($userId)."'
            and hostId='".$this->db->escape($hostId)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }

    public function unassignHostGroupToUser($userId, $hostGroupId)
    {
        $sql = "delete from UserHostGroup
            where userId='".$this->db->escape($userId)."'
            and hostGroupId='".$this->db->escape($hostGroupId)."'";
        $this->db->query($sql);
        return $this->db->getNumberOfAffectedRows();
    }
}
