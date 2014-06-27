<?php
require_once(realpath(dirname(__FILE__)) . '/Os.php');
require_once(realpath(dirname(__FILE__)) . '/RepositoryDef.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class Repository {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_name;
	/**
	 * @AssociationType Os
	 * @AssociationMultiplicity 0..*
	 */
	public $_os = array();
	/**
	 * @AssociationType RepositoryDef
	 * @AssociationMultiplicity 0..*
	 */
	public $_repositoriesDefs = array();
}
?>