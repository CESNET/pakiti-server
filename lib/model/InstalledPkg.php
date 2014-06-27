<?php
require_once(realpath(dirname(__FILE__)) . '/Host.php');
require_once(realpath(dirname(__FILE__)) . '/Arch.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/RepositoryPkg.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class InstalledPkg {
  private $_version;
  private $_release;
  private $_host;
  private $_hostId = -1;
  private $_arch;
  private $_archId = -1;
  private $_pkg;
  private $_pkgId = -1;

  public function getVersionRelease() {
    if (!empty($this->_release)) {
      return $this->_version . "-" . $this->_release;
    } else return $this->_version;
  }
  
  public function getVersion() {
    return $this->_version;
  }

  public function getRelease() {
    return $this->_release;
  }

  public function getHost() {
    return $this->_host;
  }

  public function getHostId() {
    return $this->_hostId;
  }

  public function getArch() {
    return $this->_arch;
  }

  public function getArchId() {
    return $this->_archId;
  }

  public function getPkg() {
    return $this->_pkg;
  }

  public function getPkgId() {
    return $this->_pkgId;
  }

  public function setVersion($val) {
    $this->_version = $val;
  }

  public function setRelease($val) {
    $this->_release = $val;
  }

  public function setHost(Host $val) {
    $this->_host = $val;
  }

  public function setHostId($val) {
    $this->_hostId = $val;
  }

  public function setArch(Arch $val) {
    $this->_arch = $val;
  }

  public function setArchId($val) {
    $this->_archId = $val;
  }

  public function setPkg(Pkg $val) {
    $this->_pkg = $val;
  }

  public function setPkgId($val) {
    $this->_pkgId = $val;
  }
}
?>
