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
class CveException
{
    private $_id;
    private $_pkgId;
    private $_osGroupId;
    private $_cveName;
    private $_reason;
    private $_modifier;
    private $_timestamp;

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getPkgId()
    {
        return $this->_pkgId;
    }

    public function setPkgId($pkgId)
    {
        $this->_pkgId = $pkgId;
    }

    public function getOsGroupId()
    {
        return $this->_osGroupId;
    }

    public function setOsGroupId($osGroupId)
    {
        $this->_osGroupId = $osGroupId;
    }

    public function getCveName()
    {
        return $this->_cveName;
    }

    public function setCveName($cveName)
    {
        $this->_cveName = $cveName;
    }

    public function getReason()
    {
        return $this->_reason;
    }

    public function setReason($reason)
    {
        $this->_reason = $reason;
    }

    public function getModifier()
    {
        return $this->_modifier;
    }

    public function setModifier($modifier)
    {
        $this->_modifier = $modifier;
    }
}
