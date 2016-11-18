<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

class InstalledPkgDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    /*******************
     * Public functions
     *******************/

    /*
     * Stores the installedPkg in the DB
     */
    public function create(InstalledPkg &$installedPkg)
    {
        $this->db->query(
            "insert into InstalledPkg set
            pkgId='" . $this->db->escape($installedPkg->getPkgId()) . "',
            hostId='" . $this->db->escape($installedPkg->getHostId()) . "'");
    }
    
    /*
     * Stores the installedPkg in the DB
     */
    public function createByHostIdAndPkgId($hostId, $pkgId)
    {
        $this->db->query(
            "insert into InstalledPkg set
            pkgId='" . $this->db->escape($pkgId) . "',
            hostId='" . $this->db->escape($hostId) . "'");
    }

    /*
     * Get the installedPkg by its pkgId, hostId
     */
    public function get($hostId, $pkgId)
    {
        return $this->db->queryObject("select
        p.pkgId as _pkgId,
        p.hostId as _hostId
          from
        InstalledPkg p
          where p.pkgId=" . $this->db->escape($pkgId) . " and
            p.hostId=" . $this->db->escape($hostId), "InstalledPkg");
    }

    /*
    * Get the list of installedPkg Ids by hostId
    */
    public function getIdsByHostId($hostId)
    {
        return $this->db->queryToSingleValueMultiRow("select pkgId
            from InstalledPkg
            where hostId=".$this->db->escape($hostId));
    }

    /*
     * Gets all packages for defined host
     */
    public function getInstalledPkgs(Host &$host, $orderBy = "name", $pageSize = -1, $pageNum = -1)
    {
        $sql = "select pkg.id as _id, pkg.name as _name, pkg.version as _version, pkg.release as _release, pkg.arch as _arch, pkg.type as _type
        from InstalledPkg inst inner join Pkg pkg on inst.pkgId=pkg.id";

        $where = " where inst.hostId={$host->getId()}";
        switch ($orderBy) {
            case "arch":
                $sql .= "$where order by pkg.arch";
                break;
            case "version":
                $sql .= "$where order by pkg.version, pkg.release";
                break;
            default:
                // oderByName by default
                $sql .= "$where order by pkg.name";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $offset = $pageSize * $pageNum;
            $sql .= " limit $offset,$pageSize";
        }
        
        return $this->db->queryObjects($sql,"Pkg");
    }

    /*
     * Gets all packages ids for defined hostId
     */
    public function getInstalledPkgsIdsByHostId($hostId)
    {
        $sql = "select pkgId from InstalledPkg where hostId=" . $this->db->escape($hostId);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    /*
      * Gets all installed packages for defined host
      * Returns an associated array. This function is used for the Feeder
      */
    public function getInstalledPkgsAsArray(Host &$host)
    {
        $sql = "select Pkg.name as pkgName, Pkg.`version` as pkgVersion, Pkg.`release` as pkgRelease,
        Pkg.arch as pkgArch from InstalledPkg, Pkg
        where InstalledPkg.pkgId=Pkg.id and InstalledPkg.hostId={$host->getId()} order by Pkg.name";

        $installedPkgsDb =& $this->db->queryToMultiRow($sql);
        $installedPkgs = array();
        foreach ($installedPkgsDb as $installedPkgDb) {
            $installedPkgs[$installedPkgDb["pkgName"]][$installedPkgDb["pkgArch"]] = array('pkgVersion' => $installedPkgDb["pkgVersion"], 'pkgRelease' => $installedPkgDb["pkgRelease"]);
        }
        return $installedPkgs;
    }

    /*
     * Gets count of installed packages for defined host
     */
    public function getInstalledPkgsCount($hostId)
    {
        $sql = "select count(*) from InstalledPkg where hostId={$hostId}";
        return $this->db->queryToSingleValue($sql);
    }

    /*
     * Delete the installedPkg from the DB
     */
    public function delete(InstalledPkg &$installedPkg)
    {
        $this->db->query(
            "delete from InstalledPkg where
              pkgId=" . $this->db->escape($installedPkg->getPkgId()) . " and
              hostId=". $this->db->escape($installedPkg->getHostId()));
    }

    /*
     * Remove the installedPkg from the DB
     */
    public function removeByHostIdAndPkgId($hostId, $pkgId)
    {
        $this->db->query(
            "delete from InstalledPkg where
            pkgId='" . $this->db->escape($pkgId) . "' and
            hostId='" . $this->db->escape($hostId) . "'");
    }
}
?>
