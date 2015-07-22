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
        return $this->db->queryToMultiRow("select
	pkgId
      from
	InstalledPkg
      where hostId=".$this->db->escape($hostId));
    }

    /*
     * Gets all packages for defined host
     */
    public function getInstalledPkgs(Host &$host, $orderBy, $pageSize, $pageNum)
    {
        $sql = "select pkg.id, pkg.name, pkg.version, pkg.release, pkg.arch
        from InstalledPkg inst inner join Pkg pkg on inst.pkgId=pkg.id";

        $where = " where inst.hostId={$host->getId()}";
        switch ($orderBy) {
            case "arch":
                $sql .= "$where order by pkg.arch";
                break;
            case "version":
                $sql .= "$where order by inst.version, inst.release";
                break;
            default:
                // oderByName by default
                $sql .= "$where order by pkg.name";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $offset = $pageSize * $pageNum;
            $sql .= " limit $offset,$pageSize";
        }

        $installedPkgsDb =& $this->db->queryToMultiRow($sql);

        # Create objects
        $installedPkgs = array();
        if ($installedPkgsDb != null) {
            foreach ($installedPkgsDb as $installedPkgDb) {
                $pkg = new Pkg();
                $pkg->setId($installedPkgDb["id"]);
                $pkg->setName($installedPkgDb["name"]);
                $pkg->setVersion($installedPkgDb["version"]);
                $pkg->setRelease($installedPkgDb["release"]);
                $pkg->setArch($installedPkgDb["arch"]);

                array_push($installedPkgs, $pkg);
            }
        }

        return $installedPkgs;
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
        if ($installedPkgsDb != null) {
            foreach ($installedPkgsDb as $installedPkgDb) {
                $pkgTmp = array();
                $pkgTmp["pkgVersion"] = $installedPkgDb["pkgVersion"];
                $pkgTmp["pkgRelease"] = $installedPkgDb["pkgRelease"];
                $pkgTmp["pkgArch"] = $installedPkgDb["pkgArch"];

                $installedPkgs[$installedPkgDb["pkgName"]] = $pkgTmp;
            }
        }
        return $installedPkgs;
    }

    /*
     * Gets count of installed packages for defined host
     */
    public function getInstalledPkgsCount(Host &$host)
    {
        $sql = "select count(*) from InstalledPkg where hostId={$host->getId()}";
        return $this->db->queryToSingleValue($sql);
    }

    /*
     * Update the installedPkg in the DB
     */
    public function update(InstalledPkg &$installedPkg)
    {
        $this->db->query(
            "update InstalledPkg set
            version='" . $this->db->escape($installedPkg->getVersion()) . "
            release='" . $this->db->escape($installedPkg->getRelease()) . "
          where pkgId=" . $this->db->escape($installedPkg->getPkgId()) . " and
        hostId=" . $this->db->escape($installedPkg->getHostId()) . " and
        archId=" . $this->db->escape($installedPkg->getArchId()));
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
}
?>
