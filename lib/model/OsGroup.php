<?php
/**
 * @access public
 * @author Michal Prochazka
 */
class OsGroup {
	private $_id = -1;
	private $_name;
	private $_regex;

	/**
	 * @return mixed
	 */
	public function getRegex()
	{
		return $this->_regex;
	}

	/**
	 * @param mixed $regex
	 */
	public function setRegex($regex)
	{
		$this->_regex = $regex;
	}
	
  public function OsGroup() {
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
