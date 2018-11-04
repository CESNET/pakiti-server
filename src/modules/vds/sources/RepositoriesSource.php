<?php

require_once(realpath(dirname(__FILE__)) . '/../lib/Source.php');

/**
 * @author Michal Prochazka
 */
class RepositoriesSource extends Source implements ISource {
	private static $NAME = "Repositories"; 
	private $_pakiti;
  
  public function __construct(Pakiti $pakiti) {
    parent::__construct($pakiti);

    $this->setName(RepositoriesSource::$NAME);
  }
  
  public function init() {
    parent::init();
  }

  public function retrieveVulnerabilities() {
  }


  /*
   * Get the name of this class.
   */
  public function getClassName() {
    return get_class();
  }


  protected function retreivePkgs() {
  }
}
