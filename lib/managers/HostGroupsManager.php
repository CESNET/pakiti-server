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

class HostGroupsManager extends DefaultManager {
  private $_pakiti;

  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
  }

  public function getPakiti() {
    return $this->_pakiti;
  }
  
  public function getHostGroupById($id) {
    Utils::log(LOG_DEBUG, "Getting host group by id [hostGroupId=$id]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getById($id);  
  }
  
  public function getHostGroupByName($name) {
    Utils::log(LOG_DEBUG, "Getting host group by name [hostGroupName=$name]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getByName($name);  
  }
  
  public function getHostGroupIdByName($name) {
    Utils::log(LOG_DEBUG, "Getting host group ID by name [hostGroupName=$name]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getIdByName($name);  
  }

  public function getHostGroupsByHost(Host &$host) {
    Utils::log(LOG_DEBUG, "Getting host groups by name [host={$host->getHostname()}]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getByHostId($host->getId());  
  }

  public function getHostGroups($orderBy, $pageNum = -1, $pageSize = -1) {
    Utils::log(LOG_DEBUG, "Getting all host groups", __FILE__, __LINE__);
    $hostGroupsIds = $this->getPakiti()->getDao("HostGroup")->getHostGroupsIds($orderBy, $pageNum, $pageSize); 

    $hostGroups = array();
    if ($hostGroupsIds != null) {
      foreach ($hostGroupsIds as $hostGroupId) {
	array_push($hostGroups, $this->getHostGroupById($hostGroupId));
      }
    }

    return $hostGroups;
  }

  public function getHostGroupsCount() {
    Utils::log(LOG_DEBUG, "Getting the count of all host groups", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getHostGroupsCount(); 
  }

  public function getHostsCount(HostGroup &$hostGroup) {
    Utils::log(LOG_DEBUG, "Getting the count of all hosts inside the host groups", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("HostGroup")->getHostsCount($hostGroup->getId());
  }

  public function getHosts(HostGroup &$hostGroup, $orderBy, $pageSize = -1, $pageNum = -1) {
    Utils::log(LOG_DEBUG, "Getting the hosts from the host group [hostGroup={$hostGroup->getName()}", __FILE__, __LINE__);

    $hostsIds = $this->getPakiti()->getDao("HostGroup")->getHostsIds($hostGroup->getId(), $orderBy, $pageSize, $pageNum); 

    $hosts = array();
    foreach ($hostsIds as $hostId) {
      array_push($hosts, $this->getPakiti()->getManager("HostsManager")->getHostById($hostId));
    }

    return $hosts;
  }
			/*
   * Create association between host and hostGroup
   */
  public function assignHostToHostGroup(Host &$host, HostGroup &$hostGroup) {
    if (($host == null) || ($host->getId() == -1) || ($hostGroup == null)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host or HostGroup object is not valid or Host.id|HostGroup.id is not set");
    }
    Utils::log(LOG_DEBUG, "Assigning the host to the host group [host=" . $host->getHostname() . ",hostGroupName=" . $hostGroup->getName() . "]", __FILE__, __LINE__);
    
    # Check if the hostGroup name is valid
    if ($hostGroup->getName() == "") {
      $hostGroup->setName(Constants::$NA);
    }
    $hostGroupId = $this->getHostGroupIdByName($hostGroup->getName());
    if ($hostGroupId == -1) {
      # HostGroup doesn't exist, so create it
      $this->getPakiti()->getDao("HostGroup")->create($hostGroup);
    } else {
      $hostGroup->setId($hostGroupId);
    }

    Utils::log(LOG_DEBUG, "Assinging the host to the hostGroup [hostId=" . $host->getId() . ",hostGroupId=" . $hostGroup->getId() . "]", __FILE__, __LINE__);
    # Check if the tag already exists
    $isAssigned = 
      $this->getPakiti()->getManager("DbManager")->queryToSingleValue(
    		"select 1 from HostHostGroup where 
    	 		hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and 
    	 		hostGroupId=".$this->getPakiti()->getManager("DbManager")->escape($hostGroup->getId()));
    
    if ($isAssigned == null) {
      # Association between host and hostTag doesn't exist, so create it
      $this->getPakiti()->getManager("DbManager")->query(
    		"insert into HostHostGroup set 
          hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId()).",
    	 		hostGroupId=".$this->getPakiti()->getManager("DbManager")->escape($hostGroup->getId()));
    }
  }
  
  /*
   * Removes the host from the host group.
   */
  public function removeHostFromHostGroups(Host &$host) {
     if (($host == null) || ($host->getId() == -1)) {
       Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
       throw new Exception("Host object is not valid or Host.id is not set");
     }
    Utils::log(LOG_DEBUG, "Removing the host from all host groups [host=" . $host->getHostname() . "]", __FILE__, __LINE__);
    
    $this->getPakiti()->getDao("HostGroup")->removeHostFromHostGroups($host->getId());
  }
}