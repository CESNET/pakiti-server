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
class OsGroupsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeOsGroup(OsGroup &$osGroup)
    {
        Utils::log(LOG_DEBUG, "Storing the osGroup", __FILE__, __LINE__);
        if ($osGroup == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("OsGroup object is not valid");
        }

        $name = $osGroup->getName();

        $new = false;
        $dao = $this->getPakiti()->getDao("OsGroup");
        $osGroup->setId($dao->getIdByName($name));
        if ($osGroup->getId() == -1) {
            if (! array_key_exists($name, Config::$OS_GROUPS_MAPPING)) {
                Utils::log(LOG_ERR, "Refusing to store group '$name' that isn't enabled in the configuration");
                throw new Exception("Unknown OS Group ($name) requested");
            }
            $dao->create($osGroup);
            $new = true;
            # new osGroup, so recalculate Oses
            $this->recalculateOses($osGroup);
        }
        return $new;
    }

    public function recalculateOses(OsGroup $osGroup)
    {
        Utils::log(LOG_DEBUG, "Recalculating osGroup oses", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        $osesManager = $this->getPakiti()->getManager("OsesManager");

        $dao->unassignOsesFromOsGroup($osGroup->getId());
        foreach ($osesManager->getOses() as $os) {
            if (array_key_exists($osGroup->getName(), Config::$OS_GROUPS_MAPPING) && !empty(Config::$OS_GROUPS_MAPPING[$osGroup->getName()]) && !empty($os->getName()) && preg_match("/" . htmlspecialchars_decode(Config::$OS_GROUPS_MAPPING[$osGroup->getName()]) . "/", $os->getName()) == 1) {
                $osesManager->assignOsToOsGroup($os->getId(), $osGroup->getId());
            }
        }
    }

    public function getOsGroupById($id)
    {
        Utils::log(LOG_DEBUG, "Getting osGroup by id[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        return $dao->getById($id);
    }

    public function getOsGroups($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting osGroups", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        $ids = $dao->getIds($orderBy, $pageSize, $pageNum);

        $osGroups = array();
        foreach ($ids as $id) {
            array_push($osGroups, $dao->getById($id));
        }
        return $osGroups;
    }

    public function getOsGroupsIds()
    {
        Utils::log(LOG_DEBUG, "Getting all osGroups IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        return $dao->getIds();
    }

    public function getOsGroupByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting osGroup ID by name[$name]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        return $dao->getById($dao->getIdByName($name));
    }

    public function getOsGroupIdByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting osGroup ID by name[$name]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        return $dao->getIdByName($name);
    }

    public function getOsGroupsByOs($osId)
    {
        Utils::log(LOG_DEBUG, "Getting osGroups by os[$osId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("OsGroup");
        $ids = $dao->getIdsByOsId($osId);

        $osGroups = array();
        foreach ($ids as $id) {
            array_push($osGroups, $dao->getById($id));
        }
        return $osGroups;
    }
}
