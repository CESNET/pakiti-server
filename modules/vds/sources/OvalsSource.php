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

require(realpath(dirname(__FILE__)) . '/ovalSources/lib/IOvalSource.php');
require(realpath(dirname(__FILE__)) . '/ovalSources/lib/OvalSourceDef.php');
require(realpath(dirname(__FILE__)) . '/ovalSources/lib/OvalSourceDefDao.php');

/*
 * Interface for all source of OVALs (RedHat, SUSE, Debian, ...).
 * They are little bit different.
 */
class OvalsSource extends VdsSource implements ISource  {
  private static $NAME = "Ovals";
  private $_pakiti;
  private $_ovalSubSources;

  /*
   * Load all types of OVAL sources
   */
  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
    $this->_ovalSubSources = array();
    $this->setName(OvalsSource::$NAME);

    # Load all OVAL sources
    $this->loadSubSources();
  }

  /*
   * Get the name of this class.
   */
  public function getClassName() {
    return get_class();
  }

  /*
   * Ask all OVAL sources to provide the complete list of CVE definitions
   */
  public function synchronize() {
    Utils::log(LOG_DEBUG, "Synchronizing Ovals", __FILE__, __LINE__);
    foreach ($this->_ovalSubSources as &$ovalSubSource) {

      # Get URIs
      $sourcesDefs = $ovalSubSource->getSubSourceDefs($this);

      foreach ($sourceDefs as $sourceDef) {
        # Retreive the CVE definitions
        $defs = $ovalSubSource->retreiveDefinitions($sourceDef);

        # Store them into the DB
        # [0] -> [cves] -> list of cves
        #     -> [pkgs] -> [osVersion] -> list of [pkgName] -> [pkgVersion]
        foreach ($defs as $def) {
          
          # OvalSubSourceDefId
          
          # Store the CVEs
          # cve.name
          # cveDef.cveId cveDef.ovalSourceDefId
          
          # Store the OSes
          # OvalOs.ovalId, OvalOs.osId
          
          # Store the vulnerabilities
          # pkgId, version, release, archId, operator, vdsSourceId, vdsDefId
        }
      }
    }
  }

  /*
   * Checks if the particular OVAL source is registered in the DB (Oval table) and load it.
   */
  protected function loadSubSources() {
    Utils::log(LOG_DEBUG, "Loading OVAL sources", __FILE__, __LINE__);

    # List all files in the sources directory, each file represents submodule
    $dir = realpath(dirname(__FILE__)) . '/ovalSources/';
    if ($handle = opendir($dir)) {
      while (false !== ($file = readdir($handle))) {
        # Load only files and ommit the OvalSourceInterface
        if (is_file($dir.$file)) {
          require($dir.$file);

          # Get the filename and extension, filename represent the class name
          $className = preg_replace('/.php/', '', $file);

          eval("\$ovalSubSource = new $className();");

          # Check if the module is already registered
          if (($id = $this->_pakiti->getManager("DbManager")->queryToSingleValue(
    				"select id from Oval where type='".mysql_real_escape_string($ovalSubSource->getType()).
    				"' and name='".mysql_real_escape_string($ovalSubSource->getName())."'")) == null) {
          # Module is not registered, so store the name and type into the DB

          # Start transaction
          $this->_pakiti->getManager("DbManager")->begin();

          $this->_pakiti->getManager("DbManager")->query(
      				"insert into Oval set type='".$ovalSubSource->getType()."', name='".$ovalSubSource->getName()."'");

          $id = $this->_pakiti->getManager("DbManager")->getLastInsertedId();

          # Commit transaction
          $this->_pakiti->getManager("DbManager")->commit();
    				}
    				# Set the submodule ID
    				$ovalSubSource->setId($id);

    				# Finally add the OVAL submodule
    				array_push($this->_ovalSubSources, $ovalSubSource);
        }
      }
    }
  }

  /*
   * Get OVAL source by Id
   */
  public function getSubSourceById($id) {
    Utils::log(LOG_DEBUG, "Getting Oval source by ID [id=$id]", __FILE__, __LINE__);

    foreach ($this->_ovalSubSources as &$subSource) {
      if ($subSource->getId() == $id) {
        return $subSource;
      }
    }
  }
}