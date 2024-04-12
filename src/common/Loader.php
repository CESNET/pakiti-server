<?php

# Load the constants
require_once(realpath(dirname(__FILE__)) . '/Constants.php');

# Default config file
$config_file = Constants::$PAKITI_CONFIG_FILE;

# If cli is used
if (php_sapi_name() == "cli") {
    # try to get config file path from option --config
    for ($i = 0; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (strncmp($arg, "--config=", 9) === 0) {
            $config_file = substr($arg, 9);
            break;
        }
        if ($arg == "--config") {
            if (! array_key_exists($i+1, $argv)) {
                fwrite(STDERR, "Configuration file must be given with the --config option\n");
                exit(1);
            }
            $config_file = $argv[$i+1];
            break;
        }
    }
# else if apache env variable exists
} elseif (array_key_exists(Constants::$PAKITI_CONFIG_ENV, $_SERVER)) {
    # set pakiti config file path from env variable
    $config_file = $_SERVER[Constants::$PAKITI_CONFIG_ENV];
}

require_once(realpath(dirname(__FILE__)) . '/DefaultConfig.php');
# Load the configuration file
if (file_exists($config_file))
    require_once($config_file);
else
    require_once(realpath(dirname(__FILE__)) . '/Config.php');

# Load Pakiti, Constants and Utils class
require_once(realpath(dirname(__FILE__)) . '/Pakiti.php');
require_once(realpath(dirname(__FILE__)) . '/Utils.php');
require_once(realpath(dirname(__FILE__)) . '/Acl.php');

# Attribute name definitions
require_once(realpath(dirname(__FILE__)) . '/AttributesNames.php');

# Base class for the modules
require_once(realpath(dirname(__FILE__)) . '/DefaultModule.php');

# Enable autoload for the dao, model and manager classes
spl_autoload_register(function ($className)
{
    if (preg_match('/.*Dao$/', $className) > 0) {
        # Dao class
        if (file_exists(realpath(dirname(__FILE__)) . '/../dao/' . $className . '.php')) {
            include_once(realpath(dirname(__FILE__)) . '/../dao/' . $className . '.php');
        }
    } elseif (preg_match('/.*Manager$/', $className) > 0) {
        # Managers interfaces
        if (file_exists(realpath(dirname(__FILE__)) . '/../managers/' . $className . '.php')) {
            include_once(realpath(dirname(__FILE__)) . '/../managers/' . $className . '.php');
        }
    } elseif (preg_match('/.*Module$/', $className) > 0) {
        # Get the module name
        $moduleName = strtolower(preg_replace('/^(.*)Module$/', '\1', $className));
        if (file_exists(realpath(dirname(__FILE__)) . '/../../modules/' . $moduleName . '/' . $className . '.php')) {
            include_once(realpath(dirname(__FILE__)) . '/../../modules/' . $moduleName . '/' . $className . '.php');
        }
    } else {
        #  Models
        if (file_exists(realpath(dirname(__FILE__)) . '/../model/' . $className . '.php')) {
            include_once(realpath(dirname(__FILE__)) . '/../model/' . $className . '.php');
        }
    }
    Utils::log(LOG_DEBUG, "Class $className loaded", __FILE__, __LINE__);
});

# Create the Pakiti object
try {
    $pakiti = new Pakiti();
    $pakiti->init();

    $pakiti->checkVersion();
} catch (Exception $e) {
    syslog(LOG_ERR, $e->getMessage());
    exit;
}
