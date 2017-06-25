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
 * @author Michal Prochazka
 */
class CveExceptionsManager extends DefaultManager
{
    private $_pakiti;

    public function __construct(Pakiti &$pakiti)
    {
        $this->_pakiti =& $pakiti;
    }

    public function getPakiti()
    {
        return $this->_pakiti;
    }


    /**
    * Create if not exist, else set id
    * @return false if already exist
    */
    public function storeCveException(CveException &$cveException)
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

    public function getCveExceptionIdByCveNamePkgIdOsGroupId($cveName, $pkgId, $osGroupId)
    {
        Utils::log(LOG_DEBUG, "Getting CVE exception ID by cveName, pkgId and osGroupId", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("CveException")->getIdByCveNamePkgIdOsGroupId($cveName, $pkgId, $osGroupId);
    }

    public function getCveExceptionsByPkg(Pkg $pkg)
    {
        $this->getPakiti()->getDao("CveException")->getCveExceptionsByPkgId($pkg->getId());
    }

    public function getCveExceptionsByCveName($cveName)
    {
        return $this->getPakiti()->getDao("CveException")->getCveExceptionsByCveName($cveName);
    }

    # remove also all associated Exceptions
    public function removeCveException(CveException $exception)
    {
        $this->getPakiti()->getDao("CveException")->delete($exception);
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
