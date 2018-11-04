<?php

/**
 * @author Michal Prochazka
 */
class CveException
{
    private $_id;
    private $_pkgId;
    private $_osGroupId;
    private $_cveName;
    private $_reason;
    private $_modifier;
    private $_timestamp;

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getPkgId()
    {
        return $this->_pkgId;
    }

    public function setPkgId($pkgId)
    {
        $this->_pkgId = $pkgId;
    }

    public function getOsGroupId()
    {
        return $this->_osGroupId;
    }

    public function setOsGroupId($osGroupId)
    {
        $this->_osGroupId = $osGroupId;
    }

    public function getCveName()
    {
        return $this->_cveName;
    }

    public function setCveName($cveName)
    {
        $this->_cveName = $cveName;
    }

    public function getReason()
    {
        return $this->_reason;
    }

    public function setReason($reason)
    {
        $this->_reason = $reason;
    }

    public function getModifier()
    {
        return $this->_modifier;
    }

    public function setModifier($modifier)
    {
        $this->_modifier = $modifier;
    }
}
