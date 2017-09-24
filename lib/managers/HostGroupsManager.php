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
class HostGroupsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeHostGroup(HostGroup &$hostGroup)
    {
        Utils::log(LOG_DEBUG, "Storing the hostGroup", __FILE__, __LINE__);
        if ($hostGroup == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("HostGroup object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("HostGroup");
        $hostGroup->setId($dao->getIdByName($hostGroup->getName()));
        if ($hostGroup->getId() == -1) {
            # HostGroup is missing, so store it
          $dao->create($hostGroup);
            $new = true;
        } else {
            $dao->update($hostGroup);
        }
        return $new;
    }

    public function getHostGroupById($id, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting host group by id [hostGroupId=$id]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("HostGroup")->getById($id, $userId);
    }

    public function getHostGroupByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting host group by name [hostGroupName=$name]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("HostGroup");
        return $dao->getById($dao->getIdByName($name));
    }

    public function getHostGroupIdByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting host group ID by name [hostGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("HostGroup")->getIdByName($name);
    }

    public function getHostGroupsByHost(Host &$host)
    {
        Utils::log(LOG_DEBUG, "Getting host groups by name [host={$host->getHostname()}]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("HostGroup");
        $ids = $dao->getIdsByHostId($host->getId());

        $hostGroups = array();
        foreach ($ids as $id) {
            array_push($hostGroups, $dao->getById($id));
        }

        return $hostGroups;
    }

    public function getHostGroups($orderBy = null, $pageNum = -1, $pageSize = -1, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting all host groups", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("HostGroup");
        $ids = $dao->getHostGroupsIds($orderBy, $pageNum, $pageSize, $userId);

        $hostGroups = array();
        foreach ($ids as $id) {
            array_push($hostGroups, $dao->getById($id));
        }

        return $hostGroups;
    }

    /**
     * Get host groups IDs
     */
    public function getHostGroupsIds($orderBy = null, $pageNum = -1, $pageSize = -1, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting host groups IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("HostGroup");
        return $dao->getHostGroupsIds($orderBy, $pageNum, $pageSize, $userId);
    }

    public function getHostGroupsCount($userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting the count of all host groups", __FILE__, __LINE__);
        return sizeof($this->getPakiti()->getDao("HostGroup")->getHostGroupsIds(null, -1, -1, $userId));
    }

    /**
     * Create association between host and hostGroup
     */
    public function assignHostToHostGroup($hostId, $hostGroupId)
    {
        Utils::log(LOG_DEBUG, "Assign host to hostGroup [hostId=" . $hostId . ",hostGroupId=" . $hostGroupId . "]", __FILE__, __LINE__);
        $this->getPakiti()->getManager("DbManager")->query("insert ignore into HostHostGroup set
            hostId=".$this->getPakiti()->getManager("DbManager")->escape($hostId).",
            hostGroupId=".$this->getPakiti()->getManager("DbManager")->escape($hostGroupId));
    }

    /**
     * Removes the host from the host group.
     */
    public function removeHostFromHostGroups($hostId)
    {
        Utils::log(LOG_DEBUG, "Removing the host with ID[".$hostId."] from all host groups", __FILE__, __LINE__);
        $this->getPakiti()->getDao("HostGroup")->removeHostFromHostGroups($hostId);
    }

    public function deleteHostGroup($id)
    {
        Utils::log(LOG_DEBUG, "Deleting hostGroup[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("HostGroup");
        return $dao->delete($id);
    }
}
