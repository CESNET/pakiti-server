<?php
require_once(realpath(dirname(__FILE__)) . '/User.php');
#require_once(realpath(dirname(__FILE__)) . '/CveException.php');
require_once(realpath(dirname(__FILE__)) . '/PkgException.php');

/**
 * @access public
 * @author Michal Prochazka
 */
class CveException {
	/**
	 * @AttributeType int
	 */
	private $_id;
	private $_pkgId;
	private $_osGroupId;
	private $_cveName;
	private $_reason;
	private $_modifier;
	private $_timestamp;

	/**
	 * @return mixed
	 */
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/**
	 * @param mixed $timestamp
	 */
	public function setTimestamp($timestamp)
	{
		$this->_timestamp = $timestamp;
	}

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
	public function getPkgId()
	{
		return $this->_pkgId;
	}

	/**
	 * @param mixed $pkgId
	 */
	public function setPkgId($pkgId)
	{
		$this->_pkgId = $pkgId;
	}

	/**
	 * @return mixed
	 */
	public function getOsGroupId()
	{
		return $this->_osGroupId;
	}

	/**
	 * @param mixed $osGroupId
	 */
	public function setOsGroupId($osGroupId)
	{
		$this->_osGroupId = $osGroupId;
	}

	/**
	 * @return mixed
	 */
	public function getCveName()
	{
		return $this->_cveName;
	}

	/**
	 * @param mixed $cveName
	 */
	public function setCveName($cveName)
	{
		$this->_cveName = $cveName;
	}

	/**
	 * @return mixed
	 */
	public function getReason()
	{
		return $this->_reason;
	}

	/**
	 * @param mixed $reason
	 */
	public function setReason($reason)
	{
		$this->_reason = $reason;
	}

	/**
	 * @return mixed
	 */
	public function getModifier()
	{
		return $this->_modifier;
	}

	/**
	 * @param mixed $modifier
	 */
	public function setModifier($modifier)
	{
		$this->_modifier = $modifier;
	}

	/**
	 * @AttributeType String
	 */
	//private $_version;
	/**
	 * @AttributeType String
	 */
	//private $_release;
	/**
	 * @AttributeType String
	 */
	//private $_description;
	/**
	 * @AttributeType Timestamp
	 */
	//private $_timestamp;
	/**
	 * @AttributeType int
	 */
	//private $_enabled = 1;
	/**
	 * @AssociationType User
	 * @AssociationMultiplicity 1
	 */
	//public $_user;
	/**
	 * @AssociationType CveException
	 * @AssociationMultiplicity 0..*
	 */
	//public $_cvesExceptions = array();
	/**
	 * @AssociationType PkgException
	 * @AssociationMultiplicity 0..*
	 */
	//public $_pkgsExceptions = array();
}
?>