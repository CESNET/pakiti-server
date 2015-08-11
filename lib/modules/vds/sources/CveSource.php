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

require_once(realpath(dirname(__FILE__)) . '/../lib/Source.php');

/*
 * Interface for all source of OVALs (RedHat, SUSE, Debian, ...).
 * They are little bit different.
 */

class CveSource extends Source implements ISource
{
    private static $NAME = "Cve";
    private $_pakiti;

    /*
     * Load all types of CVE sources
     */
    public function __construct(Pakiti &$pakiti)
    {
        parent::__construct($pakiti);

        $this->_pakiti =& $pakiti;
        $this->setName(CveSource::$NAME);
    }

    /*
     * Initialization routine
     */
    public function init()
    {
        parent::init();

        # Get module ID from the DB
    }


    /*
     * Get the name of this class.
     */
    public function getClassName() {
        return get_class();
    }


    /*
     * Ask all CVE sources to provide the complete list of CVE definitions
     */
    public function retrieveVulnerabilities()
    {
        Utils::log(LOG_DEBUG, "Synchronizing CVE", __FILE__, __LINE__);
        $vulnerabilities = array();

        foreach ($this->getSubSources() as $subSource) {
            $defs = $subSource->retrieveDefinitions();

            # We have CVE definition in this format:
            # Array
            #(
            #	   [subSourceDefId] => 5
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
            #				              [operator] => <
            #                        )
            #                    [1] => Array
            #                        (
            #                             [name] => java-1.4.2-ibm-src
            #                             [version] => 0:1.4.2.13.11
            #                             [release] => 1jpp.1.el5
            #				                [operator] => <
            #                         )
            #
            #                )
            #
            #        )
            #
            #)

            # Store them into the list of Vulnerabilities
            print_r($defs);
            if ($defs) {
                # Reformat data into
                foreach ($defs as $def) {
                    #CVEs definition
                    $cveDef = new CveDef();
                    $cveDef->setDefinitionId($def['definition_id']);
                    $cveDef->setTitle($def['title']);
                    $cveDef->setRefUrl($def['ref_url']);
                    $cveDef->setVdsSubSourceDefId($def['subSourceDefId']);

                    $cveDefId = $this->_pakiti->getDao("CveDef")->getCveDefId($cveDef);

                    if ($cveDefId == null){
                        # CVEs
                        $cves = array();
                        foreach ($def['cves'] as $cveName) {
                            $cve = new Cve();
                            $cve->setName($cveName);
                            array_push($cves, $cve);
                        }
                        $cveDef->setCves($cves);

                        $this->_pakiti->getManager('CveDefsManager')->createCveDef($cveDef);

                    }else{
                        $cveDef->setId($cveDefId);
                    }

                    foreach ($def['osGroup'] as $osGroupName => $defsPkg) {
                        foreach ($defsPkg as $defPkg) {

                            $vuln = new Vulnerability();

                            $vuln->setCveDefId($cveDef->getId());

                            # OVAL from RH and DSA doesn't contain arch, so use all
                            $archName = 'all';
                            $arch = $this->_pakiti->getManager("HostsManager")->getArch($archName);

                            if ($arch == null) {
                                # Arch is not defined in the DB, so created it  e);
                                $arch = $this->_pakiti->getManager('HostsManager')->createArch($archName);
                            }

                            $vuln->setName($defPkg['name']);
                            $vuln->setRelease($defPkg['release']);
                            $vuln->setVersion($defPkg['version']);
                            $vuln->setArch($arch->getName());

                            # Get osGroup Id
                            $osGroup = $this->_pakiti->getManager("OsGroupsManager")->getOsGroupByName($osGroupName);
                            if ($osGroup == null) {
                                $osGroup = new OsGroup();
                                $osGroup->setName($osGroupName);
                                # osGropu is not defined in the DB, so created it
                                $osGroup = $this->_pakiti->getManager('OsGroupsManager')->createOsGroup($osGroupName);
                            }
                            $vuln->setOsGroupId($osGroup->getId());

                            $vuln->setOperator($defPkg['operator']);

                            array_push($vulnerabilities, $vuln);
                        }
                    }
                }
            }
        }
        return $vulnerabilities;
    }
}
