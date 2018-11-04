<?php

/**
 * @author Michal Prochazka
 */
class DefaultManager
{
    private $_pakiti;

    public function __construct(Pakiti $pakiti)
    {
        $this->_pakiti = $pakiti;
    }

    public function getPakiti()
    {
        return $this->_pakiti;
    }
}
