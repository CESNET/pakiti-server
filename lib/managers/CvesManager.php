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
class CvesManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeCve(Cve &$cve)
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
}
