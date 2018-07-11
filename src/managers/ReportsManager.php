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
class ReportsManager extends DefaultManager
{
    public function getReportById($id)
    {
        Utils::log(LOG_DEBUG, "Getting the report by its ID [id=$id]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Report")->getById($id);
    }

    /**
     * Get all host reports
     */
    public function getHostReports($hostId = -1, $orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting reports for host ID[". $hostId ."]", __FILE__, __LINE__);
        $ids = $this->getPakiti()->getDao("Report")->getHostReportsIds($hostId, $orderBy, $pageSize, $pageNum);

        $reports = array();
        foreach ($ids as $id) {
            array_push($reports, $this->getPakiti()->getDao("Report")->getById($id));
        }
        return $reports;
    }

    public function getReportsByHostIdFromTo($hostId, $from, $to)
    {
        Utils::log(LOG_DEBUG, "Getting reports for host ID[". $hostId ."] from [$from] to [$to]", __FILE__, __LINE__);
        $ids = $this->getPakiti()->getDao("Report")->getReportsIdsByHostIdFromTo($hostId, $from, $to);

        $reports = array();
        foreach ($ids as $id) {
            array_push($reports, $this->getPakiti()->getDao("Report")->getById($id));
        }
        return $reports;
    }

    /**
     * Get all host reports count
     */
    public function getHostReportsCount($hostId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting the count of all reports for host ID[".$hostId."]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Report")->getHostReportsCount($hostId);
    }

    /*
     * Stores the report in the DB
     */
    public function createReport(Report &$report, Host &$host)
    {
        if ($host == null || $host->getId() == -1) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Host object is not valid or Host.id is not set");
        }

        Utils::log(LOG_DEBUG, "Creating the report", __FILE__, __LINE__);
        if ($report == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Report object is not valid");
        }

        # Store the report details
        $this->getPakiti()->getDao("Report")->create($report);

        # Assign the report to the host
        $sql = "insert into ReportHost set hostId=".$host->getId().", reportId=".$report->getId();

        $this->getPakiti()->getManager("DbManager")->query($sql);

        # Assign the lastReportId to the host
        $sql = "update Host set ".
            "lastReportId=".$report->getId().", ".
            "lastReportHeaderHash='".$this->getPakiti()->getManager("DbManager")->escape($report->getHeaderHash())."', ".
            "lastReportPkgsHash='".$this->getPakiti()->getManager("DbManager")->escape($report->getPkgsHash())."', ".
            "numOfCves='".$this->getPakiti()->getManager("DbManager")->escape($report->getNumOfCves())."', ".
            "numOfCvesWithTag='".$this->getPakiti()->getManager("DbManager")->escape($report->getNumOfCvesWithTag())."' ".
            "where id=".$host->getId();

        $this->getPakiti()->getManager("DbManager")->query($sql);

        return $report;
    }

    /**
     * Retrieve both hashes for the report header and list of pkgs
     */
    public function getLastReportHashes(Host &$host)
    {
        if ($host == null || $host->getId() == -1) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Host object is not valid or Host.id is not set");
        }
        Utils::log(LOG_DEBUG, "Getting last report hashes [hostId=" . $host->getId() . "]", __FILE__, __LINE__);
        $row = $this->getPakiti()->getManager("DbManager")->queryToSingleRow("select lastReportHeaderHash, lastReportPkgsHash from Host where id=" . $this->getPakiti()->getManager("DbManager")->escape($host->getId()));

        $ret = array(
            Constants::$REPORT_LAST_HEADER_HASH => $row["lastReportHeaderHash"],
            Constants::$REPORT_LAST_PKGS_HASH => $row["lastReportPkgsHash"]
        );
        return $ret;
    }
}
