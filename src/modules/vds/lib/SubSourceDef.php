<?php

/**
 * @author Michal Prochazka
 */
class SubSourceDef implements ISubSourceDef
{
    private $_id = -1;
    private $_name;
    private $_uri;
    private $_enabled;
    private $_lastChecked;
    private $_subSourceId = -1;

    public function getId()
    {
        return $this->_id;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function getEnabled()
    {
        return $this->_enabled;
    }

    public function getLastChecked()
    {
        return $this->_lastChecked;
    }

    public function getSubSourceId()
    {
        return $this->_subSourceId;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

    public function setName($val)
    {
        $this->_name = $val;
    }

    public function setUri($val)
    {
        $this->_uri = $val;
    }

    public function setEnabled($val)
    {
        $this->_enabled = $val;
    }

    public function setLastChecked($val)
    {
        $this->_lastChecked = $val;
    }

    public function setSubSourceId($val)
    {
        $this->_subSourceId = $val;
    }
}
