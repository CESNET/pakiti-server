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

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("host");

$host = $pakiti->getManager("HostsManager")->getHostById($html->getHttpGetVar("hostId", -1), $html->getUserId());
if ($host == null) {
    $html->fatalError("Host with id " . $id . " doesn't exist or access denied");
    exit;
}

$html->setTitle("Host: " . $host->getHostname());
$html->setNumOfEntities($pakiti->getManager("ReportsManager")->getHostReportsCount($host->getId()));

$reports = $pakiti->getManager("ReportsManager")->getHostReports($host, $html->getSortBy(), $html->getPageSize(), $html->getPageNum());

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $host->getHostname(); ?></h1>
<ul class="nav nav-tabs">
    <li role="presentation"><a href="host.php?hostId=<?php echo $host->getId(); ?>">Detail</a></li>
    <li role="presentation" class="active"><a href="reports.php?hostId=<?php echo $host->getId(); ?>">Reports</a></li>
    <li role="presentation"><a href="packages.php?hostId=<?php echo $host->getId(); ?>">Packages</a></li>
    <li role="presentation"><a href="cves.php?hostId=<?php echo $host->getId(); ?>">CVEs</a></li>
</ul>

<br><br>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="300">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "id")); ?>">ID</a>
                <?php if ($html->getSortBy() == "id") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th width="300">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "receivedOn")); ?>">Received on</a>
                <?php if ($html->getSortBy() == "receivedOn") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th width="300">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "processedOn")); ?>">Processed on</a>
                <?php if ($html->getSortBy() == "processedOn") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>Through proxy</th>
            <th>HostGroup</th>
            <th>Source</th>
            <th>#Installed pkgs</th>
            <th>#CVE</th>
            <th>#CVE with tag</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reports as $report) { ?>
            <tr>
                <td><?php echo $report->getId(); ?></td>
                <td><?php echo $report->getReceivedOn(); ?></td>
                <td><?php echo $report->getProcessedOn(); ?></td>
                <td><?php echo $report->getThroughProxy() == 0 ? "No" : $report->getProxyHostname(); ?></td>
                <td><?php echo $report->getHostGroup(); ?></td>
                <td><?php echo $report->getSource(); ?></td>
                <td><?php echo $report->getNumOfInstalledPkgs(); ?></td>
                <td><?php echo $report->getNumOfCves(); ?></td>
                <td><?php echo $report->getNumOfCvesWithTag(); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
