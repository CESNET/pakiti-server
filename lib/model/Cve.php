<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

require_once(realpath(dirname(__FILE__)) . '/Tag.php');
require_once(realpath(dirname(__FILE__)) . '/CvePkg.php');
require_once(realpath(dirname(__FILE__)) . '/CveException.php');

/**
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
	private $_cveExceptions = array();

	/**
	 * @return array
	 */
	public function getCveExceptions()
	{
		return $this->_cveExceptions;
	}

	/**
	 * @param array $cveExceptions
	 */
	public function setCveExceptions($cveExceptions)
	{
		$this->_cveExceptions = $cveExceptions;
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