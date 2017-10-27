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
 * @author Jakub Mlcak
 */
class Host
{
    private $_id = -1;
    private $_hostname;
    private $_ip;
    private $_reporterHostname;
    private $_reporterIp;
    private $_kernel;
    private $_pkgTypeId = -1;
    private $_osId = -1;
    private $_archId = -1;
    private $_domainId = -1;
    private $_numOfCves = 0;
    private $_numOfCvesWithTag = 0;
    private $_lastReportId = -1;

    # Only getters - loaded from db [1:1] with 1 column
    private $_osName;
    private $_archName;
    private $_domainName;
    private $_pkgTypeName;


    public function getId()
    {
        return $this->_id;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

    public function getHostname()
    {
        return $this->_hostname;
    }

    public function setHostname($val)
    {
        $this->_hostname = $val;
    }

    public function getIp()
    {
        return $this->_ip;
    }

    public function setIp($val)
    {
        $this->_ip = $val;
    }

    public function getReporterHostname()
    {
        return $this->_reporterHostname;
    }

    public function setReporterHostname($val)
    {
        $this->_reporterHostname = $val;
    }

    public function getReporterIp()
    {
        return $this->_reporterIp;
    }

    public function setReporterIp($val)
    {
        $this->_reporterIp = $val;
    }

    public function getKernel()
    {
        return $this->_kernel;
    }

    public function setKernel($val)
    {
        $this->_kernel = $val;
    }

    public function getPkgTypeId()
    {
        return $this->_pkgTypeId;
    }

    public function setPkgTypeId($val)
    {
        return $this->_pkgTypeId = $val;
    }

    public function getOsId()
    {
        return $this->_osId;
    }

    public function setOsId($val)
    {
        $this->_osId = $val;
    }

    public function getArchId()
    {
        return $this->_archId;
    }

    public function setArchId($val)
    {
        $this->_archId = $val;
    }

    public function getDomainId()
    {
        return $this->_domainId;
    }

    public function setDomainId($val)
    {
        return $this->_domainId = $val;
    }

    public function getNumOfCves()
    {
        return $this->_numOfCves;
    }

    public function setNumOfCves($val)
    {
        $this->_numOfCves = $val;
    }

    public function getNumOfCvesWithTag()
    {
        return $this->_numOfCvesWithTag;
    }

    public function setNumOfCvesWithTag($val)
    {
        $this->_numOfCvesWithTag = $val;
    }

    public function getLastReportId()
    {
        return $this->_lastReportId;
    }

    public function setLastReportId($val)
    {
        return $this->_lastReportId = $val;
    }

    # Extra

    public function getOsName()
    {
        return $this->_osName;
    }

    public function getArchName()
    {
        return $this->_archName;
    }

    public function getDomainName()
    {
        return $this->_domainName;
    }

    public function getPkgTypeName()
    {
        return $this->_pkgTypeName;
    }
}
