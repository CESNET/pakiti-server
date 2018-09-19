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
class ReportDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(Report $report)
    {
        $this->db->query("insert into Report set
            processedOn='".$this->db->escape(date('Y-m-d H:i:s', $report->getProcessedOn()))."',
            receivedOn='".$this->db->escape(date('Y-m-d H:i:s', $report->getReceivedOn()))."',
            throughProxy=".$this->db->escape($report->getThroughProxy()).",
            proxyHostname='".$this->db->escape($report->getProxyHostname())."',
            hostGroup='".$this->db->escape($report->getHostGroup())."',
            source='".$this->db->escape($report->getSource())."',
            numOfInstalledPkgs=".$this->db->escape($report->getNumOfInstalledPkgs()).",
            numOfVulnerablePkgsSec=".$this->db->escape($report->getNumOfVulnerablePkgsSec()).",
            numOfVulnerablePkgsNorm=".$this->db->escape($report->getNumOfVulnerablePkgsNorm()).",
            numOfCves=".$this->db->escape($report->getNumOfCves()).",
            numOfCvesWithTag=".$this->db->escape($report->getNumOfCvesWithTag()));

        # Set the newly assigned id
        $report->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->getBy($id, "id");
    }

    public function getByName($name)
    {
        $this->getBy($name, "name");
    }

    public function getIdByName($name)
    {
        $id = $this->db->queryToSingleValue("select id from Report
            where name='".$this->db->escape($name)."'");
        if ($id == null) {
            return -1;
        }
        return $id;
    }

    public function update(Report $report)
    {
        $this->db->query("update Report set
            processedOn='".$this->db->escape(date('Y-m-d H:i:s', $report->getProcessedOn()))."',
            receivedOn='".$this->db->escape(date('Y-m-d H:i:s', $report->getReceivedOn()))."',
            throughProxy=".$this->db->escape($report->getThroughProxy()).",
            proxyHostname='".$this->db->escape($report->getProxyHostname())."',
            hostGroup='".$this->db->escape($report->getHostGroup())."',
            source='".$this->db->escape($report->getSource())."',
            numOfInstalledPkgs=".$this->db->escape($report->getNumOfInstalledPkgs()).",
            numOfVulnerablePkgsSec=".$this->db->escape($report->getNumOfVulnerablePkgsSec()).",
            numOfVulnerablePkgsNorm=".$this->db->escape($report->getNumOfVulnerablePkgsNorm()).",
            numOfCves=".$this->db->escape($report->getNumOfCves()).",
            numOfCvesWithTag=".$this->db->escape($report->getNumOfCvesWithTag())."
            where id=".$report->getId());
    }

    public function delete(Report $report)
    {
        $this->db->query("delete from Report where id=".$report->getId());
    }

    public function getReportsIds($orderBy, $pageSize, $pageNum)
    {
        $sql = "select id from Report";
        switch ($orderBy) {
            default:
                $sql .= " order by receivedOn";
        }

        if ($pageSize != -1 && $pageNum != -1) {
            $offset = $pageSize*$pageNum;
            $sql .= " limit $offset,$pageSize";
        }

        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getHostReportsIds($hostId = -1, $orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        $select = "Report.id";
        $from = "Report";
        $join = null;
        $where = null;
        $order = null;
        $limit = null;
        $offset = null;

        if ($hostId != -1) {
            $join[] = "left join ReportHost on Report.id = ReportHost.reportId";
            $where[] = "ReportHost.hostId=".$hostId."";
        }

        switch ($orderBy) {
            case "id":
                $order[] = "Report.id";
                break;
            case "processedOn":
                $order[] = "Report.receivedOn DESC";
                break;
            default:
                break;
        }
        $order[] = "Report.receivedOn DESC";

        if ($pageSize != -1 && $pageNum != -1) {
            $limit = $pageSize;
            $offset = $pageSize * $pageNum;
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order, $limit, $offset);
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    public function getHostReportsCount($hostId = -1)
    {
        $sql = "select count(id) from Report";
        if ($hostId != -1) {
            $sql .= " left join ReportHost on Report.id=ReportHost.reportId
                where ReportHost.hostId='".$this->db->escape($hostId)."'";
        }
        return $this->db->queryToSingleValue($sql);
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=".$this->db->escape($value);
        } elseif ($type == "name") {
            $where = "name='".$this->db->escape($value)."'";
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->db->queryObject("select
            id as _id,
            processedOn as _processedOn,
            receivedOn as _receivedOn,
            throughProxy as _throughProxy,
            proxyHostname as _proxyHostname,
            hostGroup as _hostGroup,
            source as _source,
            numOfInstalledPkgs as _numOfInstalledPkgs, 
            numOfVulnerablePkgsSec as _numOfVulnerablePkgsSec,
            numOfVulnerablePkgsNorm as _numOfVulnerablePkgsNorm,
            numOfCves as _numOfCves,
            numOfCvesWithTag as _numOfCvesWithTag
            from Report
            where $where", "Report");
    }

    public function getReportsIdsByHostIdFromTo($hostId, $fromDate, $toDate)
    {
        $select = "distinct Report.id";
        $from = "Report";
        $join = "inner join ReportHost on Report.id = ReportHost.reportId";
        $where = null;
        $order = "Report.receivedOn";

        if ($hostId != null) {
            $where[] = "ReportHost.hostId=".$this->db->escape($hostId);
        }
        if ($fromDate != null) {
            $where[] = "Report.receivedOn >= '".$this->db->escape($fromDate)."'";
        }
        if ($toDate != null) {
            $where[] = "Report.receivedOn <= '".$this->db->escape($toDate)."'";
        }

        $sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
