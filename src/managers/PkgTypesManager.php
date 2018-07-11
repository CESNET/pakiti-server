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
 * @author Jakub Mlcak
 */
class PkgTypesManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storePkgType(PkgType &$pkgType)
    {
        Utils::log(LOG_DEBUG, "Storing the pkgType", __FILE__, __LINE__);
        if ($pkgType == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("PkgType object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("PkgType");
        $pkgType->setId($dao->getIdByName($pkgType->getName()));
        if ($pkgType->getId() == -1) {
            # PkgType is missing, so store it
            $dao->create($pkgType);
            $new = true;
        }
        return $new;
    }

    /**
     * Get pkgType ID by name
     */
    public function getPkgTypeIdByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting pkgType ID by name[$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("PkgType")->getIdByName($name);
    }

    /**
     * Get all pkgTypes IDs
     */
    public function getPkgTypesIds()
    {
        return $this->getPakiti()->getDao("PkgType")->getIds();
    }
}
