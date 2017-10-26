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
class PkgDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    /**
     * Stores the pkg in the DB
     */
    public function create(Pkg &$pkg)
    {
        $this->db->query("insert into Pkg set
            name='" . $this->db->escape($pkg->getName()) . "',
            version='" . $this->db->escape($pkg->getVersion()) . "',
            archId='" . $this->db->escape($pkg->getArchId()) . "',
            pkgTypeId='" . $this->db->escape($pkg->getPkgTypeId()) . "',
            `release`='" . $this->db->escape($pkg->getRelease()) . "'");

        # Set the newly assigned id
        $pkg->setId($this->db->getLastInsertedId());
    }

    public function getPkgsIds($orderBy = null, $pageSize = -1, $pageNum = -1, $hostId = -1, $search = null)
    {
        $select = "distinct Pkg.id";
        $from = "Pkg";
        $join = null;
        $where = null;
        $order = null;
        $limit = null;
        $offset = null;

        switch ($orderBy) {
            case "version":
                $order[] = "Pkg.version";
                break;
            case "release":
                $order[] = "Pkg.release";
                break;
            case "arch":
                $join[] = "inner join Arch on Pkg.archId = Arch.id";
                $order[] = "Arch.name";
                break;
            case "type":
                $join[] = "inner join PkgType on Pkg.pkgTypeId = PkgType.id";
                $order[] = "PkgType.name";
                break;
            default:
                break;
        }
        $order[] = "Pkg.name";
        $order[] = "Pkg.version";

        if ($hostId != -1) {
            $join[] = "inner join InstalledPkg on InstalledPkg.pkgId = Pkg.id";
            $where[] = "InstalledPkg.hostId = '".$this->db->escape($hostId)."'";
        }

        if ($search != null) {
            $search = trim($search);
            $where[] = "lower(concat(Pkg.name, ' ', Pkg.version, '-', Pkg.release)) like '%".$this->db->escape(strtolower($search), true)."%'";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    /**
     * Get the pkg by its ID
     */
    public function getById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->getBy($id, "id");
    }

    /*
     * Get the pkg by its name
     */
    public function getByName($name)
    {
        return $this->getBy($name, "name");
    }

    public function getIdByNameVersionReleaseArchIdTypeId($name, $version, $release, $archId, $pkgTypeId)
    {
        $sql = "select id from Pkg
            where binary name='" . $this->db->escape($name) . "'
            and version='" . $this->db->escape($version) . "'
            and `release`='" . $this->db->escape($release) . "'
            and archId='" . $this->db->escape($archId) . "'
            and pkgTypeId='" . $this->db->escape($pkgTypeId) . "'";
        $id = $this->db->queryToSingleValue($sql);

        if ($id == null) {
            return -1;
        }
        return $id;
    }

    /**
     * Update the pkg in the DB
     */
    public function update(Pkg &$pkg)
    {
        $this->db->query("update Pkg set
            name='" . $this->db->escape($pkg->getName()) . "',
            version='" . $this->db->escape($pkg->getVersion()) . "',
            `release`='" . $this->db->escape($pkg->getRelease()) . "',
            archId='" . $this->db->escape($pkg->getArchId()) . "',
            pkgTypeId='" . $this->db->escape($pkg->getPkgTypeId()) . "'
            where id=" . $this->db->escape($pkg->getId()));
    }

    /**
     * Delete the pkg from the DB
     */
    public function delete(Pkg &$pkg)
    {
        $this->db->query("delete from Pkg where id=" . $this->db->escape($pkg->getId()));
    }

    public function assignPkgToHost($pkgId, $hostId)
    {
        $sql = "insert into InstalledPkg set
            pkgId='" . $this->db->escape($pkgId) . "',
            hostId='" . $this->db->escape($hostId) . "'";
        $this->db->query($sql);
    }

    public function unassignPkgToHost($pkgId, $hostId)
    {
        $sql = "delete from InstalledPkg where
            pkgId='" . $this->db->escape($pkgId) . "' and
            hostId='" . $this->db->escape($hostId) . "'";
        $this->db->query($sql);
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "Pkg.id=" . $this->db->escape($value);
        } elseif ($type == "name") {
            $where = "binary Pkg.name='" . $this->db->escape($value) . "'";
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->db->queryObject("select Pkg.id as _id, Pkg.name as _name, Pkg.version as _version, Pkg.archId as _archId, Pkg.pkgTypeId as _pkgTypeId, Pkg.`release` as _release, PkgType.name as _pkgTypeName, Arch.name as _archName from Pkg
            inner join PkgType on Pkg.pkgTypeId = PkgType.id
            inner join Arch on Pkg.archId = Arch.id
            where $where", "Pkg");
    }

    public function getPkgsTypesNames()
    {
        $sql = "select distinct(type) from Pkg";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getVulnerableIdsForHost($hostId, $tag)
    {
        $select = "distinct(Pkg.id)";
        $from = "Pkg";
        $order = "Pkg.name ASC";

        # pkgs
        $join[] = "inner join InstalledPkg on (Pkg.id = InstalledPkg.pkgId and InstalledPkg.hostId = '" . $this->db->escape($hostId) . "')";

        # cveDefs
        $join[] = "inner join PkgCveDef on Pkg.id = PkgCveDef.pkgId";
        $join[] = "inner join Cve on PkgCveDef.cveDefId = Cve.cveDefId";

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
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
