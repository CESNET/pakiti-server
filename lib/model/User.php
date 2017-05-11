<?php

class User
{
    private $_id = -1;
    private $_uid;
    private $_name;
    private $_email;
    private $_admin = 0;
    private $_createdAt;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

    public function getUid()
    {
        return $this->_uid;
    }

    public function setUid($val)
    {
        $this->_uid = $val;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($val)
    {
        $this->_name = $val;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function setEmail($val)
    {
        $this->_email = $val;
    }

    public function isAdmin()
    {
        return $this->_admin;
    }

    public function setAdmin($val)
    {
        if ($val) {
            $this->_admin = 1;
        } else {
            $this->_admin = 0;
        }
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function setCreatedAt($val)
    {
        $this->_createdAt = $val;
    }
}
