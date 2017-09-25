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

require_once(realpath(dirname(__FILE__)) . '/../../include/ISubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../lib/SubSource.php');

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class OvalSUSE extends SubSource implements ISubSource
{
    private static $NAME = "SUSE OVAL";
    private static $TYPE = "SUSE";

    public function retrieveDefinitions()
    {
        Utils::log(LOG_DEBUG, "Retreiving definitions from the ".OvalSUSE::getName()." OVAL", __FILE__, __LINE__);

        $defs = array();
        foreach ($this->getSubSourceDefs() as $subSourceDef) {

            # Loading the defined file
            $oval = new DOMDocument();
            libxml_set_streams_context(Utils::getStreamContext());
            $oval->load($subSourceDef->getUri());

            if ($oval === false) {
                Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
                throw new Exception("Cannot load OVAL [source URI=".$subSourceDef->getUri()."]");
            }

            $currentSubSourceHash = $this->computeHash($oval->saveXML());
            if (!$this->isSubSourceDefContainsNewData($subSourceDef, $currentSubSourceHash)) {
                continue;
            }

            # Get the XPath
            $this->_xpath = new DOMXPath($oval);

            $this->_xpath->registerNamespace("def", "http://oval.mitre.org/XMLSchema/oval-definitions-5");

            $xDefinitions = $this->_xpath->query("/def:oval_definitions/def:definitions/def:definition");

            # Go through all definitions
            foreach ($xDefinitions as $xDefinition) {
                $def = array();

                $def['subSourceDefId'] = $subSourceDef->getId();

                $def['definition_id'] = $xDefinition->attributes->getNamedItem('id')->nodeValue;

                $el_severity = $xDefinition->getElementsByTagName('severity')->item(0);
                if (!empty($el_severity)) {
                    $def['severity'] = $el_severity->nodeValue;
                } else {
                    $def['severity'] = "n/a";
                }

                $def['title'] = rtrim($xDefinition->getElementsByTagName('title')->item(0)->nodeValue);
                $def['ref_url'] = "";

                # Get associated CVEs
                $cve_query = 'def:metadata/def:advisory/def:cve';
                $cves = $this->_xpath->query($cve_query, $xDefinition);

                $def['cves'] = array();
                $def['os'] = array();

                foreach ($cves as $cve) {
                    array_push($def['cves'], $cve->nodeValue);
                }

                # Processing criteria
                $root_criterias_query = 'def:criteria';
                $root_criterias = $this->_xpath->query($root_criterias_query, $xDefinition);

                foreach ($root_criterias as $root_criteria) {
                    $os = null;
                    $packages = array();
                    $this->processCriterias($this->_xpath, $root_criteria, $def, $os, $packages);
                }
                array_push($defs, $def);
            }
            $this->updateSubSourceLastChecked($subSourceDef);
            $this->updateLastSubSourceDefHash($subSourceDef, $currentSubSourceHash);
        }
        return $defs;
    }

    public function getClassName()
    {
        return get_class();
    }
   
    public function getName()
    {
        return OvalSUSE::$NAME;
    }
  
    public function getType()
    {
        return OvalSUSE::$TYPE;
    }

    # Process each criteria, this function must be duplicated because PHP removed call by reference. processCriteriasWithReference requires os and package to be passed as a reference
    protected function processCriteriasWithReference(&$xpath, $criteriaElement, &$res, &$os, &$packages)
    {
        $operator = $criteriaElement->attributes->getNamedItem('operator')->nodeValue;

        if (!array_key_exists('osGroup', $res)) {
            $res['osGroup'] = array();
        }

        # If we have $os and $package filled, store id
        if ($os != null && !empty($packages)) {
            //print "Storing $os, $package\n";
            if (!array_key_exists($os, $res['osGroup'])) {
                $res['osGroup'][$os] = array();
            }
            foreach ($packages as $item) {
                array_push($res['osGroup'][$os], $item);
            }
            # Empty package varialble
            $packages = array();
        }

        # Check if the child nodes are criterion or criteria
        $criterias_query = 'def:criteria';
        $criterions_query = 'def:criterion';

        $criterias = $xpath->query($criterias_query, $criteriaElement);
        $criterions = $xpath->query($criterions_query, $criteriaElement);

        if ($criterions->length > 0) {
            # We have found criterions, so parse them. Try to find suse version and packages names/versions
            foreach ($criterions as $criterion) {
                $comment = $criterion->attributes->getNamedItem('comment')->nodeValue;
                //print "Comment: $comment\n";
                if (strpos($comment, "SUSE") === 0 || strpos($comment, "openSUSE") === 0) {
                    preg_match("/^(.*) is installed$/", $comment, $suse_release);
                    $os = $suse_release[1];
                    //print "Got OS: $os\n";
                } elseif (strpos($comment, "is installed")) {
                    preg_match("/^([^ ]+)-([^-]+)-([^-]+) is installed$/", $comment, $results);
                    $package = array();
                    $package['name'] = $results[1];
                    $package['version'] = $results[2];
                    $package['release'] = $results[3];
                    $package['operator'] = '<';
                    array_push($packages, $package);
                    //print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
                }
            }

            # Criterions can contain both os and package under one criteria
            if ($os != null && !empty($packages)) {
                //print "Storing $os, $package\n";
                if (!array_key_exists($os, $res['osGroup'])) {
                    $res['osGroup'][$os] = array();
                }
                foreach ($packages as $item) {
                    array_push($res['osGroup'][$os], $item);
                }
                # Empty package varialble
                $packages = array();
            }
        }

        if ($criterias->length > 0) {
            # We have foung criterias, so pass them for further processing
            foreach ($criterias as $criteria) {
                if ($operator == "AND") {
                    $this->processCriteriasWithReference($xpath, $criteria, $res, $os, $packages);
                } else {
                    $this->processCriterias($xpath, $criteria, $res, $os, $packages);
                }
            }
        }
    }

    protected function processCriterias(&$xpath, $criteriaElement, &$res, $os, $packages)
    {
        $operator = $criteriaElement->attributes->getNamedItem('operator')->nodeValue;

        if (!array_key_exists('osGroup', $res)) {
            $res['osGroup'] = array();
        }

        # If we have $os and $package filled, store id
        if ($os != null && !empty($packages)) {
            //print "Storing $os, $package\n";
            if (!array_key_exists($os, $res['osGroup'])) {
                $res['osGroup'][$os] = array();
            }
            foreach ($packages as $item) {
                array_push($res['osGroup'][$os], $item);
            }
            # Empty package varialble
            $packages = array();
        }

        # Check if the child nodes are criterion or criteria
        $criterias_query = 'def:criteria';
        $criterions_query = 'def:criterion';

        $criterias = $xpath->query($criterias_query, $criteriaElement);
        $criterions = $xpath->query($criterions_query, $criteriaElement);

        if ($criterions->length > 0) {
            # We have found criterions, so parse them. Try to find suse version and packages names/versions
            foreach ($criterions as $criterion) {
                $comment = $criterion->attributes->getNamedItem('comment')->nodeValue;
                //print "Comment: $comment\n";
                if (strpos($comment, "SUSE") === 0 || strpos($comment, "openSUSE") === 0) {
                    preg_match("/^(.*) is installed$/", $comment, $suse_release);
                    $os = $suse_release[1];
                    //print "Got OS: $os\n";
                } elseif (strpos($comment, "is installed")) {
                    preg_match("/^([^ ]+)-([^-]+)-([^-]+) is installed$/", $comment, $results);
                    $package = array();
                    $package['name'] = $results[1];
                    $package['version'] = $results[2];
                    $package['release'] = $results[3];
                    $package['operator'] = '<';
                    array_push($packages, $package);
                    //print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
                }
            }

            # Criterions can contain both os and package under one criteria
            if ($os != null && !empty($packages)) {
                //print "Storing $os, $package\n";
                if (!array_key_exists($os, $res['osGroup'])) {
                    $res['osGroup'][$os] = array();
                }
                foreach ($packages as $item) {
                    array_push($res['osGroup'][$os], $item);
                }
                # Empty package varialble
                $packages = array();
            }
        }

        if ($criterias->length > 0) {
            # We have foung criterias, so pass them for further processing
            foreach ($criterias as $criteria) {
                if ($operator == "AND") {
                    $this->processCriteriasWithReference($xpath, $criteria, $res, $os, $packages);
                } else {
                    $this->processCriterias($xpath, $criteria, $res, $os, $packages);
                }
            }
        }
    }
}
