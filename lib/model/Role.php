<?php
require_once(realpath(dirname(__FILE__)) . '/User.php');
require_once(realpath(dirname(__FILE__)) . '/UserRole.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class Role {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_name;
	/**
	 * @AssociationType User
	 * @AssociationMultiplicity 0..*
	 */
	public $_users = array();
	/**
	 * @AssociationType UserRole
	 * @AssociationMultiplicity 0..*
	 */
	public $_usersRoles = array();
}
?>