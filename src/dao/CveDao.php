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
 * @author Vadym Yanovskyy
 * @author Jakub Mlcak
 */
class CveDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(Cve $cve)
    {
        $sql = "insert into Cve set
            name='" . $this->db->escape($cve->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $cve->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from Cve
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "Cve");
    }

    public function getIdByName($name)
    {
        $sql = "select id from Cve
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getNamesForPkgAndOs($pkgId, $osId, $tag = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $order = "Cve.name DESC";

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        # pkg
        $join[] = "inner join PkgCveDef on (CveCveDef.cveDefId = PkgCveDef.cveDefId and PkgCveDef.pkgId = '" . $this->db->escape($pkgId) . "')";

        # os
        $join[] = "inner join OsOsGroup on (PkgCveDef.osGroupId = OsOsGroup.osGroupId and OsOsGroup.osId = '" . $this->db->escape($osId) . "')";

        # exceptions
        $join[] = "left join CveException on (Cve.name = CveException.cveName and PkgCveDef.pkgId = CveException.pkgId and PkgCveDef.osGroupId = CveException.osGroupId)";
        $where[] = "CveException.id IS NULL";

        # tag
        if ($tag !== null) {
            if ($tag === true) {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
            } elseif ($tag === false){
                $join[] = "left join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
                $where[] = "CveTag.id IS NULL ";
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getNamesForHost($hostId, $tag = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $order = "Cve.name DESC";

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        # pkgs
        $join[] = "inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId";
        $join[] = "inner join InstalledPkg on (PkgCveDef.pkgId = InstalledPkg.pkgId and InstalledPkg.hostId = '" . $this->db->escape($hostId) . "')";

        # os
        $join[] = "inner join OsOsGroup on PkgCveDef.osGroupId = OsOsGroup.osGroupId";
        $join[] = "inner join Host on (OsOsGroup.osId = Host.osId and Host.id = '" . $this->db->escape($hostId) . "')";

        # exceptions
        $join[] = "left join CveException on (Cve.name = CveException.cveName and PkgCveDef.pkgId = CveException.pkgId and PkgCveDef.osGroupId = CveException.osGroupId)";
        $where[] = "CveException.id IS NULL";

        # tag
        if ($tag !== null) {
            if ($tag === true) {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
            } elseif ($tag === false){
                $join[] = "left join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
                $where[] = "CveTag.id IS NULL";
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getNames($used = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $join = null;
        $where = null;
        $order = "Cve.name DESC";

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        if ($used !== null) {
            if ($used === true) {
                $join[] = "inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
