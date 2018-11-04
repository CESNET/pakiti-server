<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class Pkg
{
    private $_id = -1;
    private $_name;
    private $_version;
    private $_release;
    private $_archId;
    private $_pkgTypeId;

    # Only getters - loaded from db [1:1] with 1 column
    private $_archName;
    private $_pkgTypeName;

    public function getVersionRelease()
    {
        if (!empty($this->_release)) {
            return $this->_version . "-" . $this->_release;
        } else {
            return $this->_version;
        }
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($val)
    {
        $this->_name = $val;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function setVersion($val)
    {
        $this->_version = $val;
    }

    public function getRelease()
    {
        return $this->_release;
    }

    public function setRelease($val)
    {
        $this->_release = $val;
    }

    public function getArchId()
    {
        return $this->_archId;
    }

    public function setArchId($val)
    {
        $this->_archId = $val;
    }

    public function getPkgTypeId()
    {
        return $this->_pkgTypeId;
    }

    public function setPkgTypeId($val)
    {
        $this->_pkgTypeId = $val;
    }

    # Extra

    public function getArchName()
    {
        return $this->_archName;
    }

    public function getPkgTypeName()
    {
        return $this->_pkgTypeName;
    }

    # Used only in FeederModule parsePkgs
    public function setArchName($val)
    {
        $this->_archName = $val;
    }

    public function setPkgTypeName($val)
    {
        $this->_pkgTypeName = $val;
    }
}
