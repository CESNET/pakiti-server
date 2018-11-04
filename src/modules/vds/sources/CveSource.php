<?php

require_once(realpath(dirname(__FILE__)) . '/../lib/Source.php');

/**
 * @author Michal Prochazka
 */
class CveSource extends Source implements ISource
{
    private static $NAME = "Cve";
    private $_pakiti;

    /**
     * Load all types of CVE sources
     */
    public function __construct(Pakiti $pakiti)
    {
        parent::__construct($pakiti);

        $this->_pakiti = $pakiti;
        $this->setName(CveSource::$NAME);
    }

    /**
     * Initialization routine
     */
    public function init()
    {
        parent::init();
        # Get module ID from the DB
    }

    /**
     * Get the name of this class.
     */
    public function getClassName()
    {
        return get_class();
    }

    /**
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

            # Store them into the list of Vulnerabilities
            if ($defs) {
                # Reformat data into
                foreach ($defs as $def) {
                    #CVEs definition
                    $cveDef = new CveDef();
                    $cveDef->setDefinitionId($def['definition_id']);
                    $cveDef->setTitle($def['title']);
                    $cveDef->setRefUrl($def['ref_url']);
                    $cveDef->setVdsSubSourceDefId($def['subSourceDefId']);

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
            }
        }
        return $vulnerabilities;
    }
}
