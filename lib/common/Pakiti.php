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

/**
 * @author Michal Prochazka
 */
final class Pakiti {
  private $_dbManager = null;
  private $_hostsManager = null;
  private $_tagsManager = null;
  private $_hostGroupsManager = null;
  private $_reportsManager = null;
  
  public function init() {
    # Setup logging facility
    openlog("Pakiti", LOG_PERROR | LOG_ODELAY, LOG_LOCAL0);

    Utils::log(LOG_DEBUG, "Pakiti initialized", __FILE__, __LINE__);
  }
  
  public function checkDBVersion() {
    # Check if the Pakiti DB is in correct version
    $dbVersion = $this->getManager("DbManager")->queryToSingleValue("select attrValue from PakitiAttributes where attrName='".AttributeNames::$PAKITI_VERSION."'");

    if ($dbVersion != Constants::$PAKITI_VERSION) {
      throw new Exception("Pakiti version doesn't correspond with the Pakiti database version!");
    }
  }
  
  /*
   * Get the manager by its name, e.g.'DbManager'. Manager is initialized if it was never used before.
   */
  public function getManager($name) {
    # Get the first charactar and make it lowercase
    $lcFirstChar = strtolower(substr($name, 0, 1));
    $propertyName = $lcFirstChar . substr($name, 1);
    eval("\$manager =& \$this->_$propertyName;");
    
    if ($manager == null) {
      eval("\$manager = new $name(\$this);");
    }
    return $manager;
  }
  
  /*
   * Get the DAO of the requested className
   */
  public function getDao($className) {
   eval("\$dao = new ${className}Dao(\$this->getManager(\"DbManager\"));");
   return $dao;
  }
}
?>
