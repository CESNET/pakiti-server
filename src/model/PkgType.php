<?php

/**
 * @author Jakub Mlcak
 */
class PkgType
{
    private $_id = -1;
    private $_name;

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
}
