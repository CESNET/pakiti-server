<?php
require_once(realpath(dirname(__FILE__)) . '/Cve.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/DefaultException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class CveException {
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
	 * @AssociationType Exception
	 * @AssociationMultiplicity 1
	 */
	public $_exception;
}
?>