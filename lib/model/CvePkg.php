<?php
require_once(realpath(dirname(__FILE__)) . '/Cve.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/InstalledPkgCve.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class CvePkg {
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
	private $_operator;
	/**
	 * @AssociationType Cve
	 * @AssociationMultiplicity 1
	 */
	public $_cve;
	/**
	 * @AssociationType Pkg
	 * @AssociationMultiplicity 1
	 */
	public $_pkg;
	/**
	 * @AssociationType Oval
	 * @AssociationMultiplicity 1
	 */
	public $_oval;
	/**
	 * @AssociationType InstalledPkgCve
	 * @AssociationMultiplicity 0..*
	 */
	public $_installedPkgsCves = array();
}
?>