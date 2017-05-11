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
 */
class Host {
  private $_id = -1;
  private $_hostname;
  private $_ip;
  private $_reporterHostname;
  private $_reporterIp;
  private $_kernel;
  private $_type;
  private $_ownRepositoriesDef = 0;
  private $_os;
  private $_osName;
  private $_osId = -1;
  private $_arch;
  private $_archName;
  private $_archId = -1;
  private $_domain;
  private $_domainName;
  private $_domainId = -1;
  private $_lastReportId = -1;
  private $_hostGroup;
  private $_hostGroupName;
  private $_hostGroupId;

  public function Host() {
  }
	
  public function getId() {
    return $this->_id;
  }
	
  public function getHostname() {
	  return $this->_hostname;
	}
	
  public function getIp() {
	  return $this->_ip;
	}
	
  public function getReporterHostname() {
	  return $this->_reporterHostname;
	}
	
  public function getReporterIp() {
	  return $this->_reporterIp;
	}
	
  public function getKernel() {
	  return $this->_kernel;
	}
	
  public function getOs() {
	  return $this->_os;
	}
	
  public function getOsName() {
	  return $this->_osName;
	}

  public function getOsId() {
	  return $this->_osId;
  }
  
  public function getArch() {
	  return $this->_arch;
	}

  public function getArchName() {
	  return $this->_archName;
	}  

  public function getArchId() {
	  return $this->_archId;
	}
	
  public function getDomain() {
	  return $this->_domain;
	}
	
  public function getDomainName() {
	  return $this->_domainName;
	}

  public function getDomainId() {
	  return $this->_domainId;
	}
	
  public function getLastReportId() {
	  return $this->_lastReportId;
	}
	
  public function getType() {
	  return $this->_type;
	}
	
  public function getOwnRepositoriesDef() {
	  return $this->_ownRepositoriesDef;
	}

  public function getHostGroup() {
	  return $this->_hostGroup;
	}

  public function getHostGroupName() {
	  return $this->_hostGroupName;
	}

  public function getHostGroupId() {
	  return $this->_hostGroupId;
	}
	
  public function setId($val) {
	  $this->_id = $val;
	}
	
  public function setHostname($val) {
	  $this->_hostname = $val;
	}
	
  public function setIp($val) {
	  $this->_ip = $val;
	}
	
  public function setReporterHostname($val) {
	  $this->_reporterHostname = $val;
	}
	
  public function setReporterIp($val) {
	  $this->_reporterIp = $val;
	}
	
  public function setKernel($val) {
	  $this->_kernel = $val;
	}
	
  public function setOs(Os $val) {
	  $this->_os = $val;
	}

  public function setOsName($val) {
	  $this->_osName = $val;
	}	

  public function setOsId($val) {
	  $this->_osId = $val;
  }
  
  public function setArch(Arch $val) {
	  $this->_arch = $val;
	}

  public function setArchName($val) {
	  $this->_archName = $val;
	}
  
  public function setArchId($val) {
	  $this->_archId = $val;
	}
	
  public function setDomain(Domain $val) {
	  $this->_domain = $val;
	}
	
  public function setDomainName($val) {
	  $this->_domainName = $val;
	}

  public function setDomainId($val) {
	  return $this->_domainId = $val;
	}
	
  public function setLastReportId($val) {
	  return $this->_lastReportId = $val;
	}
	
  public function setType($val) {
	  return $this->_type = $val;
	}
	
  public function setOwnRepositoriesDef($val) {
	  return $this->_ownRepositoriesDef = $val;
	}

  public function setHostGroup($val) {
	  return $this->_hostGroup = $val;
	}

  public function setHostGroupName($val) {
	  return $this->_hostGroupName = $val;
	}

  public function setHostGroupId($val) {
	  return $this->_hostGroupId = $val;
	}
}
?>
