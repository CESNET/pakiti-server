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
 * @author Jakub Mlcak
 */
class OsesManager extends DefaultManager
{
    /**
    * Create if not exist, else set id
    * @return false if already exist
    */
    public function storeOs(Os &$os)
    {
        Utils::log(LOG_DEBUG, "Storing the os", __FILE__, __LINE__);
        if ($os == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Os object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Os");
        $os->setId($dao->getIdByName($os->getName()));
        if ($os->getId() == -1) {
            # Os is missing, so store it
            $dao->create($os);
            $new = true;
            $this->recalculateOsGroups($os);
        }
        return $new;
    }

    private function recalculateOsGroups(Os $os)
    {
        Utils::log(LOG_DEBUG, "Recalculating os osGroups", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Os");
        $osGroupsManager = $this->getPakiti()->getManager("OsGroupsManager");

        $dao->unassignOsGroupsFromOs($os->getId());
        foreach ($osGroupsManager->getOsGroups() as $osGroup) {
            if (array_key_exists($osGroup->getName(), Config::$OS_GROUPS_MAPPING) && !empty(Config::$OS_GROUPS_MAPPING[$osGroup->getName()]) && !empty($os->getName()) && preg_match("/" . htmlspecialchars_decode(Config::$OS_GROUPS_MAPPING[$osGroup->getName()]) . "/", $os->getName()) == 1) {
                $dao->assignOsToOsGroup($os->getId(), $osGroup->getId());
            }
        }
    }

    public function getOsById($id)
    {
        Utils::log(LOG_DEBUG, "Getting os by ID[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Os");
        return $dao->getById($id);
    }

    public function assignOsToOsGroup($osId, $osGroupId)
    {
        Utils::log(LOG_DEBUG, "Assign OS to OS group", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Os");
        return $dao->assignOsToOsGroup($osId, $osGroupId);
    }

    public function getOses($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting oses", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Os");
        $ids = $dao->getIds($orderBy, $pageSize, $pageNum);

        $oses = array();
        foreach ($ids as $id) {
            array_push($oses, $dao->getById($id));
        }
        return $oses;
    }
}
