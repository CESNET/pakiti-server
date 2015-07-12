<?php
require_once(realpath(dirname(__FILE__)) . '/Tag.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/Arch.php');
require_once(realpath(dirname(__FILE__)) . '/RepositoryDef.php');
require_once(realpath(dirname(__FILE__)) . '/InstalledPkg.php');
require_once(realpath(dirname(__FILE__)) . '/PkgException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class RepositoryPkg {
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
	 * @AssociationType Tag
	 * @AssociationMultiplicity 0..*
	 */
	public $_tag = array();
	/**
	 * @AssociationType Pkg
	 * @AssociationMultiplicity 1
	 */
	public $_pkg;
	/**
	 * @AssociationType Arch
	 * @AssociationMultiplicity 1
	 */
	public $_arch;
	/**
	 * @AssociationType RepositoryDef
	 * @AssociationMultiplicity 1
	 */
	public $_repositoriesDef;
	/**
	 * @AssociationType InstalledPkg
	 * @AssociationMultiplicity 0..*
	 */
	public $_installedPkgs = array();
	/**
	 * @AssociationType PkgException
	 * @AssociationMultiplicity 0..*
	 */
	public $_pkgsExceptions = array();
}
?>