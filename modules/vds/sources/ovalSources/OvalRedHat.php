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

require(realpath(dirname(__FILE__)) . '/lib/OvalClass.php');

class OvalRedHat extends OvalClass implements ISubSource {
  private static $NAME = "RedHat OVAL";
  private static $TYPE = "RedHat";
  private $_id;
  private $_xpath;

  public function retreiveDefinitions(OvalSourceDef &$ovalSourceDef) {
    if ($ovalSourceDef == null) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("ovalSourceDef is null");
    }
    Utils::log(LOG_DEBUG, "Retreiving definitions from the ".OvalRedHat::getName()." OVAL", __FILE__, __LINE__);

    # Loading the defined file
    $oval = new DOMDocument();
    libxml_set_streams_context(get_context());
    $oval->load($ovalSourceDef->getUri());

    if ($oval === FALSE) {
      Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
      throw new Exception("Cannot load OVAL [source URI=".$ovalSourceDef->getUri()."]");
    }

    # Get the XPath
    $this->_xpath = new DOMXPath($oval);

    $this->_xpath->registerNamespace("oval", "http://oval.mitre.org/XMLSchema/oval-definitions-5");
    $this->_xpath->registerNamespace("oval-def", "http://oval.mitre.org/XMLSchema/oval-definitions-5");
    $this->_xpath->registerNamespace("unix-def", "http://oval.mitre.org/XMLSchema/oval-definitions-5#unix");
    $this->_xpath->registerNamespace("red-def", "http://oval.mitre.org/XMLSchema/oval-definitions-5#linux");
    $this->_xpath->registerNamespace("xsi", "http://www.w3.org/2001/XMLSchema-instance");

    $xDefinitions = $this->_xpath->query("oval-def:definitions//oval-def:definition");
     
    $defs = array();
    # Go through all definitions
    foreach ($xDefinitions as $xDefinition) {
      # Get the list of CVEs associated with the definition
      $xCves = $this->_xpath->query("oval-def:metadata/oval-def:advisory/oval-def:cve", $xDefinition);
      $cves = array();
      foreach ($xCves as $xCve) {
        array_push($cves, $xCve->nodeValue);
      }

      # Go through the criterias and extracts OS version and list of affected packages
      $xFirstCriteria = $this->_xpath->query("./oval-def:criteria[1]", $xDefinition);
      if (getOperator($xFirstCriteria) == "and") {
        # There is only one affected OS version
        $pkgs = processPkgsForOs($xFirstCriteria->item(0));
      } elseif (getOperator($xFirstCriteria) == "or") {
        # There is more than one affected OS version, so process them separatelly
        $xAnds = $this->_xpath->query("./oval-def:criteria[@operator='AND']", $xFirstCriteria->item(0));
        foreach ($xAnds as $xAnd) {
          $pkgs = array_merge_recursive($pkgs, processPkgsForOs($xAnd));
        }
      }
      	
      # Merge returned array with previously
      array_push($defs, array("cves" => $cves, "pkgs" => $pkgs));
    }
    
    return $defs;
  }

  public function getId() {
    return $this->_id;
  }

  public function setId($val) {
    $this->_id = $val;
  }

  public function getName() {
    return OvalRedhat::$NAME;
  }

  public function getType() {
    return OvalRedhat::$TYPE;
  }

  /*
   * Get the operator from the XML element: AND/OR
   */
  protected function getOperator(&$xElem) {
    if ($xElem->item(0)->getAttribute('operator') == "AND") {
      return "and";
    } elseif ($xElem->item(0)->getAttribute('operator') == "OR") {
      return "or";
    } else {
      return NULL;
    }
  }

  /*
   * Extracts the RedHat release version (3, 4, 5, 6, ...)
   */
  protected function getOsVersion(&$xElem) {
    $rawOsVersion = $xElem->getAttribute('comment');

    # Parse the OS version from the string 'Red Hat Enterprise Linux 5 is installed'
    if (preg_match("/\.*Linux ([0-9]*) is installed$/", $rawOsVersion, $matches) == 1) {
      return $matches[1];
    } else {
      return NULL;
    }
  }

  /*
   * Process list of the packages for each OS version.
   * Returns an array [osVersion] -> [pkgName => pkgVersion]*
   */
  protected function processPkgsForOs(&$xElem) {
    # Get the OS version from the childElement
    $childElement = $this->_xpath->query("./oval-def:criterion", $xElem);
    $osVersion = getOsVersion($childElement->item(0));

    # Os cannot be detected
    if ($osVersion == NULL) {
      return null;
    }

    $pkgsForOs = array();
    $pkgsForOs[$osVersion] = array();

    $xPkgs = $this->_xpath->query(".//oval-def:criterion[@comment]", $xElem);
    foreach ($xPkgs as $pkg) {
      $pkgTest = $pkg->getAttribute('comment');
      if (preg_match("/^(.*) is earlier than (.*)$/", $pkgTest, $matches) == 1) {
        array_push($pkgsForOs[$osVersion], array($matches[1] =>  $matches[2]));
      }
    }

    return $pkgsForOs;
  }
}