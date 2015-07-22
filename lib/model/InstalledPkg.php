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
  private $_hostId = -1;
  private $_pkgId = -1;

  public function InstalledPkg() {
  }

  public function getHostId() {
    return $this->_hostId;
  }

  public function getPkgId() {
    return $this->_pkgId;
  }

  public function setHostId($val) {
    $this->_hostId = $val;
  }

  public function setPkgId($val) {
    $this->_pkgId = $val;
  }
}
?>
