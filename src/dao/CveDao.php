<?php

/**
 * @author Vadym Yanovskyy
 * @author Jakub Mlcak
 */
class CveDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(Cve $cve)
    {
        $sql = "insert into Cve set
            name='" . $this->db->escape($cve->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $cve->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from Cve
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "Cve");
    }

    public function getIdByName($name)
    {
        $sql = "select id from Cve
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getNamesForPkgAndOs($pkgId, $osId, $tag = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $order = "Cve.name DESC";

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        # pkg
        $join[] = "inner join PkgCveDef on (CveCveDef.cveDefId = PkgCveDef.cveDefId and PkgCveDef.pkgId = '" . $this->db->escape($pkgId) . "')";

        # os
        $join[] = "inner join OsOsGroup on (PkgCveDef.osGroupId = OsOsGroup.osGroupId and OsOsGroup.osId = '" . $this->db->escape($osId) . "')";

        # exceptions
        $join[] = "left join CveException on (Cve.name = CveException.cveName and PkgCveDef.pkgId = CveException.pkgId and PkgCveDef.osGroupId = CveException.osGroupId)";
        $where[] = "CveException.id IS NULL";

        # tag
        if ($tag !== null) {
            if ($tag === true) {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
            } elseif ($tag === false){
                $join[] = "left join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
                $where[] = "CveTag.id IS NULL ";
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getNamesForHost($hostId, $tag = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $order = "Cve.name DESC";

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        # pkgs
        $join[] = "inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId";
        $join[] = "inner join InstalledPkg on (PkgCveDef.pkgId = InstalledPkg.pkgId and InstalledPkg.hostId = '" . $this->db->escape($hostId) . "')";

        # os
        $join[] = "inner join OsOsGroup on PkgCveDef.osGroupId = OsOsGroup.osGroupId";
        $join[] = "inner join Host on (OsOsGroup.osId = Host.osId and Host.id = '" . $this->db->escape($hostId) . "')";

        # exceptions
        $join[] = "left join CveException on (Cve.name = CveException.cveName and PkgCveDef.pkgId = CveException.pkgId and PkgCveDef.osGroupId = CveException.osGroupId)";
        $where[] = "CveException.id IS NULL";

        # tag
        if ($tag !== null) {
            if ($tag === true) {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
            } elseif ($tag === false){
                $join[] = "left join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
                $where[] = "CveTag.id IS NULL";
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getNames($used = null, $pageSize = -1, $pageNum = -1)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $join = null;
        $where = null;
        $order = "Cve.name DESC";
        $limit = null;
        $offset = null;

        # cveDef
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";

        if ($used !== null) {
            if ($used === true) {
                $join[] = "inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId";
            }
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    /* A generalized version of getNamesForHost() and comp.; lists CVEs assigned at least to one host. */
    /* A lot of cut&paste from getHostsIds() */
    public function getNamesForHosts($pageSize = -1, $pageNum = -1, $tag = null, $hostGroupId = -1, $activity = null)
    {
        $select = "distinct(Cve.name)";
        $from = "Cve";
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";
        $join[] = "inner join PkgCveDef on CveCveDef.cveDefId = PkgCveDef.cveDefId";
        $join[] = "inner join OsOsGroup on PkgCveDef.osGroupId = OsOsGroup.osGroupId";
        $join[] = "inner join InstalledPkg on PkgCveDef.pkgId = InstalledPkg.pkgId";
        $join[] = "inner join Host on InstalledPkg.hostId = Host.id";
        $join[] = "left join CveException on (Cve.name = CveException.cveName and PkgCveDef.pkgId = CveException.pkgId and PkgCveDef.osGroupId = CveException.osGroupId)";
        $where[] = "CveException.id IS NULL";
        $where[] = "Host.osId = OsOsGroup.osId";
        $limit = null;
        $offset = null;
        $order = "Cve.name DESC";

        if ($tag != null) {
            if ($tag === true) {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1')";
            } else {
                $join[] = "inner join CveTag on (Cve.name = CveTag.cveName and CveTag.enabled = '1' and CveTag.tagName = '" . $this->db->escape($tag) . "')";
            }
        }

        if ($hostGroupId != -1) {
            $join[] = "inner join HostHostGroup on HostHostGroup.hostId = Host.id";
            $where[] = "HostHostGroup.hostGroupId = '".$this->db->escape($hostGroupId)."'";
        }

        if ($activity != null) {
            $join[] = "left join Report on Host.lastReportId = Report.id";
            if (preg_match('/^(\+|-|)(\d+)(.|)$/', trim($activity), $matches) === 1) {
                switch ($matches[1]) {
                    case "-":
                        $operator = "<";
                        break;
                    default:
                        $operator = ">";
                        break;
                }
                $number = $matches[2];
                switch ($matches[3]) {
                    case "s":
                        $interval = "second";
                        break;
                    case "m":
                        $interval = "minute";
                        break;
                    case "h":
                        $interval = "hour";
                        break;
                    case "w":
                        $interval = "week";
                        break;
                    case "y":
                        $interval = "year";
                        break;
                    default:
                        $interval = "day";
                        break;
                }
                $where[] = "Report.receivedOn ".$operator." date_sub(now(), interval ".$number." ".$interval.")";
            }
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function countCves($vdsSubSourceDef_id)
    {
        $select = "count(distinct(Cve.id))";
        $from = "Cve";
        $join[] = "inner join CveCveDef on Cve.id = CveCveDef.cveId";
        $join[] = "inner join CveDef on (CveCveDef.cveDefId = CveDef.id and CveDef.vdsSubSourceDefId = " . $this->db->escape($vdsSubSourceDef_id) . ")";

        $sql = Utils::sqlSelectStatement($select, $from, $join);
        return $this->db->queryToSingleValue($sql);
    }
}
