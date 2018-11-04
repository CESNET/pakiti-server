<?php

/**
 * @author Jakub Mlcak
 */
class OsesManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeOs(Os $os)
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

    public function recalculateOsGroups(Os $os)
    {
        Utils::log(LOG_DEBUG, "Recalculating os osGroups", __FILE__, __LINE__);

        if (empty($os->getName()))
            return;

        $dao = $this->getPakiti()->getDao("Os");
        $osGroupsManager = $this->getPakiti()->getManager("OsGroupsManager");

        $dao->unassignOsGroupsFromOs($os->getId());
        foreach (array_keys(Config::$OS_GROUPS_MAPPING) as $osGroupName) {
            if (!empty(Config::$OS_GROUPS_MAPPING[$osGroupName]) && preg_match("/" . htmlspecialchars_decode(Config::$OS_GROUPS_MAPPING[$osGroupName]). "/", $os->getName()) == 1) {
                $og = $osGroupsManager->getOsGroupByName($osGroupName);
                if ($og == null) {
                    /* group assignment is re-calculted on its adding */
                    $new = new OsGroup();
                    $new->setName($osGroupName);
                    $osGroupsManager->storeOsGroup($new);
                } else {
                    $dao->assignOsToOsGroup($os->getId(), $og->getId());
                }
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
