<?php
require_once(realpath(dirname(__FILE__)) . '/RepositoryPkg.php');
require_once(realpath(dirname(__FILE__)) . '/DefaultException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class PkgException {
	/**
	 * @AssociationType RepositoryPkg
	 * @AssociationMultiplicity 1
	 */
	public $_repositoryPkg;
	/**
	 * @AssociationType Exception
	 * @AssociationMultiplicity 1
	 */
	public $_exception;
}
?>