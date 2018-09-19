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
class CveDefDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveDef $cveDef)
    {
        $sql = "insert into CveDef set
            definitionId='".$this->db->escape($cveDef->getDefinitionId())."',
            title='".$this->db->escape($cveDef->getTitle())."',
            refUrl='".$this->db->escape($cveDef->getRefUrl())."',
            vdsSubSourceDefId='".$this->db->escape($cveDef->getVdsSubSourceDefId()). "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $cveDef->setId($this->db->getLastInsertedId());
    }

    public function getIdByDefinitionIdTitleRefUrlVdsSubSourceDefId($definitionId, $title, $refUrl, $vdsSubSourceDefId)
    {
        $sql = "select id from CveDef
            where definitionId='".$this->db->escape($definitionId)."'
            and title='".$this->db->escape($title)."'
            and refUrl='".$this->db->escape($refUrl)."'
            and vdsSubSourceDefId='".$this->db->escape($vdsSubSourceDefId)."'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function assignCveToCveDef($cveId, $cveDefId)
    {
        $sql = "insert ignore into CveCveDef set
            cveId = '".$this->db->escape($cveId)."',
            cveDefId = '".$this->db->escape($cveDefId)."'";
        $this->db->query($sql);
    }

    public function assignPkgToCveDef($pkgId, $cveDefId, $osGroupId)
    {
        $sql = "insert ignore into PkgCveDef set
            pkgId = '".$this->db->escape($pkgId)."',
            cveDefId = '".$this->db->escape($cveDefId)."',
            osGroupId = '".$this->db->escape($osGroupId)."'";
        $this->db->query($sql);
    }
}
