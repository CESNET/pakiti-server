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
    
    // Fill arch, host and pkg object
    
    foreach ($pkgs as &$pkg) {
      $pkg->setHost($host);
      $pkg->setArch($this->getPakiti()->getDao("Arch")->getById($pkg->getArchId()));
      $pkg->setPkg($this->getPakiti()->getDao("Pkg")->getById($pkg->getPkgId()));
    }

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
      		(`pkgId`, `hostId`, `version`, `release`, `archId`) 
      	select 
      		p.id,". 
          $this->getPakiti()->getManager("DbManager")->escape($host->getId()).",'".
          $this->getPakiti()->getManager("DbManager")->escape($value['pkgVersion'])."','".
          $this->getPakiti()->getManager("DbManager")->escape($value['pkgRelease'])."',".
          "a.id
         from
         	Pkg p, Arch a
         where
         	a.name='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgArch'])."'
         	and binary p.name='".$this->getPakiti()->getManager("DbManager")->escape($pkgName)."'";
    
      $this->getPakiti()->getManager("DbManager")->query($sql);
      
      if ($this->getPakiti()->getManager("DbManager")->getNumberOfAffectedRows() == 0) {
        # When affected rows is 0, it probably means, that the package name 
        # doesn't exist in the table Pkg or the package architecture doesn't exist. 
      
        # We must check if the package is already stored in the table Pkg, if not => store it
        if (($pkgId = $this->getPkgId($pkgName)) == -1) {
          $pkgId = $this->addPkg($pkgName);
        }
        # Check if the package architecture is
        if (($archId = $this->getPakiti()->getDao("Arch")->getIdByName($value['pkgArch'])) == -1) {
          $arch = new Arch();
          $arch->setName($value['pkgArch']);
          $this->getPakiti()->getDao("Arch")->create($arch);
          $archId = $arch->getId();
        }
        # Try the insert the entry once again
        $sql = "insert into InstalledPkg 
        			(`pkgId`, `hostId`, `version`, `release`, `archId`) 
 						values (
        			$pkgId,". 
              $this->getPakiti()->getManager("DbManager")->escape($host->getId()).",'".
              $this->getPakiti()->getManager("DbManager")->escape($value['pkgVersion'])."','".
              $this->getPakiti()->getManager("DbManager")->escape($value['pkgRelease'])."',
              $archId)";
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
      $sql = "update InstalledPkg set
      			`version`='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgVersion'])."',
      			`release`='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgRelease'])."',
      			archId=(select id from Arch where name='".$this->getPakiti()->getManager("DbManager")->escape($value['pkgArch'])."')
      		where 
      			hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and 
      			pkgId=(select id from Pkg where binary name='".$this->getPakiti()->getManager("DbManager")->escape($pkgName)."')";
       
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
    
    foreach ($pkgs as &$pkgName) {
      $sql = "delete from InstalledPkg where
      	hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and 
      	pkgId=(select id from Pkg where binary name='".$this->getPakiti()->getManager("DbManager")->escape($pkgName)."')";
      
      $this->getPakiti()->getManager("DbManager")->query($sql);
    }
    unset($pkgName);
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
   
  /*
   * Adds the package name into the DB and returns the ID of the newly created record.
   */
  public function addPkg($pkgName) {
    $this->getPakiti()->getManager("DbManager")->query(
    	"insert into Pkg set name='" . $this->getPakiti()->getManager("DbManager")->escape($pkgName) ."'");
    
    return $this->getPakiti()->getManager("DbManager")->getLastInsertedId();
  }
   /*
   * Adds the package name into the DB and returns the ID of the newly created record.
   */
  public function createPkg(&$pkg) {
    $this->getPakiti()->getManager("DbManager")->query(
    	"insert into Pkg set name='" . $this->getPakiti()->getManager("DbManager")->escape($pkg->getName()) ."'");
    
    $pkg->setId($this->getPakiti()->getManager("DbManager")->getLastInsertedId());
    return $pkg;
  }
}
