<?php
require_once(realpath(dirname(__FILE__)) . '/Tag.php');
require_once(realpath(dirname(__FILE__)) . '/CvePkg.php');
require_once(realpath(dirname(__FILE__)) . '/CveException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class Cve {
	/**
	 * @AttributeType int
	 */
	private $_id;
	/**
	 * @AttributeType String
	 */
	private $_name;
	/**
	 * @AssociationType Tag
	 * @AssociationMultiplicity 0..*
	 */
	public $_tag = array();
	/**
	 * @AssociationType CvePkg
	 * @AssociationMultiplicity 0..*
	 */
	public $_cvesPkgs = array();
	/**
	 * @AssociationType CveException
	 * @AssociationMultiplicity 0..*
	 */
	public $_cvesExceptions = array();
}
?>