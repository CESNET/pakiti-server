<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

class ReportsManager extends DefaultManager {
  private $_pakiti;

  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
  }

  public function getPakiti() {
    return $this->_pakiti;
  }
  
  public function getReportById($id) {
    Utils::log(LOG_DEBUG, "Getting the report by its ID [id=$id]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Report")->getById($id);
  }

  /*
   * Get all host reports
   */
  public function getHostReports(Host &$host, $orderBy, $pageSize, $pageNum) {
    Utils::log(LOG_DEBUG, "Getting all reports for host [hostname={$host->getHostname()}]", __FILE__, __LINE__);
    $reportsIds =& $this->getPakiti()->getDao("Report")->getHostReportsIds($host->getId(), $orderBy, $pageSize, $pageNum); 

    $reports = array();
    foreach ($reportsIds as $reportId) {
      array_push($reports, $this->getPakiti()->getDao("Report")->getById($reportId));
    }

    return $reports;
  }

  /*
   * Get all host reports count
   */
  public function getHostReportsCount(Host &$host) {
    Utils::log(LOG_DEBUG, "Getting the count of all reports for host [hostname={$host->getHostname()}]", __FILE__, __LINE__);
    return $this->getPakiti()->getDao("Report")->getHostReportsIdsCount($host->getId()); 
  }

  
  /*
   * Stores the report in the DB
   */
  public function createReport(Report &$report, Host &$host) {
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
      "lastReportPkgsHash='".$this->getPakiti()->getManager("DbManager")->escape($report->getPkgsHash())."' ".
      "where id=".$host->getId();
    
    $this->getPakiti()->getManager("DbManager")->query($sql);


    return $report;
  }

  public function updateReport(Report &$report)
  {
    if ($report == null || $report->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Report object is not valid or Report.id is not set");
    }

    Utils::log(LOG_DEBUG, "Updating the report", __FILE__, __LINE__);

    $this->getPakiti()->getDao("Report")->update($report);

  }

  
  /*
   * Retrieve both hashes for the report header and list of pkgs
   */
  public function getLastReportHashes(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }

    Utils::log(LOG_DEBUG, "Getting last report hashes [hostId=" . $host->getId() . "]", __FILE__, __LINE__);
    
    $row = $this->getPakiti()->getManager("DbManager")->queryToSingleRow(
        "select lastReportHeaderHash, lastReportPkgsHash from Host where id=" . $this->getPakiti()->getManager("DbManager")->escape($host->getId()));

    $ret = array (
      Constants::$REPORT_LAST_HEADER_HASH => $row[0],
      Constants::$REPORT_LAST_PKGS_HASH => $row[1]
    );
    return $ret;
  }

  /*
   * Store the hashes of the report header and pkgs
   */
  public function storeReportHashes(Host &$host, $headerHash, $pkgsHash) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }

    Utils::log(LOG_DEBUG, "Storing the report hashes [hostId=" . $host->getId() . ",headerHash=$headerHash,pkgsHash=$pkgsHash]", __FILE__, __LINE__);
   
    $this->getPakiti()->getManager("DbManager")->query(
        "update Host set lastReportHeaderHash='" . $this->getPakiti()->getManager("DbManager")->escape($headerHash) . "',
    		lastReportPkgsHash='" . $this->getPakiti()->getManager("DbManager")->escape($pkgsHash) . "' where id=" . $this->getPakiti()->getManager("DbManager")->escape($host->getId()));
  }
  
  /*
   * Removes all host's reports
   */
  public function removeHostReports(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }

    Utils::log(LOG_DEBUG, "Removing all reports associated with the host [hostname='{$host->getHostname()}']", __FILE__, __LINE__);
    
    $this->getPakiti()->getDao("Report")->deleteReportsByHostId($host->getId());
  }
}
