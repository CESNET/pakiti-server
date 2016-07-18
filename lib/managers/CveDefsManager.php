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
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
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
            $cve->setCveExceptions($this->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsByCveName($cve->getName()));
        }
        return $cve;
    }

    public function getCvesByCveDef(CveDef &$cveDef)
    {
        $cves = $this->getPakiti()->getDao("Cve")->getCvesByCveDef($cveDef);
        if (is_array($cves)) {
            foreach ($cves as $cve) {
                $cve->setTag($this->getPakiti()->getManager("TagsManager")->getCveTags($cve));
                $cve->setCveExceptions($this->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsByCveName($cve->getName()));
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
    public function getCveNamesWithTags()
    {

        $cveNamesWithTags = array();
        $cveNamesAssociatedWithSomeTags = $this->getPakiti()->getManager("DbManager")->queryToMultiRow("select DISTINCT cveName from CveTag");
        foreach ($cveNamesAssociatedWithSomeTags as $cveNameAssociatedWithSomeTags) {
            array_push($cveNamesWithTags, $cveNameAssociatedWithSomeTags['cveName']);
        }
        return $cveNamesWithTags;
    }


    public function getCveDefsForHost(Host $host){
        $pkgsCveDefs = array();

        //Get OS groups
        $osGroups = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupsByOs($host->getOs());

        //Get installed PkgsIds on Host
        $installedPkgIds = $this->getPakiti()->getDao("InstalledPkg")->getIdsByHostId($host->getId());
        
        //If host haven't installed any pkgs then also havn't any cveDefs
        if($installedPkgIds == null) return array();
        
        $sql = "select * from CveDef inner join PkgCveDef on CveDef.id = PkgCveDef.cveDefId where
                PkgCveDef.pkgId in
                (" . implode(",", array_map("intval", $installedPkgIds)) . ")
                and PkgCveDef.osGroupId in
                (" . implode(",", array_map("intval", array_map(function ($osGroup) { return $osGroup->getId(); }, $osGroups))) . ")";

        $cveDefsDb =& $this->getPakiti()->getManager("DbManager")->queryToMultiRow($sql);
        
        if ($cveDefsDb != null) {
            foreach ($cveDefsDb as $cveDefDb) {
                $cveDef = new CveDef();
                $cveDef->setId($cveDefDb["id"]);
                $cveDef->setDefinitionId($cveDefDb["definitionId"]);
                $cveDef->setTitle($cveDefDb["title"]);
                $cveDef->setRefUrl($cveDefDb["refUrl"]);
                $cveDef->setVdsSubSourceDefId($cveDefDb["vdsSubSourceDefId"]);

                # Exclude CVEs with exceptions
                $cves = $this->getCvesByCveDef($cveDef);
                foreach ($cves as $key => $cve) {
                    foreach ($cve->getCveExceptions() as $cveException) {
                        if ($cveException->getPkgId() == $cveDefDb["pkgId"] && $cveException->getOsGroupId() == $cveDefDb["osGroupId"]) {
                            unset($cves[$key]);
                            //If we found exception, we can skip the others
                            break;
                        }
                    }
                }
                $cveDef->setCves($cves);
                $pkgsCveDefs[$cveDefDb["pkgId"]][] = $cveDef;
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
                $cve->setCveExceptions($this->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsByCveName($cve->getName()));

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
                $cve->setCveExceptions($this->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsByCveName($cve->getName()));

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
            if (!empty($cves)) {
                $pkgsCves[$pkgId] = $cves;
            }

        }
        return $pkgsCves;

    }





}