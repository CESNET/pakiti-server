<?php

require_once(realpath(dirname(__FILE__)) . '/../../include/ISubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../lib/SubSource.php');

/**
 * @author Michal Prochazka
 */
class OvalRedHat extends SubSource implements ISubSource
{
    protected static $NAME = "RedHat OVAL";
    protected static $TYPE = "RedHat";
    private $_xpath;

    public function processAdvisories($contents, $subSourceDef_id)
    {
        $defs = array();
        $oval = new DOMDocument();

        $ret = $oval->loadXML($contents, LIBXML_PARSEHUGE);
        if ($ret === FALSE) {
                Utils::log(LOG_ERR, "Cannot load OVAL for $subSourceDef_id", __FILE__, __LINE__);
                throw new Exception("Cannot load OVAL for $subSourceDef_id");
        }

        $this->_xpath = new DOMXPath($oval);

        $this->_xpath->registerNamespace("def", "http://oval.mitre.org/XMLSchema/oval-definitions-5");

        $xDefinitions = $this->_xpath->query("/def:oval_definitions/def:definitions/def:definition");

        # Go through all definitions
        foreach ($xDefinitions as $xDefinition) {
                $def = array();

                $def['subSourceDefId'] = $subSourceDef_id;

                $def['definition_id'] = $xDefinition->attributes->getNamedItem('id')->nodeValue;

                # Don't consider marginal versions, like 'Supplementary for RHEL' and the like, which easily might distort results
                $platform_query = 'def:metadata/def:affected/def:platform';
                $platforms = $this->_xpath->query($platform_query, $xDefinition);
                $supported = false;
                foreach ($platforms as $platform) {
                        if (preg_match('/^Red Hat Enterprise Linux [0-9\.]+$/', $platform->nodeValue)) {
                                $supported = true;
                                break;
                        }
                }
                if (!$supported) {
                        continue;
                }

                $el_severity = $xDefinition->getElementsByTagName('severity')->item(0);
                if (!empty($el_severity)) {
                        $def['severity'] = $el_severity->nodeValue;
                } else {
                        $def['severity'] = "n/a";
                }

                $def['title'] = rtrim($xDefinition->getElementsByTagName('title')->item(0)->nodeValue);

                /* This is a hack to recognize the related architecture, supposing that only ARM should be disregarded */
                if (strpos($def['title'], 'aarch64') !== false) {
                        continue;
                }

                $def['ref_url'] = $xDefinition->getElementsByTagName('reference')->item(0)->getAttribute('ref_url');

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
                        $package = array();
                        $this->processCriterias($this->_xpath, $root_criteria, $def, $os, $package);
                }
                array_push($defs, $def);
        }

