<?php
require_once(realpath(dirname(__FILE__)) . '/Tag.php');
require_once(realpath(dirname(__FILE__)) . '/Pkg.php');
require_once(realpath(dirname(__FILE__)) . '/Arch.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class PkgTag {
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
	 * @AssociationMultiplicity 1
	 */
	public $_tag;
	/**
	 * @AssociationType Pkg
	 * @AssociationMultiplicity 1
	 */
	public $_pkg;
	/**
	 * @AssociationType Arch
	 * @AssociationMultiplicity 0..1
	 */
	public $_arch;
}
?>