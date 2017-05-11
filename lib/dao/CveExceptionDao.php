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
class CveExceptionDao
{
    private $db;

    public function __construct(DbManager &$dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveException &$exception)
    {
        $timestamp = new DateTime();
        $this->db->query(
            "insert into `CveException` set
        pkgId={$exception->getPkgId()},
      	`reason`='" . $this->db->escape($exception->getReason()) . "',
      	cveName='" . $this->db->escape($exception->getCveName()) . "',
      	osGroupId='" . $this->db->escape($exception->getOsGroupId()) . "',
      	modifier='" . $this->db->escape($exception->getModifier()) . "',
      	timestamp='" . $timestamp->format('Y-m-d H:i:s') . "'
      	");

        # Set the newly assigned id
        $exception->setId($this->db->getLastInsertedId());
        $exception->setTimestamp($timestamp->format('Y-m-d H:i:s'));
    }

    public function getCvesExceptions()
    {
        $sql = "select id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp from CveException";
        return $this->db->queryObjects($sql, "CveException");
    }

    public function getCveExceptionsByPkgId($pkgId)
    {
        return $this->db->queryObjects(
            "select
    		id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier,  `timestamp` as _timestamp
      from
      	CveException
      where
        $pkgId={$pkgId}", "CveException");
    }

    public function getCveExceptionsByCveName($cveName)
    {
        return $this->db->queryObjects(
            "select
    		id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp
      from
      	CveException
      where
        cveName='" . $this->db->escape($cveName) . "'
        ", "CveException");
    }

    public function getById($id)
    {
        if (!is_numeric($id)) return null;
        return $this->getBy($id, "id");
    }

    public function getByCveNamePkgIdOsGroupId($cveName, $pkgId, $osGroupId)
    {
        return $this->db->queryObject(
            "select
    		id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp
      from
      	CveException
      where
        cveName='" . $this->db->escape($cveName) . "' and
        pkgId='" . $this->db->escape($pkgId) . "' and
        osGroupId='" . $this->db->escape($osGroupId) . "'
        ", "CveException");
    }

    public function getIdByCveNamePkgIdOsGroupId($cveName, $pkgId, $osGroupId) {
        $sql = "select id from CveException
            where cveName='" . $this->db->escape($cveName) . "'
            and pkgId='" . $this->db->escape($pkgId) . "'
            and osGroupId='" . $this->db->escape($osGroupId) . "'";

        $id = $this->db->queryToSingleValue($sql);
        if ($id == null) {
            return -1;
        }
        return $id;
    }

    public function delete(CveException &$exception)
    {
        $this->db->query(
            "delete from CveException where id=" . $this->db->escape($exception->getId()));
    }

    public function deleteCvesExceptions()
    {
        $this->db->query(
            "delete from CveException"
        );
    }

    public function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=" . $this->db->escape($value);
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->db->queryObject(
            "select
    		id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp
      from
      	CveException
      where
      	$where", "CveException");
    }

    public function isExceptionCandidate($cveName, $pkgId, $osGroupId){
        $sql = "select 1 from Cve
            inner join PkgCveDef on Cve.cveDefId = PkgCveDef.cveDefId
            where Cve.name = '".$this->db->escape($cveName)."'
            and PkgCveDef.pkgId = '".$this->db->escape($pkgId)."'
            and PkgCveDef.osGroupId = '".$this->db->escape($osGroupId)."'";
        
        if($this->db->queryToSingleValue($sql) == 1){
            return true;
        } else {
            return false;
        }
    }

}
