<?php

/**
 * @author Michal Prochazka
 */
final class Pakiti
{
    private $_dbManager = null;
    private $_hostsManager = null;
    private $_hostGroupsManager = null;
    private $_reportsManager = null;

    public function init()
    {
		$log_options = LOG_ODELAY;
		if (php_sapi_name() == "cli")
			$log_options = $log_options | LOG_PERROR;
        $ident = "Pakiti";
        if (Config::$PAKITI_NAME) {
            $ident = "$ident " . Config::$PAKITI_NAME;
        }
        openlog($ident, $log_options, LOG_LOCAL0);

        Utils::log(LOG_DEBUG, "Pakiti initialized", __FILE__, __LINE__);
    }

    public function checkVersion()
    {
        # Check if the Pakiti Config.php is in correct version
        $configVersion = isset(Config::$CONFIG_VERSION) ? Config::$CONFIG_VERSION : null;
        if ($configVersion != Constants::$CONFIG_VERSION) {
            $msg = "Pakiti version doesn't correspond with the Pakiti Config.php version!";
            print $msg;
            throw new Exception($msg);
        }

        # Check if the Pakiti DB is in correct version
        $dbVersion = $this->getManager("DbManager")->queryToSingleValue("select attrValue from PakitiAttributes where attrName='".AttributeNames::$DB_VERSION."'");
        if ($dbVersion != Constants::$DB_VERSION) {
            $msg = "Pakiti version doesn't correspond with the Pakiti DB version!";
            print $msg;
            throw new Exception($msg);
        }
    }

    /**
     * Get the manager by its name, e.g.'DbManager'. Manager is initialized if it was never used before.
     */
    public function getManager($name)
    {
        $propertyName = "_" . lcfirst($name);
        if (!isset($this->$propertyName))
        {
            $this->$propertyName = new $name($this);
        }
        return $this->$propertyName;
    }

	public function setManager($name, $manager)
	{
		$propertyName = "_" . lcfirst($name);
		if (isset($this->$propertyName)) {
			unset($this->$propertyName);
		}
		$this->$propertyName = $manager;
	}

    /**
     * Get the DAO of the requested className
     */
    public function getDao($className)
    {
        $className .= "Dao"; 
        return new $className($this->getManager("DbManager"));
    }
}
