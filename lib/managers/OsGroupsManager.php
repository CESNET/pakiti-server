<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/2/15
 * Time: 8:16 PM
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

    public function getOsGroups($orderBy, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting all OS Groups", __FILE__, __LINE__);
        $osGroupsIds =& $this->getPakiti()->getDao("OsGroup")->getOsGroupsIds($orderBy, $pageSize, $pageNum);
        $osGroups = array();
        if ($osGroupsIds) {
            foreach ($osGroupsIds as $osGroupId) {
                array_push($osGroups, $this->getPakiti()->getDao("OsGroup")->getById($osGroupId));
            }
        }

        return $osGroups;
    }

    public function getOsGroupIdByName($name){
        Utils::log(LOG_DEBUG, "Getting os group ID by name [osGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getIdByName($name);
    }

    public function getOsGroupByOsId($osId){
        Utils::log(LOG_DEBUG, "Getting os group by osId [osId={$osId}]", __FILE__, __LINE__);
        $osGroup = $this->getPakiti()->getDao("OsGroup")->getByOsId($osId);
        if ($osGroup != null) {
            return $this->getPakiti()->getDao("OsGroup")->getById($osGroup->getId());
        } else {
            return null;
        }

    }


    /**
     * Create association between Os and OsGroup
     */
    public function assignOsToOsGroup(Os &$os, OsGroup &$osGroup)
    {
        if (($os == null) || ($os->getId() == -1) || ($osGroup == null)) {
            Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
            throw new Exception("Os or OsGroup object is not valid or Os.id|OsGroup.id is not set");
        }
        Utils::log(LOG_DEBUG, "Assigning the os to the os group [os=" . $os->getName() . ",osGroupName=" . $osGroup->getName() . "]", __FILE__, __LINE__);

        # Check if the tag already exists
        $isAssigned =
            $this->getPakiti()->getManager("DbManager")->queryToSingleValue(
                "select 1 from OsOsGroup where
    	 		osId=" . $this->getPakiti()->getManager("DbManager")->escape($os->getId()) . "");

        if ($isAssigned == null) {
            # Association between os and osGroup doesn't exist, so create it
            $this->getPakiti()->getManager("DbManager")->query(
                "insert into OsOsGroup set
          osId=" . $this->getPakiti()->getManager("DbManager")->escape($os->getId()) . ",
    	 		osGroupId=" . $this->getPakiti()->getManager("DbManager")->escape($osGroup->getId()));
        } else {
            # Update
            $this->getPakiti()->getManager("DbManager")->query(
                "update OsOsGroup set
          osId=" . $this->getPakiti()->getManager("DbManager")->escape($os->getId()) . ",
    	 		osGroupId=" . $this->getPakiti()->getManager("DbManager")->escape($osGroup->getId()) . " where
    	 		osId=" . $this->getPakiti()->getManager("DbManager")->escape($os->getId()) . "");
        }
    }

    public function removeOsFromOsGroups(Os &$os)
    {
        if (($os == null) || ($os->getId() == -1)) {
            Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
            throw new Exception("Os object is not valid or Os.id is not set");
        }
        Utils::log(LOG_DEBUG, "Removing the os from all os groups [os=" . $os->getName() . "]", __FILE__, __LINE__);

        $this->getPakiti()->getDao("OsGroup")->removeOsFromOsGroups($os->getId());
    }

    public function createOsGroup($name)
    {
        Utils::log(LOG_DEBUG, "Creating osGroup $name", __FILE__, __LINE__);
        $osGroup = new OsGroup();
        $osGroup->setName($name);
        $this->getPakiti()->getDao("OsGroup")->create($osGroup);

        return $osGroup;
    }

}