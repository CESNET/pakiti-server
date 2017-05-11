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
class PkgsManager extends DefaultManager {
  private $_pakiti;

  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
  }

  public function getPakiti() {
    return $this->_pakiti;
  }

  /**
  * Create if not exist, else set id
  * @return false if already exist
  */
  public function storePkg(Pkg &$pkg){
    Utils::log(LOG_DEBUG, "Storing the pkg", __FILE__, __LINE__);
    if ($pkg == null) {
        Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
        throw new Exception("Pkg object is not valid");
    }

    $new = false;
    $dao = $this->getPakiti()->getDao("Pkg");
    $pkg->setId($dao->getPkgIdByNameVersionReleaseArchType($pkg->getName(), $pkg->getVersion(), $pkg->getRelease(), $pkg->getArch(), $pkg->getType()));
    if ($pkg->getId() == -1) {
      # Pkg is missing, so store it
      $dao->create($pkg);
      $new = true;
    }
    return $new;
  }

  public function getInstalledPkgsAsArray(Host $host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the packages stored in the DB [hostId=" . $host->getId() . "]", __FILE__, __LINE__);

    return $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsAsArray($host);
  }

  /*
   * Returns the array of the pkgs. Array is sorted by the key (pkgName).
   */
  public function getInstalledPkgs(Host &$host, $orderBy = "id", $pageSize = -1, $pageNum = -1) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the packages stored in the DB [hostId=" . $host->getId() . "]", __FILE__, __LINE__);
    
    $pkgs =& $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgs($host, $orderBy, $pageSize, $pageNum);

    return $pkgs;
  }

    /*
     * Returns count of installed pkgs.
     */
  public function getInstalledPkgsCount(Host &$host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the count of installed packages stored in the DB [hostId=" . $host->getId() . "]", __FILE__, __LINE__);
    
    return $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsCount($host->getId());
  }


  /** Return Packages by CveName and Os Group
   * @param $cveName
   * @param OsGroup $osGroup
   * @return mixed
   * @throws Exception
   */
  public function getPkgsByCveNameAndOsGroup($cveName, OsGroup $osGroup)
  {
    if (($osGroup == null) || ($osGroup->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("OsGroup object is not valid or OsGroup.id is not set");
    }

    if ($cveName == "" || $cveName == null) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve name is not valid");
    }

    $sql = "select Pkg.id as _id, Pkg.name as _name, Pkg.version as _version, Pkg.release as _release, Pkg.arch as _arch, Pkg.type as _type from PkgCveDef
        join Cve on PkgCveDef.cveDefId = Cve.cveDefId join Pkg on Pkg.id=PkgCveDef.pkgId where Cve.name='" . $this->getPakiti()->getManager("DbManager")->escape($cveName) . "'
        and osGroupId={$osGroup->getId()} and (Pkg.id not in (select pkgId from CveException where osGroupId={$osGroup->getId()} and cveName=Cve.name))";
    return $this->getPakiti()->getManager("DbManager")->queryObjects($sql, "Pkg");
  }

  /**
   * @return mixed
   * Find packages which are not connected with any host
   */
  public function getUnusedPkgs(){
    Utils::log(LOG_DEBUG, "Getting unused packages from DB", __FILE__, __LINE__);
    $sql = "select Pkg.id as _id, Pkg.name as _name, Pkg.version as _version, Pkg.release as _release, Pkg.arch as _arch, Pkg.type as _type from Pkg where Pkg.id not in (select pkgId from InstalledPkg)";
    return $this->getPakiti()->getManager("DbManager")->queryObjects($sql, "Pkg");
  }

  public function getPkgId($name, $version, $release, $arch, $type)
  {
    if ((!isset($name)) || !isset($version) || !isset($release) || !isset($arch)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Some of the parameters is not set");
    }
    return $this->getPakiti()->getDao("Pkg")->getPkgIdByNameVersionReleaseArchType($name, $version, $release, $arch, $type);
  }


  /*
   * Removes all installed packages associated with the host.
   */
  public function removeHostPackages(Host &$host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }

    Utils::log(LOG_DEBUG, "Removing installed packages [hostname=" . $host->getHostname() . "]", __FILE__, __LINE__);
    
    $this->getPakiti()->getManager("DbManager")->query("delete from InstalledPkg where hostId={$host->getId()}");
  }

  /*
   * Gets the package by the name.
   */
  public function getPkg($pkgName) {
    return $this->getPakiti()->getDao("Pkg")->getByName($pkgName);
  }

  public function getPkgById($pkgId)
  {
    return $this->getPakiti()->getDao("Pkg")->getById($pkgId);
  }
   
  /*
   * Adds the package name into the DB and returns the ID of the newly created record.
   */
  public function addPkg($pkgName, $pkgVersion, $pkgArch, $pkgRelease, $pkgType) {
    $this->getPakiti()->getManager("DbManager")->query(
        "insert into Pkg set
          name='" . $this->getPakiti()->getManager("DbManager")->escape($pkgName) . "',
          version='" . $this->getPakiti()->getManager("DbManager")->escape($pkgVersion) . "',
          arch='" . $this->getPakiti()->getManager("DbManager")->escape($pkgArch) . "',
          type='" . $this->getPakiti()->getManager("DbManager")->escape($pkgType) . "',
          `release`='" . $this->getPakiti()->getManager("DbManager")->escape($pkgRelease)."'");

    return $this->getPakiti()->getManager("DbManager")->getLastInsertedId();
  }

  /*
   * Delete the pkg from the DB
   */
  public function deletePkg(&$pkg){
    if (($pkg == null) || ($pkg->getId() == -1)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Pkg object is not valid or Pkg.id is not set");
    }
    $this->getPakiti()->getDao("Pkg")->delete($pkg);
  }


   /*
   * Adds the package name into the DB and returns the ID of the newly created record.
   */
  public function createPkg(&$pkg) {
      $this->getPakiti()->getDao("Pkg")->create($pkg);
    return $pkg->getId();
  }

    /*
    * Assign Pkgs with Host
    */
  public function assignPkgsWithHost($pkgsIds, $hostId, $installedPkgsIds = array())
  {
    Utils::log(LOG_DEBUG, "Assign Pkgs with Host", __FILE__, __LINE__);

    $installedPkgDao = $this->getPakiti()->getDao("InstalledPkg");
    $pkgsIdsToAdd = array_diff($pkgsIds, $installedPkgsIds);
    $pkgsIdsToRemove = array_diff($installedPkgsIds, $pkgsIds);
    foreach($pkgsIdsToAdd as $pkgId){
      $installedPkgDao->createByHostIdAndPkgId($hostId, $pkgId);
    }
    foreach($pkgsIdsToRemove as $pkgId){
      $installedPkgDao->removeByHostIdAndPkgId($hostId, $pkgId);
    }
  }

  public function getPkgsTypesNames(){
    return $this->getPakiti()->getDao("Pkg")->getPkgsTypesNames();
  }
}
