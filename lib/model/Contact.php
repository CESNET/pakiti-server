<?php
require_once(realpath(dirname(__FILE__)) . '/ContactType.php');
require_once(realpath(dirname(__FILE__)) . '/HostGroup.php');
require_once(realpath(dirname(__FILE__)) . '/Domain.php');
require_once(realpath(dirname(__FILE__)) . '/User.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class Contact {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_value;
	/**
	 * @AssociationType ContactType
	 * @AssociationMultiplicity 1
	 */
	public $_contactType;
	/**
	 * @AssociationType HostGroup
	 * @AssociationMultiplicity 0..*
	 */
	public $_hostsGroup = array();
	/**
	 * @AssociationType Domain
	 * @AssociationMultiplicity 0..*
	 */
	public $_domain = array();
	/**
	 * @AssociationType User
	 * @AssociationMultiplicity 0..*
	 */
	public $_users = array();
}
?>