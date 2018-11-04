<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class Cve
{
    private $_id;
    private $_name;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }
}
