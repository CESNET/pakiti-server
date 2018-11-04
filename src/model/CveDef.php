<?php

/**
 * @author Vadym Yanovskyy
 */
class CveDef
{
    private $_id;
    private $_definitionId;
    private $_title;
    private $_refUrl;
    private $_vdsSubSourceDefId;
    private $_cves = array();

    public function getCves()
    {
        return $this->_cves;
    }

    public function setCves($cves)
    {
        $this->_cves = $cves;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getDefinitionId()
    {
        return $this->_definitionId;
    }

    public function setDefinitionId($definitionId)
    {
        $this->_definitionId = $definitionId;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getRefUrl()
    {
        return $this->_refUrl;
    }

    public function setRefUrl($refUrl)
    {
        $this->_refUrl = $refUrl;
    }

    public function getVdsSubSourceDefId()
    {
        return $this->_vdsSubSourceDefId;
    }

    public function setVdsSubSourceDefId($vdsSubSourceDefId)
    {
        $this->_vdsSubSourceDefId = $vdsSubSourceDefId;
    }
}
