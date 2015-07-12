<?php
require_once(realpath(dirname(__FILE__)) . '/Role.php');
require_once(realpath(dirname(__FILE__)) . '/Contact.php');
require_once(realpath(dirname(__FILE__)) . '/UserRole.php');
require_once(realpath(dirname(__FILE__)) . '/Tag.php');
require_once(realpath(dirname(__FILE__)) . '/DefaultException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class User {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_name;
	/**
	 * @AttributeType int
	 */
	private $_isPakitiAdmin = 0;
	/**
	 * @AssociationType Role
	 * @AssociationMultiplicity 0..*
	 */
	public $_roles = array();
	/**
	 * @AssociationType Contact
	 * @AssociationMultiplicity 1
	 */
	public $_contact;
	/**
	 * @AssociationType UserRole
	 * @AssociationMultiplicity 0..*
	 */
	public $_usersRoles = array();
	/**
	 * @AssociationType Tag
	 * @AssociationMultiplicity 0..*
	 */
	public $_tags = array();
	/**
	 * @AssociationType Exception
	 * @AssociationMultiplicity 0..*
	 */
	public $_exceptions = array();
}
?>