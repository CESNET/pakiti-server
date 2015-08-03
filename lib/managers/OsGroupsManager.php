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

    public function getOsGroupByOs(Os &$os){
        Utils::log(LOG_DEBUG, "Getting os group by os [os={$os->getName()}]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("OsGroup")->getByOsId($os->getId());
    }

}