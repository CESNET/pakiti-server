<?php
require_once(realpath(dirname(__FILE__)) . '/User.php');
require_once(realpath(dirname(__FILE__)) . '/Role.php');
require_once(realpath(dirname(__FILE__)) . '/Domain.php');
require_once(realpath(dirname(__FILE__)) . '/HostGroup.php');
require_once(realpath(dirname(__FILE__)) . '/Host.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class UserRole {
	/**
	 * @AssociationType User
	 * @AssociationMultiplicity 1
	 */
	public $_user;
	/**
	 * @AssociationType Role
	 * @AssociationMultiplicity 1
	 */
	public $_role;
	/**
	 * @AssociationType Domain
	 * @AssociationMultiplicity 1
	 */
	public $_domain;
	/**
	 * @AssociationType HostGroup
	 * @AssociationMultiplicity 1
	 */
	public $_hostsGroup;
	/**
	 * @AssociationType Host
	 * @AssociationMultiplicity 1
	 */
	public $_host;
}
?>