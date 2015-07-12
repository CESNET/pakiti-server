<?php
require_once(realpath(dirname(__FILE__)) . '/User.php');
require_once(realpath(dirname(__FILE__)) . '/CveException.php');
require_once(realpath(dirname(__FILE__)) . '/PkgException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class Exception {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_version;
	/**
	 * @AttributeType String
	 */
	private $_release;
	/**
	 * @AttributeType String
	 */
	private $_description;
	/**
	 * @AttributeType Timestamp
	 */
	private $_timestamp;
	/**
	 * @AttributeType int
	 */
	private $_enabled = 1;
	/**
	 * @AssociationType User
	 * @AssociationMultiplicity 1
	 */
	public $_user;
	/**
	 * @AssociationType CveException
	 * @AssociationMultiplicity 0..*
	 */
	public $_cvesExceptions = array();
	/**
	 * @AssociationType PkgException
	 * @AssociationMultiplicity 0..*
	 */
	public $_pkgsExceptions = array();
}
?>