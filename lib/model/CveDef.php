<?php

/**

 * User: Vadym Yanovskyy
 * Date: 7/20/15
 * Time: 2:34 PM
 */
class CveDef
{
    private $_id;
    private $_definitionId;
    private $_title;
    private $_refUrl;
    private $_vdsSubSourceDefId;
    private $_cves = array();

    /**
     * @return array
     */
    public function getCves()
    {
        return $this->_cves;
    }

    /**
     * @param array $cves
     */
    public function setCves($cves)
    {
        $this->_cves = $cves;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return mixed
     */
    public function getDefinitionId()
    {
        return $this->_definitionId;
    }

    /**
     * @param mixed $definitionId
     */
    public function setDefinitionId($definitionId)
    {
        $this->_definitionId = $definitionId;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return mixed
     */
    public function getRefUrl()
    {
        return $this->_refUrl;
    }

    /**
     * @param mixed $refUrl
     */
    public function setRefUrl($refUrl)
    {
        $this->_refUrl = $refUrl;
    }

    /**
     * @return mixed
     */
    public function getVdsSubSourceDefId()
    {
        return $this->_vdsSubSourceDefId;
    }

    /**
     * @param mixed $vdsSubSourceDefId
     */
    public function setVdsSubSourceDefId($vdsSubSourceDefId)
    {
        $this->_vdsSubSourceDefId = $vdsSubSourceDefId;
    }

}