        return $defs;
    }

    /**
     * Get the operator from the XML element: AND/OR
     */
    protected function getOperator($xElem)
    {
        if ($xElem->item(0)->getAttribute('operator') == "AND") {
            return "and";
        } elseif ($xElem->item(0)->getAttribute('operator') == "OR") {
            return "or";
        } else {
            return null;
        }
    }

    /**
     * Extracts the RedHat release version (3, 4, 5, 6, ...)
     */
    protected function getOsVersion($xElem)
    {
        $rawOsVersion = $xElem->getAttribute('comment');
        # Parse the OS version from the string 'Red Hat Enterprise Linux 5 is installed'
        if (preg_match("/\.*Linux ([0-9]*) is installed$/", $rawOsVersion, $matches) == 1) {
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * Process list of the packages for each OS version.
     * Returns an array [osVersion] -> [pkgName => pkgVersion]*
     */
    protected function processPkgsForOs($xElem)
    {
        # Get the OS version from the childElement
        $childElement = $this->_xpath->query("./def:criterion", $xElem);
        $osVersion = getOsVersion($childElement->item(0));

        # Os cannot be detected
        if ($osVersion == null) {
            return null;
        }

        $pkgsForOs = array();
        $pkgsForOs[$osVersion] = array();

        $xPkgs = $this->_xpath->query(".//def:criterion[@comment]", $xElem);
        foreach ($xPkgs as $pkg) {
            $pkgTest = $pkg->getAttribute('comment');
            if (preg_match("/^(.*) is earlier than (.*)$/", $pkgTest, $matches) == 1) {
                array_push($pkgsForOs[$osVersion], array($matches[1] =>  $matches[2]));
            }
        }

        return $pkgsForOs;
    }

    # Process each criteria, this function must be duplicated because PHP removed call by reference. processCriteriasWithReference requires os and package to be passed as a reference
    protected function processCriteriasWithReference($xpath, $criteriaElement, &$res, &$os, &$package)
    {
        $operator = $criteriaElement->attributes->getNamedItem('operator')->nodeValue;

        if (!array_key_exists('osGroup', $res)) {
            $res['osGroup'] = array();
        }

        # If we have $os and $package filled, store id
        if ($os != null && !empty($package)) {
            //print "Storing $os, $package\n";
            if (!array_key_exists($os, $res['osGroup'])) {
                $res['osGroup'][$os] = array();
            }
            array_push($res['osGroup'][$os], $package);
            # Empty package variable
            $package = null;
        }

        # Check if the child nodes are criterion or criteria
        $criterias_query = 'def:criteria';
        $criterions_query = 'def:criterion';

        $criterias = $xpath->query($criterias_query, $criteriaElement);
        $criterions = $xpath->query($criterions_query, $criteriaElement);

        if ($criterions->length > 0) {
            # We have found criterions, so parse them. Try to find redhat version and packages names/versions
            foreach ($criterions as $criterion) {
                $comment = $criterion->attributes->getNamedItem('comment')->nodeValue;
                if (strpos($comment, "is installed")) {
                    preg_match("/^Red Hat Enterprise Linux.* (\d+)[ ]*(Client|Server|Workstation|ComputeNode|)[ ]*is installed$/", $comment, $redhat_release);
                    $os = 'Red Hat Enterprise Linux ' . $redhat_release[1];
                    //print "Got OS: $os\n";
                } elseif (strpos($comment, "is earlier than")) {
                    preg_match("/^([^ ]+) is earlier than ([^-]*)-(.*)$/", $comment, $results);
                    $package = array();
                    $package['name'] = $results[1];
                    $package['version'] = $results[2];
                    $package['release'] = $results[3];
                    $package['operator'] = '<';
                    //print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
                }
            }

            # Criterions can contain both os and package under one criteria
            if ($os != null && !empty($package)) {
                //print "Storing $os, $package\n";
                if (!array_key_exists($os, $res['osGroup'])) {
                    $res['osGroup'][$os] = array();
                }
                array_push($res['osGroup'][$os], $package);
                # Empty package varialble
                $package = null;
            }
        }

        if ($criterias->length > 0) {
            # We have foung criterias, so pass them for further processing
            foreach ($criterias as $criteria) {
                if ($operator == "AND") {
                    $this->processCriteriasWithReference($xpath, $criteria, $res, $os, $package);
                } else {
                    $this->processCriterias($xpath, $criteria, $res, $os, $package);
                }
            }
        }
    }

    protected function processCriterias($xpath, $criteriaElement, &$res, $os, $package)
    {
        $operator = $criteriaElement->attributes->getNamedItem('operator')->nodeValue;

        if (!array_key_exists('osGroup', $res)) {
            $res['osGroup'] = array();
        }

        # If we have $os and $package filled, store id
        if ($os != null && !empty($package)) {
            //print "Storing $os, $package\n";
            if (!array_key_exists($os, $res['osGroup'])) {
                $res['osGroup'][$os] = array();
            }
            array_push($res['osGroup'][$os], $package);
            # Empty package variable
            $package = null;
        }

        # Check if the child nodes are criterion or criteria
        $criterias_query = 'def:criteria';
        $criterions_query = 'def:criterion';

        $criterias = $xpath->query($criterias_query, $criteriaElement);
        $criterions = $xpath->query($criterions_query, $criteriaElement);
        if ($criterions->length > 0) {
            # We have found criterions, so parse them. Try to find redhat version and packages names/versions
            foreach ($criterions as $criterion) {
                $comment = $criterion->attributes->getNamedItem('comment')->nodeValue;
                if (strpos($comment, "is installed")) {
                    preg_match("/^Red Hat Enterprise Linux.* (\d+)[ ]*(Client|Server|Workstation|ComputeNode|)[ ]*is installed$/", $comment, $redhat_release);
                    $os = 'Red Hat Enterprise Linux ' . $redhat_release[1];
                    //print "Got OS: $os\n";
                } elseif (strpos($comment, "is earlier than")) {
                    preg_match("/^([^ ]+) is earlier than ([^-]*)-(.*)$/", $comment, $results);
                    $package = array();
                    $package['name'] = $results[1];
                    $package['version'] = $results[2];
                    $package['release'] = $results[3];
                    $package['operator'] = '<';
                    //print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
                }
            }

            # Criterions can contain both os and package under one criteria
            if ($os != null && !empty($package)) {
                //print "Storing $os, $package\n";
                if (!array_key_exists($os, $res['osGroup'])) {
                    $res['osGroup'][$os] = array();
                }
                array_push($res['osGroup'][$os], $package);
                # Empty package varialble
                $package = null;
            }
        }

        if ($criterias->length > 0) {
            # We have foung criterias, so pass them for further processing
            foreach ($criterias as $criteria) {
                if ($operator == "AND") {
                    $this->processCriteriasWithReference($xpath, $criteria, $res, $os, $package);
                } else {
                    $this->processCriterias($xpath, $criteria, $res, $os, $package);
                }
            }
        }
    }
}
