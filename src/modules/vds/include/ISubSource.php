<?php

/**
 * @author Michal Prochazka
 */
interface ISubSource
{
    /**
     * Get the definitions from the subsource.
     * Param: array of objects SubSourceDef
     */
    public function retrieveDefinitions();

    public function getName();

    public function getType();

    public function getId();

    public function setId($val);

    public function getSubSourceDefs();

    public function addSubSourceDef(ISubSourceDef $subSourceDef);

    public function removeSubSourceDef(ISubSourceDef $subSourceDef);
}
