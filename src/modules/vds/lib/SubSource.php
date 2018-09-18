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

require_once(realpath(dirname(__FILE__)) . '/../include/ISubSourceDef.php');
require_once(realpath(dirname(__FILE__)) . '/SubSourceDef.php');

/**
 * @author Michal Prochazka
 */
class SubSource
{
    private $_id;
    private $_pakiti;
    private $_db;

    public function __construct(Pakiti $pakiti)
    {
        $this->_pakiti =& $pakiti;
        $this->_db = $pakiti->getManager("DbManager");
        $this->_subSourceDefs = array();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

	public function getName()
	{
		/* $NAME must be defined in any child class */
		return static::$NAME;
	}

	public function getType()
	{
		/* $TYPE must be defined in any child class */
		return static::$TYPE;
	}

	public function getClassName()
	{
		return get_called_class();
	}

    public function getSubSourceDefs()
    {
        $sql = "select id as _id, name as _name, uri as _uri, enabled as _enabled, lastChecked as _lastChecked, vdsSubSourceId as _subSourceId from VdsSubSourceDef
            where vdsSubSourceId=".$this->_db->escape($this->getId());
        return $this->_db->queryObjects($sql, "SubSourceDef");
    }

    public function addSubSourceDef(ISubSourceDef $subSourceDef)
    {
        # Check if the subSourceDef already exists
        if (($id = $this->getBy($subSourceDef->getName(), "name") == null)) {
            $this->_db->query("insert into VdsSubSourceDef set
                name='".$this->_db->escape($subSourceDef->getName())."',
                uri='".$this->_db->escape($subSourceDef->getUri())."',
                enabled=1,
                vdsSubSourceId=".$this->_db->escape($subSourceDef->getSubSourceId()));

            # Set the newly assigned id
            $subSourceDef->setId($this->_db->getLastInsertedId());
        } else {
            $subSourceDef->setId($id);
        }
    }


    /**
     * Compute the hash, currently MD5
     */
    protected function computeHash($string)
    {
        return md5($this->getName() . $string);
    }

    public function removeSubSourceDef(ISubSourceDef $subSourceDef)
    {
        $this->_db->query("delete from VdsSubSourceDef where id=".$this->_db->escape($subSourceDef->getId()));
    }

    /**
     * Update the SubSourceDef in the DB
     */
    public function updateSubSourceDef(ISubSourceDef $subSourceDef)
    {
        $this->_db->query("update VdsSubSourceDef set
            name='".$this->_db->escape($subSourceDef->getName()).",
            uri='".$this->_db->escape($subSourceDef->getUri())."',
            enabled=".$this->_db->escape($subSourceDef->getEnabled()).",
            lastChecked='".$this->_db->escape($subSourceDef->getLastChecked())."',
            vdsSubSourceId=".$this->_db->escape($subSourceDef->getSubSourceId())." 
            where id=".$this->_db->escape($subSourceDef->getId()));
    }

    public function getLastSubSourceDefHash(ISubSourceDef $subSourceDef)
    {
        $row = $this->_db->queryToSingleRow("select lastSubSourceDefHash from VdsSubSourceDef
            where id=" . $this->_db->escape($subSourceDef->getId()));
        return $row["lastSubSourceDefHash"];
    }

    protected function isSubSourceDefContainsNewData(ISubSourceDef $subSourceDef, $currentSubSourceHash)
    {
        $lastSubSourceHash = $this->getLastSubSourceDefHash($subSourceDef);
        if ($lastSubSourceHash != null && $lastSubSourceHash == $currentSubSourceHash) {
            # Data are the same as stored one, so we do not need to store anything
            Utils::log(LOG_DEBUG, "SubSourceDef [SubSourceDef=" . $subSourceDef->getName() . "] doesn't contain any new data, exiting...", __FILE__, __LINE__);
            $this->updateSubSourceLastChecked($subSourceDef);
            return false;
        }
        return true;
    }

    /**
     * Update SubSourceDef has in DB
     * @param ISubSourceDef $subSourceDef
     * @param $subSourceDefHash
     */
    public function updateLastSubSourceDefHash(ISubSourceDef $subSourceDef, $subSourceDefHash)
    {
        $this->_db->query("update VdsSubSourceDef set
            lastSubSourceDefHash='" . $this->_db->escape($subSourceDefHash) . "'
            where id=" . $this->_db->escape($subSourceDef->getId()));
    }

    /**
     * Enable the sourceDef
     */
    public function enableSubSourceDef(ISubSourceDef $subSourceDef)
    {
        $this->_db->query("update VdsSubSourceDef set enabled=".Constants::$ENABLED." where id=".$this->_db->escape($subSourceDef->getId()));
    }

    /**
     * Disable the sourceDef
     */
    public function disableSubSourceDef(ISubSourceDef $subSourceDef)
    {
        $this->_db->query("update VdsSubSourceDef set enabled=".Constants::$DISABLED." where id=".$this->_db->escape($subSourceDef->getId()));
    }

    public function updateSubSourceLastChecked(ISubSourceDef $subSourceDef)
    {
        $this->_db->query("update VdsSubSourceDef set lastChecked=now() where id=".$this->_db->escape($subSourceDef->getId()));
    }

    /**
     * Assign OS for the subSourceDef
     */
    public function assignOsToSubSourceDef(ISubSourceDef $subSourceDef, Os $os)
    {
        $this->_db->query("insert into VdsSourceDefOs (vdsSubSourceId, osId) values (" . $this->_db->escape($subSourceDef->getId()) .",". $this->_db->escape($os->getId()) . ")");
    }

    /**
     * Remove link between OS and subSourceDef
     */
    public function removeOsFromSubSourceDef(ISubSourceDef $subSourceDef, Os $os)
    {
        $this->_db->query("delete from VdsSourceDefOs
            where vdsSubSourceId=" . $this->_db->escape($subSourceDef->getId()) . "
            and osId=". $this->_db->escape($os->getId()) . "");
    }

    public function getSubSourceDefById($id)
    {
        return $this->getBy($id, "id");
    }

    /**
     * Get the SubSourceDef ID by its name
     */
    protected function getIdByName($name)
    {
        $id = $this->_db->queryToSingleValue("select id from VdsSubSourceDef
            where name='".$this->_db->escape($name)."'");
        if ($id == null) {
            return -1;
        }
        return $id;
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=".$this->_db->escape($value);
        } elseif ($type == "name") {
            $where = "name='".$this->_db->escape($value)."'";
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->_db->queryObject("select id as _id, name as _name, uri as _uri, enabled as _enabled, lastChecked as _lastChecked, vdsSubSourceId as _subSourceId from VdsSubSourceDef
            where $where", "SubSourceDef");
    }
}
