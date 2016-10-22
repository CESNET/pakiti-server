<?php
/**
 * @access public
 * @author Jakub Mlcak
 */
class Stat {
  private $_name;
  private $_value = 0;

  public function Arch() {
  }

  public function getName() {
    return $this->_name;
  }

  public function getValue() {
    return $this->_value;
  }

  public function setName($val) {
    $this->_name = $val;
  }

  public function setValue($val) {
    $this->_value = $val;
  }
}
?>
