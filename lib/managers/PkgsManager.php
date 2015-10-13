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

class PkgsManager extends DefaultManager {
  private $_pakiti;

  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
  }

  public function getPakiti() {
    return $this->_pakiti;
  }
  
  public function getInstalledPkgsAsArray(Host $host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the packages stored in the DB [hostId=".$host->getId()."]", __FILE__, __LINE__);

    return $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsAsArray($host);
  }

  /*
   * Returns the array of the pkgs. Array is sorted by the key (pkgName).
   */
  public function getInstalledPkgs(Host &$host, $orderBy = "id", $pageSize = -1, $pageNum = -1) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the packages stored in the DB [hostId=".$host->getId()."]", __FILE__, __LINE__);
    
    $pkgs =& $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgs($host, $orderBy, $pageSize, $pageNum);

    return $pkgs;
  }

    /*
     * Returns count of installed pkgs.
     */
  public function getInstalledPkgsCount(Host &$host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    Utils::log(LOG_DEBUG, "Getting the count of installed packages stored in the DB [hostId=".$host->getId()."]", __FILE__, __LINE__);
    
    return $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsCount($host);
  }
  
  /*
   * Associates the new packages with the host.
   */
  public function addPkgs(Host &$host, &$pkgs) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    
    Utils::log(LOG_DEBUG, "Adding packages [hostId=".$host->getId().", pkgsCount=".sizeof($pkgs)."]", __FILE__, __LINE__);
   
   	foreach ($pkgs as $pkgName => &$value) {
	# Usage of BINARY when comparing package names is due to case-sensitivness
      $sql = "insert into InstalledPkg 
      		(`pkgId`, `hostId`)
      	select 
      		p.id,". $this->getPakiti()->getManager("DbManager")->escape($host->getId())."
         from
         	Pkg p
         where
         	binary p.name='".$this->getPakiti()->getManager("DbManager")->escape($pkgName)."'";
    
      $this->getPakiti()->getManager("DbManager")->query($sql);
      
      if ($this->getPakiti()->getManager("DbManager")->getNumberOfAffectedRows() == 0) {
        # When affected rows is 0, it probably means, that the package name 
        # doesn't exist in the table Pkg or the package architecture doesn't exist.

        # Check if the package architecture is
        if (($archId = $this->getPakiti()->getDao("Arch")->getIdByName($value['pkgArch'])) == -1) {
          $arch = new Arch();
          $arch->setName($value['pkgArch']);
          $this->getPakiti()->getDao("Arch")->create($arch);
        }
        # We must check if the package is already stored in the table Pkg, if not => store it
        if (($pkgId = $this->getPkgId($pkgName)) == -1) {
          $pkgId = $this->addPkg($pkgName, $value['pkgVersion'],$value["pkgArch"], $value['pkgRelease']);

        }

        # Try the insert the entry once again
        $sql = "insert into InstalledPkg
        			(`pkgId`, `hostId` )
 						values (
        			$pkgId,". $this->getPakiti()->getManager("DbManager")->escape($host->getId()).")";
        $this->getPakiti()->getManager("DbManager")->query($sql);
      }
    }
    unset($value);
  }
  
  /*
   * Updates the packages associated with the host.
   */
  public function updatePkgs(Host &$host, &$pkgs) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    
    Utils::log(LOG_DEBUG, "Updating packages [hostId=".$host->getId().", pkgsCount=".sizeof($pkgs)."]", __FILE__, __LINE__);
    
    foreach ($pkgs as $pkgName => &$value) {
      $sql = "update InstalledPkg inst inner join Pkg pkg
              on inst.pkgId = pkg.Id
              set
      			`version`='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgVersion'])."',
      			`release`='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgRelease'])."',
      			arch = '".$this->getPakiti()->getManager("DbManager")->escape($value['pkgArch'])."'
      		  where
      			hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and 
      			pkg.Id=".$this->getPkgId($pkgName)."";
       
      $this->getPakiti()->getManager("DbManager")->query($sql);
    }
    unset($value);
  }
  
	/*
   * Removes the association of the packages with the host.
   */
  public function removePkgs(Host &$host, &$pkgs) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    
    Utils::log(LOG_DEBUG, "Removing packages [hostId=".$host->getId().", pkgsCount=".sizeof($pkgs)."]", __FILE__, __LINE__);
    
    foreach ($pkgs as $pkgName => &$value) {
      $sql = "delete from InstalledPkg where
      	hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and
      	pkgId=".$this->getPkgId($pkgName)."";
      
      $this->getPakiti()->getManager("DbManager")->query($sql);
    }
    unset($pkgName);
  }

  /**Return Packages by CveName and Os Group
   * @param $cveName
   * @param OsGroup $osGroup
   * @return mixed
   * @throws Exception
   */
  public function getPkgsByCveNameAndOsGroup($cveName, OsGroup $osGroup)
  {
    if (($osGroup == null) || ($osGroup->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("OsGroup object is not valid or OsGroup.id is not set");
    }

    if ($cveName == "" || $cveName == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve name is not valid");
    }

    $sql = "select Pkg.id as _id, Pkg.name as _name, Pkg.version as _version, Pkg.release as _release, Pkg.arch as _arch from PkgCveDef
        join Cve on PkgCveDef.cveDefId = Cve.cveDefId join Pkg on Pkg.id=PkgCveDef.pkgId where Cve.name='" . $this->getPakiti()->getManager("DbManager")->escape($cveName) . "'
        and osGroupId={$osGroup->getId()} and (Pkg.id not in (select pkgId from CveException where osGroupId={$osGroup->getId()} and cveName=Cve.name))";
    return $this->getPakiti()->getManager("DbManager")->queryObjects($sql, "Pkg");
  }


  /*
   * Removes all installed packages associated with the host.
   */
  public function removeHostPackages(Host &$host) {
    if (($host == null) || ($host->getId() == -1)) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
    
    Utils::log(LOG_DEBUG, "Removing installed packages [hostname=".$host->getHostname()."]", __FILE__, __LINE__);
    
    $this->getPakiti()->getManager("DbManager")->query("delete from InstalledPkg where hostId={$host->getId()}");
  }
  
  /*
   * Gets the package ID by the name.
   */
  public function getPkgId($pkgName) {
    $id = $this->getPakiti()->getManager("DbManager")->queryToSingleValue(
      "select id from Pkg where binary name='" . $this->getPakiti()->getManager("DbManager")->escape($pkgName) ."'");
    if ($id == null) {
      return -1;
    }
    return $id;
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
  public function addPkg($pkgName, $pkgVersion, $pkgArch, $pkgRelease) {
    $this->getPakiti()->getManager("DbManager")->query(
        "insert into Pkg set
          name='" . $this->getPakiti()->getManager("DbManager")->escape($pkgName) . "',
          version='" . $this->getPakiti()->getManager("DbManager")->escape($pkgVersion) . "',
          arch='" . $this->getPakiti()->getManager("DbManager")->escape($pkgArch) . "',
          `release`='" . $this->getPakiti()->getManager("DbManager")->escape($pkgRelease)."'");

    return $this->getPakiti()->getManager("DbManager")->getLastInsertedId();
  }

   /*
   * Adds the package name into the DB and returns the ID of the newly created record.
   */
  public function createPkg(&$pkg) {
      $this->getPakiti()->getDao("Pkg")->create($pkg);
    return $pkg->getId();
  }
}
