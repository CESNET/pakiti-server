<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

/**
 * @author Jakub Mlcak
 */
class UserDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(User &$user)
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

    public function update(User &$user)
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
