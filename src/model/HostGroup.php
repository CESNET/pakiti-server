<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class HostGroup
{
    private $_id = -1;
    private $_name;
    private $_url;
    private $_contact;
    private $_note;

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

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($val)
    {
        $this->_url = $val;
    }

    public function getContact()
    {
        return $this->_contact;
    }

    public function setContact($val)
    {
        $this->_contact = $val;
    }

    public function getNote()
    {
        return $this->_note;
    }

    public function setNote($val)
    {
        $this->_note = $val;
    }
}
