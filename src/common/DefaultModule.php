<?php

/**
 * @author Michal Prochazka
 */
class DefaultModule
{
    private $_pakiti;

    public function __construct($pakiti)
    {
        $this->_pakiti = $pakiti;
    }

    public function getPakiti()
    {
        return $this->_pakiti;
    }
}
