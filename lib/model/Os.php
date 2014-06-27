<?php
/**
 * @access public
 * @author Michal Prochazka
 */
class Os {
	private $_id = -1;
	private $_name;
	
  public function Os() {
  }
  
	public function getId() {
	  return $this->_id;
	}
	
  public function getName() {
	  return $this->_name;
	}
	
	public function setId($val) {
	  $this->_id = $val;
	}
	
  public function setName($val) {
	  $this->_name = $val;
	}
}
?>