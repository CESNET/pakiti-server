<?php
/**
 * @access public
 * @author Michal Prochazka
 */
class Report {
	private $_id = -1;
	private $_receivedOn;
	private $_processedOn;
	private $_throughProxy;
	private $_proxyHostname = null;
	private $_numOfInstalledPkgs = -1;
	private $_numOfVulnerablePkgsSec = 0;
	private $_numOfVulnerablePkgsNorm = 0;
	private $_numOfCves = -1;
	private $_headerHash;
	private $_pkgsHash;
	
	public function Report() {
	}
	
	public function getId() {
	  return $this->_id;
	}
	
  public function getReceivedOn() {
	  return $this->_receivedOn;
	}
	
  public function getProcessedOn() {
	  return $this->_processedOn;
	}
	
  public function getThroughProxy() {
	  return $this->_throughProxy;
	}
	
  public function getNumOfInstalledPkgs() {
	  return $this->_numOfInstalledPkgs;
	}
	
  public function getNumOfVulnerablePkgsSec() {
	  return $this->_numOfVulnerablePkgsSec;
	}
	
  public function getNumOfVulnerablePkgsNorm() {
	  return $this->_numOfVulnerablePkgsNorm;
	}
	
  public function getNumOfCves() {
	  return $this->_numOfCves;
	}

  public function getProxyHostname() {
	  return $this->_proxyHostname;
	}

  public function getHeaderHash() {
	  return $this->_headerHash;
	}

  public function getPkgsHash() {
	  return $this->_pkgsHash;
	}

  public function setId($val) {
	  $this->_id = $val;
	}
	
  public function setReceivedOn($val) {
	  $this->_receivedOn = $val;
	}
	
  public function setProcessedOn($val) {
	  $this->_processedOn = $val;
	}
	
  public function setTroughtProxy($val) {
	  $this->_throughProxy = $val;
	}
	
  public function setNumOfInstalledPkgs($val) {
	  $this->_numOfInstalledPkgs = $val;
	}
	
  public function setNumOfVulnerablePkgsSec($val) {
	  $this->_numOfVulnerablePkgsSec = $val;
	}
	
  public function setNumOfVulnerablePkgsNorm($val) {
	  $this->_numOfVulnerablePkgsNorm = $val;
	}
	
  public function setNumOfCves($val) {
	  $this->_numOfCves = $val;
	}

  public function setProxyHostname($val) {
	  $this->_proxyHostname = $val;
	}

  public function setHeaderHash($val) {
	  return $this->_headerHash = $val;
	}

  public function setPkgsHash($val) {
	  return $this->_pkgsHash = $val;
	}
}
?>
