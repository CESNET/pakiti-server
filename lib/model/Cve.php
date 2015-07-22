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

	private $_cveDefId = -1;

	private $_tag = array();

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getCveDefId()
	{
		return $this->_cveDefId;
	}

	/**
	 * @param mixed $cveDefId
	 */
	public function setCveDefId($cveDefId)
	{
		$this->_cveDefId = $cveDefId;
	}

	/**
	 * @return array
	 */
	public function getTag()
	{
		return $this->_tag;
	}

	/**
	 * @param array $tag
	 */
	public function setTag($tag)
	{
		$this->_tag = $tag;
	}

}
?>