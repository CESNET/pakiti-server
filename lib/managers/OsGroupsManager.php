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
class OsGroupsManager extends DefaultManager{
    private $_pakiti;

    public function __construct(Pakiti &$pakiti) {
        $this->_pakiti =& $pakiti;
    }

    public function getPakiti() {
        return $this->_pakiti;
    }

    public function getOsGroupById($id) {
        Utils::log(LOG_DEBUG, "Getting os group by id [osGroupId=$id]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getById($id);
    }

    public function getOsById($id)
    {
        Utils::log(LOG_DEBUG, "Getting os by id [os=$id]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Os")->getById($id);
    }


    public function getOsGroupByName($name) {
        Utils::log(LOG_DEBUG, "Getting os group by name [osGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getByName($name);
    }

    public function getOsGroups($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting all OS Groups", __FILE__, __LINE__);
        $osGroupsIds =& $this->getPakiti()->getDao("OsGroup")->getOsGroupsIds($orderBy, $pageSize, $pageNum);
        $osGroups = array();
        foreach ($osGroupsIds as $osGroupId) {
            array_push($osGroups, $this->getPakiti()->getDao("OsGroup")->getById($osGroupId));
        }

        return $osGroups;
    }

    public function getOsGroupsIds() {
        Utils::log(LOG_DEBUG, "Getting all OS groups IDs", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getOsGroupsIds();
    }

    public function getOsGroupIdByName($name){
        Utils::log(LOG_DEBUG, "Getting os group ID by name [osGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getIdByName($name);
    }

    /** For a particular Os find its OsGroups
     * @param Os $os
     * @return array
     * @throws Exception
     */
    public function getOsGroupsByOs(Os $os)
    {
        if (($os == null) || ($os->getName() == "")) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Os object is not valid or Os.name is not set");
        }

        $osGroups = array();
        foreach ($this->getOsGroups("name") as $osGroup) {
            if ($osGroup->getRegex()) {
                if (preg_match("/" . htmlspecialchars_decode($osGroup->getRegex()) . "/", $os->getName()) == 1) array_push($osGroups, $osGroup);
            }
        }
        // if Os doesn't have any OsGroups if belongs to Unknown OsGroup
        if (empty($osGroups)) {
            $unknownOsGroup = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupByName("unknown");
            array_push($osGroups, $unknownOsGroup);
        }
        return $osGroups;
    }

    public function getOsGroupsIdsByOsName($osName){
        $os = new Os();
        $os->setName($osName);
        $osGroups = $this->getOsGroupsByOs($os);
        $osGroupsIds = array_map(function ($osGroup) {
            return $osGroup->getId();
        }, $osGroups);
        return $osGroupsIds;
    }

    public function createOsGroup($name)
    {
        if ($name == "") {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("OsGroup name is not valid");
        }

        Utils::log(LOG_DEBUG, "Creating osGroup $name", __FILE__, __LINE__);
        $osGroup = new OsGroup();
        $osGroup->setName($name);
        $this->getPakiti()->getDao("OsGroup")->create($osGroup);

        return $osGroup;
    }

    public function updateOsGroup(OsGroup $osGroup)
    {
        if (($osGroup == null) || ($osGroup->getId() == -1)) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("OsGroup object is not valid or Os.id is not set");
        }

        Utils::log(LOG_DEBUG, "Updating osGroup {$osGroup->getName()}", __FILE__, __LINE__);
        $this->getPakiti()->getDao("OsGroup")->update($osGroup);

    }

}
