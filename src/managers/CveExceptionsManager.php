<?php

/**
 * @author Michal Prochazka
 */
class CveExceptionsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeCveException(CveException $cveException)
    {
        Utils::log(LOG_DEBUG, "Storing the CveException", __FILE__, __LINE__);
        if ($cveException == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("CveException object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("CveException");
        $cveException->setId($dao->getIdByCveNamePkgIdOsGroupId($cveException->getCveName(), $cveException->getPkgId(), $cveException->getOsGroupId()));
        if ($cveException->getId() == -1) {
            # CveException is missing, so store it
            $dao->create($cveException);
            $new = true;
        }
        return $new;
    }

    public function getCvesExceptions()
    {
        return $this->getPakiti()->getDao("CveException")->getCvesExceptions();
    }

    public function getCvesExceptionsIds()
    {
        return $this->getPakiti()->getDao("CveException")->getCvesExceptionsIds();
    }

    public function getCveExceptionsByPkg(Pkg $pkg)
    {
        $this->getPakiti()->getDao("CveException")->getCveExceptionsByPkgId($pkg->getId());
    }

    public function getCveExceptionsByCveName($cveName)
    {
        return $this->getPakiti()->getDao("CveException")->getCveExceptionsByCveName($cveName);
    }

    public function getCveExceptionsCountByCveName($cveName = null)
    {
        return $this->getPakiti()->getDao("CveException")->getCveExceptionsCountByCveName($cveName);
    }

    public function removeCveExceptionById($id)
    {
        $this->getPakiti()->getDao("CveException")->deleteCveExceptionById($id);
    }

    public function getCveExceptionById($exceptionId)
    {
        return $this->getPakiti()->getDao("CveException")->getById($exceptionId);
    }

    public function isExceptionCandidate($cveName, $pkgId, $osGroupId)
    {
        return $this->getPakiti()->getDao("CveException")->isExceptionCandidate($cveName, $pkgId, $osGroupId);
    }
}
