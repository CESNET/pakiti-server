<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

# Load the constants
require_once(realpath(dirname(__FILE__)) . '/Constants.php');

# Load the configuration file
require_once(realpath(dirname(__FILE__)) . '/../../etc/Config.php');

# Load Pakiti, Constants and Utils class
require_once(realpath(dirname(__FILE__)) . '/Pakiti.php');
require_once(realpath(dirname(__FILE__)) . '/Utils.php');
require_once(realpath(dirname(__FILE__)) . '/Acl.php');

# Attribute name definitions
require_once(realpath(dirname(__FILE__)) . '/AttributesNames.php');

# Base class for the modules
require_once(realpath(dirname(__FILE__)) . '/DefaultModule.php');

# Enable autoload for the dao, model and manager classes
function __autoload($className) {
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
      $moduleName = strtolower(preg_replace('/^(.*)Module$/','\1',$className));
      if (file_exists(realpath(dirname(__FILE__)) . '/../modules/' . $moduleName . '/' . $className . '.php')) {
        include_once(realpath(dirname(__FILE__)) . '/../modules/' . $moduleName . '/' . $className . '.php');
      }
    } else {
      #  Models
      if (file_exists(realpath(dirname(__FILE__)) . '/../model/' . $className . '.php')) {
        include_once(realpath(dirname(__FILE__)) . '/../model/' . $className . '.php');
      }
    }
    Utils::log(LOG_DEBUG, "Class $className loaded", __FILE__, __LINE__);
}

# Create the Pakiti object
try {
  $pakiti = new Pakiti();
  $pakiti->init();
  
  $pakiti->checkDBVersion();
} catch (Exception $e) {
  syslog(LOG_ERR, $e->getMessage());
  exit;
}

?>
