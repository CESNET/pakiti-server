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
class PkgTypeDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(PkgType $pkgType)
    {
        $sql = "insert into PkgType set
            name='" . $this->db->escape($pkgType->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $pkgType->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from PkgType
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "PkgType");
    }

    public function getIdByName($name)
    {
        $sql = "select id from PkgType
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getIds()
    {
        $sql = "select id from PkgType";
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
