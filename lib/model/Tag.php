<?php
/**
 * @access public
 * @author Michal Prochazka
 */
class Tag {
	private $_id = -1;
	private $_name;
	private $_description;
	private $_timestamp;
	private $_enabled = 1;

    public function __construct()
    {
    
    }
    
	public function getId() {
	  return $this->_id;
	}
	
  public function getName() {
	  return $this->_name;
	}
	
  public function getDescription() {
	  return $this->_description;
	}
	
  public function getTimestamp() {
	  return $this->_timestamp;
	}
	
  public function getEnabled() {
	  return $this->_enabled;
	}
		
  public function setId($value) {
	  $this->_id = $value;
	}
  
	public function setName($value) {
	  $this->_name = $value;
	}
	
	public function setDescription($value) {
	  $this->_description = $value;
	}
	public function setTimestamp($value) {
	  $this->_timestamp = $value;
	}
	public function setEnabled($value) {
	  $this->_enabled = $value;
	}
}
?>