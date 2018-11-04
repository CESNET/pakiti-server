<?php

/**
 * @author Michal Prochazka
 */
interface ISource
{
    /**
     * Initilization routine
     */
    public function init();

    /**
     * Retreive vulnerabilities from all subsources
     */
    public function retrieveVulnerabilities();

    /**
     * Get the name, get/set ID of the VDS module
     */
    public function getName();
    public function getId();
    public function setId($id);

    /**
     * Get the name of the implementation class.
     */
    public function getClassName();

    /**
     * Get the list of registered subsources
     */
    public function getSubSources();

    /**
     * Get the subsource object by its ID
     */
    public function getSubSourceById($id);
}
