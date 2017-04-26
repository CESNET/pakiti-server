<?php

/**
 * @access public
 * @author Michal Prochazka
 */
class Tag
{
    private $_id = -1;
    private $_name;
    private $_cveName;
    private $_description;
    private $_reason;
    private $_infoUrl;
    private $_modifier;
    private $_timestamp;
    private $_enabled = 1;

    public function __construct()
    {

    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($value)
    {
        $this->_id = $value;
    }

    /**
     * @return mixed
     */
    public function getCveName()
    {
        return $this->_cveName;
    }

    /**
     * @param mixed $cveName
     */
    public function setCveName($cveName)
    {
        $this->_cveName = $cveName;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($value)
    {
        $this->_name = $value;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($value)
    {
        $this->_description = $value;
    }

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setTimestamp($value)
    {
        $this->_timestamp = $value;
    }

    public function getEnabled()
    {
        return $this->_enabled;
    }

    public function setEnabled($value)
    {
        $this->_enabled = $value;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->_reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->_reason = $reason;
    }

    public function getInfoUrl()
    {
        return $this->_infoUrl;
    }

    public function setInfoUrl($infoUrl)
    {
        $this->_infoUrl = $infoUrl;
    }

    /**
     * @return mixed
     */
    public function getModifier()
    {
        return $this->_modifier;
    }

    /**
     * @param mixed $modifier
     */
    public function setModifier($modifier)
    {
        $this->_modifier = $modifier;
    }
}

?>