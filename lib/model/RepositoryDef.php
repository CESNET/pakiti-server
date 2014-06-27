<?php
require_once(realpath(dirname(__FILE__)) . '/Arch.php');
require_once(realpath(dirname(__FILE__)) . '/Repository.php');
require_once(realpath(dirname(__FILE__)) . '/RepositoryPkg.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class RepositoryDef {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_url;
	/**
	 * @AttributeType int
	 */
	private $_enabled = 1;
	/**
	 * @AttributeType Timestamp
	 */
	private $_lastChecked;
	/**
	 * @AssociationType Arch
	 * @AssociationMultiplicity 1
	 */
	public $_arch;
	/**
	 * @AssociationType Repository
	 * @AssociationMultiplicity 1
	 */
	public $_repository;
	/**
	 * @AssociationType RepositoryPkg
	 * @AssociationMultiplicity 0..*
	 */
	public $_repositoriesPkgs = array();
}
?>