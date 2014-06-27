<?php
require_once(realpath(dirname(__FILE__)) . '/Contact.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class ContactType {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_name;
	/**
	 * @AssociationType Contact
	 * @AssociationMultiplicity 0..*
	 */
	public $_contacts = array();
}
?>