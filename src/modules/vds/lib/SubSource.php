<?php

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
        $this->_pakiti = $pakiti;
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

    public function retrieveVulnerabilities()
    {
        foreach ($this->getSubSourceDefs() as $subSourceDef) {
            try {
                $contents = Utils::downloadContents($subSourceDef->getUri());
            } catch (Exception $e) {
                Utils::log(LOG_ERR, "Error reading definitions for ".$subSourceDef->getUri().": ".$e->getMessage(), __FILE__, __LINE__);
                continue;
            }

            /* N.B. if Debian changes binary packaging (listed in Sources index) we'll not find out till a new advisory */
            $currentSubSourceHash = $this->computeHash($contents);
            if (! $this->isSubSourceDefContainsNewData($subSourceDef, $currentSubSourceHash)) {
                $this->updateSubSourceLastChecked($subSourceDef);
                continue;
            }

            $defs = $this->processAdvisories($contents, $subSourceDef->getId());
            if (! $defs)
                continue;

            $vulnerabilities = $this->parse_definitions($defs);
            if (! $vulnerabilities)
                continue;

            $this->_pakiti->getManager("VulnerabilitiesManager")->updateVulnerabilities($vulnerabilities);
            $this->updateLastSubSourceDefHash($subSourceDef, $currentSubSourceHash);
            $this->updateSubSourceLastChecked($subSourceDef);
        }
    }

    private function parse_definitions($defs)
    {
	# We have CVE definition in this format:
	# Array
	#(
	#    [subSourceDefId] => 5
	#    [definition_id] => oval:com.redhat.rhsa:def:20120006
	#    [severity] => Critical
	#    [title] => RHSA-2012:0006: java-1.4.2-ibm security update (Critical)
	#    [ref_url] => https://rhn.redhat.com/errata/RHSA-2012-0006.html
	#    [cves] => Array
	#        (
	#            [0] => CVE-2011-3389
	#            [1] => CVE-2011-3545
	#        )
	#
	#    [osGroup] => Array
	#        (
	#            [Red Hat Enterprise Linux 5] => Array
	#                (
	#                    [0] => Array
	#                        (
	#                            [name] => java-1.4.2-ibm-plugin
	#                            [version] => 0:1.4.2.13.11
	#                            [release] => 1jpp.1.el5
	#                            [operator] => <
	#                        )
	#                    [1] => Array
	#                        (
	#                             [name] => java-1.4.2-ibm-src
	#                             [version] => 0:1.4.2.13.11
	#                             [release] => 1jpp.1.el5
	#                             [operator] => <
	#                         )
	#
	#                )
	#
	#        )
	#
	#)

	$vulnerabilities = array();

	foreach ($defs as $def) {
	    $cveDef = new CveDef();
	    $cveDef->setDefinitionId($def['definition_id']);
	    $cveDef->setTitle($def['title']);
	    $cveDef->setRefUrl($def['ref_url']);
	    $cveDef->setVdsSubSourceDefId($def['subSourceDefId']);

	    if (empty($def['cves']))
		    continue;

	    if ($this->_pakiti->getManager('CveDefsManager')->storeCveDef($cveDef)) {
		foreach ($def['cves'] as $cveName) {
		    $cve = new Cve();
		    $cve->setName($cveName);
		    $this->_pakiti->getManager('CvesManager')->storeCve($cve);
		    $this->_pakiti->getManager('CveDefsManager')->assignCveToCveDef($cve->getId(), $cveDef->getId());
		}
	    }

	    # if osGroup not set, than it is unfixed in DSA
	    if (isset($def['osGroup'])) {
		foreach ($def['osGroup'] as $osGroupName => $defsPkg) {
		    foreach ($defsPkg as $defPkg) {
			$vuln = new Vulnerability();

			$vuln->setCveDefId($cveDef->getId());

			# OVAL from RH and DSA doesn't contain arch, so use all
			$archName = 'all';

			$arch = new Arch();
			$arch->setName($archName);
			$this->_pakiti->getManager("ArchsManager")->storeArch($arch);

			$osGroup = new OsGroup();
			$osGroup->setName($osGroupName);
			$this->_pakiti->getManager('OsGroupsManager')->storeOsGroup($osGroup);

			$vuln->setName($defPkg['name']);
			$vuln->setRelease($defPkg['release']);
			$vuln->setVersion($defPkg['version']);
			$vuln->setArchId($arch->getId());
			$vuln->setOsGroupId($osGroup->getId());
			$vuln->setOperator($defPkg['operator']);

			array_push($vulnerabilities, $vuln);
		    }
		}
	    }
	}

	return $vulnerabilities;
    }
}
