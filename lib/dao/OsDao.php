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
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class OsDao
{
    private $db;
  
    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }
  
    public function create(Os &$os)
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
            osGroupId='" . $this->db->escape($osGroupId) . "'
        ";
        $this->db->query($sql);
    }
}
