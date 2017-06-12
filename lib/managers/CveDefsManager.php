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
 * @author Vadym Yanovskyy
 * @author Jakub Mlcak
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
            $cve->setTag($this->getPakiti()->getManager("CveTagsManager")->getCveTagsByCveName($cve->getName()));
            $cve->setCveExceptions($this->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsByCveName($cve->getName()));
        }
        return $cve;
    }

    public function getCvesByCveDef(CveDef &$cveDef)
    {
        $cves = $this->getPakiti()->getDao("Cve")->getCvesByCveDef($cveDef);
        if (is_array($cves)) {
            foreach ($cves as $cve) {
                $cve->setTag($this->getPakiti()->getManager("CveTagsManager")->getCveTagsByCveName($cve->getName()));
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
    public function getCvesCount(Host $host, $tagged = false){
        $osGroupsIds = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupsIdsByOsName($host->getOs()->getName());
        $hostId = $host->getId();
        return $this->getCvesCountByHostIdAndOsGroupsIds($hostId, $osGroupsIds, $tagged);
    }

    public function getCvesCountByHostIdAndOsGroupsIds($hostId, $osGroupsIds, $tagged = false){

        if(empty($osGroupsIds)){
            return 0;
        }
        $sql = "select COUNT(distinct Cve.name)
            from InstalledPkg
            inner join PkgCveDef on InstalledPkg.pkgId = PkgCveDef.pkgId
            inner join Cve on PkgCveDef.cveDefId = Cve.cveDefId";
        if($tagged){
            $sql .= " inner join CveTag on Cve.name = CveTag.cveName";
        }
        $sql .= " left join CveException on (PkgCveDef.pkgId = CveException.pkgId and Cve.name = CveException.cveName)
            where InstalledPkg.hostId = '". $hostId ."'";
        if($tagged){
            $sql .= " and CveTag.enabled = 1";
        }
        $sql .= " and CveException.id IS NULL
            and PkgCveDef.osGroupId in (" . implode(",", array_map("intval", $osGroupsIds)) . ")";

        $cvesCount = $this->getPakiti()->getManager("DbManager")->queryToSingleValue($sql);
        if($cvesCount == NULL){
            $cvesCount = 0;
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

    public function getCveDefsForHost(Host $host)
    {
        $osGroupsIds = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupsIdsByOsName($host->getOs()->getName());
        $pkgsIds = $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsIdsByHostId($host->getId());
        return $this->getCveDefsForPkgs($pkgsIds, $osGroupsIds);
    }

    public function getCveDefsForPkgs($pkgsIds, $osGroupsIds){

        if(empty($pkgsIds) || empty($osGroupsIds)){
            return array();
        }
        $sql = "select *
            from PkgCveDef
            inner join CveDef on PkgCveDef.cveDefId = CveDef.id
            where PkgCveDef.pkgId in (" . implode(",", array_map("intval", $pkgsIds)) . ")
            and PkgCveDef.osGroupId in (" . implode(",", array_map("intval", $osGroupsIds)) . ")";

        $cveDefsDb =& $this->getPakiti()->getManager("DbManager")->queryToMultiRow($sql);
        $pkgsCveDefs = array();
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
        return $pkgsCveDefs;
    }

    public function getAllCves()
    {
        $cves = $this->getPakiti()->getDao("Cve")->getAllCves();
        foreach ($cves as $cve) {
            if (is_object($cve)) {
                $cve->setTag($this->getPakiti()->getManager("CveTagsManager")->getCveTagsByCveName($cve->getName()));
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
                $cve->setTag($this->getPakiti()->getManager("CveTagsManager")->getCveTagsByCveName($cve->getName()));
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
        $osGroupsIds = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupsIdsByOsName($host->getOs()->getName());
        $pkgsIds = $this->getPakiti()->getDao("InstalledPkg")->getInstalledPkgsIdsByHostId($host->getId());
        return $this->getCvesForPkgs($pkgsIds, $osGroupsIds);
    }

    public function getCvesForPkgs($pkgsIds, $osGroupsIds)
    {
        if(empty($pkgsIds) || empty($osGroupsIds)){
            return array();
        }
        $sql = "select PkgCveDef.pkgId as pkgId, Cve.id as id, Cve.name as name, Cve.cveDefId as cveDefId
            from PkgCveDef
            inner join Cve on PkgCveDef.cveDefId = Cve.cveDefId
            left join CveException on (PkgCveDef.pkgId = CveException.pkgId and Cve.name = CveException.cveName)
            where PkgCveDef.pkgId in (" . implode(",", array_map("intval", $pkgsIds)) . ")
            and CveException.id IS NULL
            and PkgCveDef.osGroupId in (" . implode(",", array_map("intval", $osGroupsIds)) . ")";

        $cvesDb = $this->getPakiti()->getManager("DbManager")->queryToMultiRow($sql);

        $cves = array();
        foreach ($cvesDb as $cveDb) {
            $cve = new Cve();
            $cve->setId($cveDb["id"]);
            $cve->setName($cveDb["name"]);
            $cve->setCveDefId($cveDb["cveDefId"]);
            $cve->setTag($this->getPakiti()->getManager("CveTagsManager")->getCveTagsByCveName($cve->getName()));
            $cves[$cveDb["pkgId"]][] = $cve;
        }
        return $cves;
    }





}