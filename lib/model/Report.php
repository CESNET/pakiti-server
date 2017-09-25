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
class Report
{
    private $_id = -1;
    private $_receivedOn;
    private $_processedOn;
    private $_throughProxy;
    private $_proxyHostname = null;
    private $_hostGroup;
    private $_source;
    private $_numOfInstalledPkgs = -1;
    private $_numOfVulnerablePkgsSec = 0;
    private $_numOfVulnerablePkgsNorm = 0;
    private $_numOfCves = -1;
    private $_numOfCvesWithTag = -1;
    private $_headerHash;
    private $_pkgsHash;

    public function getId()
    {
        return $this->_id;
    }

    public function getReceivedOn()
    {
        return $this->_receivedOn;
    }

    public function getProcessedOn()
    {
        return $this->_processedOn;
    }

    public function getThroughProxy()
    {
        return $this->_throughProxy;
    }

    public function getHostGroup()
    {
        return $this->_hostGroup;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getNumOfInstalledPkgs()
    {
        return $this->_numOfInstalledPkgs;
    }

    public function getNumOfVulnerablePkgsSec()
    {
        return $this->_numOfVulnerablePkgsSec;
    }

    public function getNumOfVulnerablePkgsNorm()
    {
        return $this->_numOfVulnerablePkgsNorm;
    }

    public function getNumOfCves()
    {
        return $this->_numOfCves;
    }

    public function getNumOfCvesWithTag()
    {
        return $this->_numOfCvesWithTag;
    }

    public function getProxyHostname()
    {
        return $this->_proxyHostname;
    }

    public function getHeaderHash()
    {
        return $this->_headerHash;
    }

    public function getPkgsHash()
    {
        return $this->_pkgsHash;
    }

    public function setId($val)
    {
        $this->_id = $val;
    }

    public function setReceivedOn($val)
    {
        $this->_receivedOn = $val;
    }

    public function setProcessedOn($val)
    {
        $this->_processedOn = $val;
    }

    public function setTroughtProxy($val)
    {
        $this->_throughProxy = $val;
    }

    public function setHostGroup($val)
    {
        $this->_hostGroup = $val;
    }

    public function setSource($val)
    {
        $this->_source = $val;
    }

    public function setNumOfInstalledPkgs($val)
    {
        $this->_numOfInstalledPkgs = $val;
    }

    public function setNumOfVulnerablePkgsSec($val)
    {
        $this->_numOfVulnerablePkgsSec = $val;
    }

    public function setNumOfVulnerablePkgsNorm($val)
    {
        $this->_numOfVulnerablePkgsNorm = $val;
    }

    public function setNumOfCves($val)
    {
        $this->_numOfCves = $val;
    }

    public function setNumOfCvesWithTag($val)
    {
        $this->_numOfCvesWithTag = $val;
    }

    public function setProxyHostname($val)
    {
        $this->_proxyHostname = $val;
    }

    public function setHeaderHash($val)
    {
        return $this->_headerHash = $val;
    }

    public function setPkgsHash($val)
    {
        return $this->_pkgsHash = $val;
    }
}
