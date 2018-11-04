<?php

/**
 * @author Michal Prochazka
 */
interface ISubSourceDef
{
    public function getId();

    public function getName();

    public function getUri();

    public function getEnabled();

    public function getLastChecked();

    public function getSubSourceId();

    public function setId($val);

    public function setName($val);

    public function setUri($val);

    public function setEnabled($val);

    public function setLastChecked($val);

    public function setSubSourceId($val);
}
