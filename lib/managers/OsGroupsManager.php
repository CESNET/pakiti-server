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

    public function getOsGroupByName($name) {
        Utils::log(LOG_DEBUG, "Getting os group by name [osGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getByName($name);
    }

    public function getOsGroupIdByName($name){
        Utils::log(LOG_DEBUG, "Getting os group ID by name [osGroupName=$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getIdByName($name);
    }

    public function getOsGroupByOsId($osId){
        Utils::log(LOG_DEBUG, "Getting os group by osId [osId={$osId}]", __FILE__, __LINE__);
        $osGroup = $this->getPakiti()->getDao("OsGroup")->getByOsId($osId);
        return $this->getPakiti()->getDao("OsGroup")->getById($osGroup->getId());
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