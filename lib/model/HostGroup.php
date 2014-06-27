<?php
/**
 * @access public
 * @author Michal Prochazka
 */
class HostGroup {
  private $_id = -1;
  private $_name;

  public function HostGroup() {
  }

  public function getId() {
    return $this->_id;
  }

  public function getName() {
    return $this->_name;
  }

  public function setId($value) {
    $this->_id = $value;
  }

  public function setName($value) {
    $this->_name = $value;
  }
}
?>
