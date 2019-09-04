<?php

/**
 * @author Vadym Yanovskyy
 * @author Jakub Mlcak
 */
class CveDefsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeCveDef(CveDef $cveDef)
    {
        Utils::log(LOG_DEBUG, "Storing the CveDef", __FILE__, __LINE__);
        if ($cveDef == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("CveDef object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("CveDef");
        $cveDef->setId($dao->getIdByDefinitionIdTitleRefUrlVdsSubSourceDefId($cveDef->getDefinitionId(), $cveDef->getTitle(), $cveDef->getRefUrl(), $cveDef->getVdsSubSourceDefId()));
        if ($cveDef->getId() == -1) {
            # CveDef def is missing, so store it
            $dao->create($cveDef);
            $new = true;
        }
        return $new;
    }

    public function assignCveToCveDef($cveId, $cveDefId)
    {
        Utils::log(LOG_DEBUG, "Assign Cve to CveDef", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveDef");
        $dao->assignCveToCveDef($cveId, $cveDefId);
    }

    public function assignPkgToCveDef($pkgId, $cveDefId, $osGroupId)
    {
        Utils::log(LOG_DEBUG, "Assign Pkg to CveDef", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveDef");
        $dao->assignPkgToCveDef($pkgId, $cveDefId, $osGroupId);
    }

    public function removePkg($pkgId)
    {
        $dao = $this->getPakiti()->getDao("CveDef");
        $dao->removePkg($pkgId);
    }
}
