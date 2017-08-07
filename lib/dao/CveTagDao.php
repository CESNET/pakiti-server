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
class CveTagDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveTag &$cveTag)
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

    public function update(CveTag &$cveTag)
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
        $sql = "select id as _id, cveName as _cveName, tagName as _tagName, `reason` as _reason, infoUrl as _infoUrl, timestamp as _timestamp, enabled as _enabled, modifier as _modifier from CveTag
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
