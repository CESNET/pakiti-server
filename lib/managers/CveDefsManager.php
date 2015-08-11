<?php

/**
 * User: Vadym Yanovskyy
 * Date: 7/20/15
 * Time: 2:59 PM
 */
class CveDefsManager extends DefaultManager
{
    private $_pakiti;

    public function __construct(Pakiti &$pakiti) {
        $this->_pakiti =& $pakiti;
    }

    public function getPakiti() {
        return $this->_pakiti;
    }

    public function createCveDef(CveDef &$cveDef){
        if ($cveDef == null) {
            Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
            throw new Exception("CveDef object is not valid");
        }

        $this->getPakiti()->getDao("CveDef")->create($cveDef);
        foreach ($cveDef->getCves() as $cve){
            if ($this->getPakiti()->getDao("Cve")->getCve($cve->getName(), $cveDef->getId()) == null) {
                $cve->setCveDefId($cveDef->getId());
                $this->getPakiti()->getDao("Cve")->create($cve);
            }
        }
    }

    public function assignPkgToCveDef($pkgId, $cveDefId, $osGroupId){
        $this->getPakiti()->getManager("DbManager")->query("insert ignore into PkgCveDef set pkgId={$pkgId},
        cveDefId={$cveDefId}, osGroupId={$osGroupId}");
    }

    public function getCveByNameAndCveDefId($name, $cveDefId)
    {
        Utils::log(LOG_DEBUG, "Getting CVE its name [name=$name] and cveDefId [cveDefId=$cveDefId]", __FILE__, __LINE__);
        $cve = $this->getPakiti()->getDao("Cve")->getCve();
        if (is_object($cve)) {
            $cve->setTag($this->getPakiti()->getManager("TagsManager")->getCveTags($cve));
        }
        return $cve;
    }

    public function getCvesByCveDef(CveDef &$cveDef)
    {
        $cves = $this->getPakiti()->getDao("Cve")->getCvesByCveDef($cveDef);
        if (is_array($cves)) {
            foreach ($cves as $cve) {
                $cve->setTag($this->getPakiti()->getManager("TagsManager")->getCveTags($cve));
            }
        }
        return $cves;
    }


    /**
     * Return count of Cves for a specific host
     * @param Host $host
     * @return int
     */
    public function getCvesCount(Host $host){
        $cvesCount = 0;
        $pkgsCves = $this->getCvesForHost($host);
        foreach ($pkgsCves as $pkgCves) {
            $cvesCount += count($pkgCves);

        }
        return $cvesCount;
    }

    /**
     * Return Cves that have been associated with some Tags without duplicities.
     * @return array
     */
    public function getCvesWithTags()
    {
        $cves = $this->getAllCves();
        $cvesWithTags = array();
        foreach ($cves as $cve) {
            if (!empty($cve->getTag())) {
                if (!in_array($cve->getName(), array_map(function ($cve) {
                    return $cve->getName();
                }, $cvesWithTags))
                )
                array_push($cvesWithTags, $cve);
            }
        }
        return $cvesWithTags;
    }


    public function getCveDefsForHost(Host $host){
        $pkgsCveDefs = array();

        //Get OS group
        $osGroup = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupByOsId($host->getOsId());

        //Get installed Pkgs on Host
        $installedPkgs = $this->getPakiti()->getManager("PkgsManager")->getInstalledPkgs($host);

        //Get CveDefs for Vulnerable packages
        foreach ($installedPkgs as $installedPkg) {
            $sql = "select * from CveDef inner join PkgCveDef on CveDef.id = PkgCveDef.cveDefId
                    where PkgCveDef.pkgId={$installedPkg->getId()} and PkgCveDef.osGroupId={$osGroup->getId()}";
            $cveDefsDb =& $this->getPakiti()->getManager("DbManager")->queryToMultiRow($sql);

            # Create objects
            $cveDefs = array();
            if ($cveDefsDb != null) {
                foreach ($cveDefsDb as $cveDefDb) {
                    $cveDef = new CveDef();
                    $cveDef->setId($cveDefDb["id"]);
                    $cveDef->setDefinitionId($cveDefDb["definitionId"]);
                    $cveDef->setTitle($cveDefDb["title"]);
                    $cveDef->setRefUrl($cveDefDb["refUrl"]);
                    $cveDef->setVdsSubSourceDefId($cveDefDb["vdsSubSourceDefId"]);
                    $cveDef->setCves($this->getCvesByCveDef($cveDef));
                    array_push($cveDefs, $cveDef);
                }
                $pkgsCveDefs[$installedPkg->getId()] = $cveDefs;
            }
        }
        return $pkgsCveDefs;
    }

    public function getAllCves()
    {
        $cves = $this->getPakiti()->getDao("Cve")->getAllCves();
        foreach ($cves as $cve) {
            if (is_object($cve)) {
                $cve->setTag($this->getPakiti()->getManager("TagsManager")->getCveTags($cve));
            }
        }
        return $cves;
    }

    public function getCveNames()
    {
        return $this->getPakiti()->getDao("Cve")->getCveNames();
    }

    public function getCvesByName($name)
    {
        $cves = $this->getPakiti()->getDao("Cve")->getCvesByName($name);
        foreach ($cves as $cve) {
            if (is_object($cve)) {
                $cve->setTag($this->getPakiti()->getManager("TagsManager")->getCveTags($cve));
            }
        }
        return $cves;
    }


    /**
     * For each vulnerable package on host find CVE
     * Retrun array: packageId => array of Cves
     * @param Host $host
     * @return array
     */

    public function getCvesForHost(Host $host)
    {
        $pkgsCveDefs = $this->getCveDefsForHost($host);
        $pkgsCves = array();
        foreach ($pkgsCveDefs as $pkgId => $pkgCveDefs) {
            $cves = array();
            foreach ($pkgCveDefs as $pkgCveDef) {
                foreach ($pkgCveDef->getCves() as $cve) {
                    array_push($cves, $cve);
                }
            }
            $pkgsCves[$pkgId] = $cves;
        }
        return $pkgsCves;

    }





}