<?php

require_once(realpath(dirname(__FILE__)) . '/../lib/Source.php');

/**
 * @author Michal Prochazka
 */
class CveSource extends Source implements ISource
{
    private static $NAME = "Cve";
    private $_pakiti;

    /**
     * Load all types of CVE sources
     */
    public function __construct(Pakiti $pakiti)
    {
        parent::__construct($pakiti);

        $this->_pakiti = $pakiti;
        $this->setName(CveSource::$NAME);
    }

    /**
     * Initialization routine
     */
    public function init()
    {
        parent::init();
        # Get module ID from the DB
    }

    /**
     * Get the name of this class.
     */
    public function getClassName()
    {
        return get_class();
    }

    /**
     * Ask all CVE sources to provide the complete list of CVE definitions
     */
    public function retrieveVulnerabilities()
    {
        foreach ($this->getSubSources() as $subSource)
            $subSource->retrieveVulnerabilities();
    }

}
