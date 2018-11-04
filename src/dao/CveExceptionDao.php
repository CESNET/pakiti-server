<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class CveExceptionDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(CveException $exception)
    {
        $this->db->query("insert into `CveException` set
            pkgId='" . $this->db->escape($exception->getPkgId()) . "',
            `reason`='" . $this->db->escape($exception->getReason()) . "',
            cveName='" . $this->db->escape($exception->getCveName()) . "',
            osGroupId='" . $this->db->escape($exception->getOsGroupId()) . "',
            modifier='" . $this->db->escape($exception->getModifier()) . "'");

        # Set the newly assigned id
        $exception->setId($this->db->getLastInsertedId());
    }

    public function getCvesExceptions()
    {
        $sql = "select id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp from CveException";
        return $this->db->queryObjects($sql, "CveException");
    }

    public function getCvesExceptionsIds()
    {
        $sql = "select id from CveException";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getCveExceptionsByPkgId($pkgId)
    {
        return $this->db->queryObjects("select id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp from CveException
            where $pkgId={$pkgId}", "CveException");
    }

    public function getCveExceptionsByCveName($cveName)
    {
        $sql = "select id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp from CveException";
        if ($cveName != null) {
            $sql .= " where cveName='".$this->db->escape($cveName)."'";
        }
        $sql .= " order by cveName DESC";
        return $this->db->queryObjects($sql, "CveException");
    }

    public function getCveExceptionsCountByCveName($cveName)
    {
        $sql = "select count(id) from CveException";
        if ($cveName != null) {
            $sql .= " where cveName='".$this->db->escape($cveName)."'";
        }
        return $this->db->queryToSingleValue($sql);
    }

    public function getById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->getBy($id, "id");
    }

    public function getIdByCveNamePkgIdOsGroupId($cveName, $pkgId, $osGroupId)
    {
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

    public function delete(CveException $exception)
    {
        $this->db->query("delete from CveException
            where id=" . $this->db->escape($exception->getId()));
    }

    public function deleteCveExceptionById($id)
    {
        $sql = "delete from CveException
            where id=".$this->db->escape($id);
        $this->db->query($sql);
    }

    public function deleteCvesExceptions()
    {
        $this->db->query("delete from CveException");
    }

    public function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=" . $this->db->escape($value);
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->db->queryObject("select id as _id, cveName as _cveName, pkgId as _pkgId, osGroupId as _osGroupId, `reason` as _reason, modifier as _modifier, `timestamp` as _timestamp from CveException
            where $where", "CveException");
    }

    public function isExceptionCandidate($cveName, $pkgId, $osGroupId)
    {
        $sql = "select 1 from Cve
            inner join CveCveDef on Cve.id = CveCveDef.cveId
            inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId
            where Cve.name = '".$this->db->escape($cveName)."'
            and PkgCveDef.pkgId = '".$this->db->escape($pkgId)."'
            and PkgCveDef.osGroupId = '".$this->db->escape($osGroupId)."'";

        if ($this->db->queryToSingleValue($sql) == 1) {
            return true;
        } else {
            return false;
        }
    }
}
