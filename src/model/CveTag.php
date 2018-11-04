<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class CveTag
{
    private $_id = -1;
    private $_cveName;
    private $_tagName;
    private $_reason;
    private $_infoUrl;
    private $_timestamp;
    private $_enabled = 1;
    private $_modifier;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($value)
    {
        $this->_id = $value;
    }

    public function getCveName()
    {
        return $this->_cveName;
    }

    public function setCveName($value)
    {
        $this->_cveName = $value;
    }

    public function getTagName()
    {
        return $this->_tagName;
    }

    public function setTagName($value)
    {
        $this->_tagName = $value;
    }

    public function getReason()
    {
        return $this->_reason;
    }

    public function setReason($value)
    {
        $this->_reason = $value;
    }

    public function getInfoUrl()
    {
        return $this->_infoUrl;
    }

    public function setInfoUrl($value)
    {
        $this->_infoUrl = $value;
    }

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setTimestamp($value)
    {
        $this->_timestamp = $value;
    }

    public function isEnabled()
    {
        return $this->_enabled;
    }

    public function setEnabled($value)
    {
        if ($value) {
            $this->_enabled = 1;
        } else {
            $this->_enabled = 0;
        }
    }

    public function getModifier()
    {
        return $this->_modifier;
    }

    public function setModifier($value)
    {
        $this->_modifier = $value;
    }
}
