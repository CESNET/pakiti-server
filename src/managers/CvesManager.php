<?php

/**
 * @author Jakub Mlcak
 */
class CvesManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeCve(Cve $cve)
    {
        Utils::log(LOG_DEBUG, "Storing the Cve", __FILE__, __LINE__);
        if ($cve == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Cve object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Cve");
        $cve->setId($dao->getIdByName($cve->getName()));
        if ($cve->getId() == -1) {
            # Cve is missing, so store it
            $dao->create($cve);
            $new = true;
        }
        return $new;
    }

    /**
     * Getting CVEs names for package and OS
     * @param $pkgId
     * @param $osId
     * @param $tag = null -> all cves | true -> only tagged CVEs | false -> only CVEs without tag | string -> only tagged CVEs with tag name
     * @return array of CVEs names
     */
    public function getCvesNamesForPkgAndOs($pkgId, $osId, $tag = null)
    {
        Utils::log(LOG_DEBUG, "Getting CVEs names for pkg[$pkgId] and os[$osId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Cve");
        return $dao->getNamesForPkgAndOs($pkgId, $osId, $tag);
    }

    /**
     * Getting CVEs names for host
     * @param $hostId
     * @param $tag = null -> all cves | true -> only tagged CVEs | false -> only CVEs without tag | string -> only tagged CVEs with tag name
     * @return array of CVEs names
     */
    public function getCvesNamesForHost($hostId, $tag = null)
    {
        Utils::log(LOG_DEBUG, "Getting CVEs names for host[$hostId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Cve");
        return $dao->getNamesForHost($hostId, $tag);
    }

    /**
     * Getting CVEs names
     * @param $used = null -> all cves | true -> only CVEs which have pkg stored in dtb
     * @return array of CVEs names
     */
    public function getCvesNames($used = null)
    {
        Utils::log(LOG_DEBUG, "Getting CVEs names", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Cve");
        return $dao->getNames($used);
    }

    public function getCvesNamesForHosts($pageSize = -1, $pageNum = -1, $tag = null, $hostGroupId = -1, $activity = null)
    {
        $dao = $this->getPakiti()->getDao("Cve");
        return $dao->getNamesForHosts($pageSize, $pageNum, $tag, $hostGroupId, $activity);
    }

    public function getSubSourceDefNames($cveName)
    {
        $dao = $this->getPakiti()->getDao("VdsSubSourceDef");
        return $dao->getNamesByCveName($cveName);
    }

    public function countCves($vdsSubSourceDef_id)
    {
        $dao = $this->getPakiti()->getDao("Cve");
        return $dao->countCves($vdsSubSourceDef_id);
    }
}
