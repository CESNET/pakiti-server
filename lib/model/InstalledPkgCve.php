<?php
require_once(realpath(dirname(__FILE__)) . '/Host.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/CvePkg.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class InstalledPkgCve {
	/**
	 * @AssociationType Host
	 * @AssociationMultiplicity 1
	 */
	public $_host;
	/**
	 * @AssociationType Pkg
	 * @AssociationMultiplicity 1
	 */
	public $_pkg;
	/**
	 * @AssociationType CvePkg
	 * @AssociationMultiplicity 1
	 */
	public $_cvesPkg;
}
?>