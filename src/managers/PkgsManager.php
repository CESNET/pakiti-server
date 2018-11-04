<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class PkgsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storePkg(Pkg $pkg)
    {
        Utils::log(LOG_DEBUG, "Storing the pkg", __FILE__, __LINE__);
        if ($pkg == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Pkg object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Pkg");
        $pkg->setId($dao->getIdByNameVersionReleaseArchIdTypeId($pkg->getName(), $pkg->getVersion(), $pkg->getRelease(), $pkg->getArchId(), $pkg->getPkgTypeId()));
        if ($pkg->getId() == -1) {
            # Pkg is missing, so store it
            $dao->create($pkg);
            $new = true;
        }
        return $new;
    }

    /**
     * Get pkgs
     */
    public function getPkgs($orderBy = null, $pageSize = -1, $pageNum = -1, $hostId = -1, $search = null)
    {
        Utils::log(LOG_DEBUG, "Getting all pkgs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Pkg");
        $pkgsIds = $dao->getPkgsIds($orderBy, $pageSize, $pageNum, $hostId, $search);

        $pkgs = array();
        foreach ($pkgsIds as $id) {
            array_push($pkgs, $dao->getById($id));
        }
        return $pkgs;
    }

    /**
     * Get pkgs count
     */
    public function getPkgsCount($hostId = -1, $search = null)
    {
        Utils::log(LOG_DEBUG, "Getting pkgs count", __FILE__, __LINE__);
        return sizeof($this->getPakiti()->getDao("Pkg")->getPkgsIds(null, -1, -1, $hostId, $search));
    }

    /**
     * Getting pkgs by Cve name and osGroup ID
     */
    public function getPkgsByCveNameAndOsGroupId($cveName, $osGroupId)
    {
        Utils::log(LOG_DEBUG, "Getting pkgs by Cve name[".$cveName."] and osGroup ID[".$osGroupId."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Pkg");
        $ids = $dao->getByCveNameAndOsGroupId($cveName, $osGroupId);

        $pkgs = array();
        foreach ($ids as $id) {
            array_push($pkgs, $dao->getById($id));
        }
        return $pkgs;
    }

    /**
     * Find packages which are not connected with any host
     */
    public function getUnusedPkgsIds()
    {
        Utils::log(LOG_DEBUG, "Getting unused pkgs IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Pkg");
        return $dao->getUnusedIds();
    }

    public function getPkgId($name, $version, $release, $archId, $typeId)
    {
        return $this->getPakiti()->getDao("Pkg")->getIdByNameVersionReleaseArchIdTypeId($name, $version, $release, $archId, $typeId);
    }

    public function getPkgById($id)
    {
        return $this->getPakiti()->getDao("Pkg")->getById($id);
    }

    /**
     * Delete the pkg from the DB
     */
    public function deletePkg($id)
    {
        $this->getPakiti()->getDao("Pkg")->delete($id);
    }

    /**
     * Assign Pkgs with Host
     */
    public function assignPkgsWithHost($pkgsIds, $hostId, $installedPkgsIds = array())
    {
        Utils::log(LOG_DEBUG, "Assign Pkgs with Host", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Pkg");

        $pkgsIdsToAdd = array_diff($pkgsIds, $installedPkgsIds);
        $pkgsIdsToRemove = array_diff($installedPkgsIds, $pkgsIds);
        foreach ($pkgsIdsToAdd as $pkgId) {
            $dao->assignPkgToHost($pkgId, $hostId);
        }
        foreach ($pkgsIdsToRemove as $pkgId) {
            $dao->unassignPkgToHost($pkgId, $hostId);
        }
    }

    /**
     * Getting vulnerable packages for host
     * @param $hostId
     * @param $tag = null -> all vulnerable pkgs | true -> only pkgs with tagged CVE | string -> tag name
     * @return array of packages
     */
    public function getVulnerablePkgsForHost($hostId, $tag = null)
    {
        Utils::log(LOG_DEBUG, "Getting vulnerable pkgs for host[$hostId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Pkg");
        $ids = $dao->getVulnerableIdsForHost($hostId, $tag);

        $pkgs = array();
        foreach ($ids as $id) {
            array_push($pkgs, $dao->getById($id));
        }
        return $pkgs;
    }
}
