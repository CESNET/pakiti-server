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

$_id = $html->getHttpGetVar("hostId", -1);

$host = $pakiti->getManager("HostsManager")->getHostById($_id, $html->getUserId());
if ($host == null) {
    $html->fatalError("Host with id " . $_id . " doesn't exist or access denied");
    exit;
}

$html->setTitle("Host: " . $host->getHostname());

$report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
$reportsCount = $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host->getId());

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $host->getHostname(); ?></h1>
<ul class="nav nav-tabs">
    <li role="presentation" class="active"><a href="host.php?hostId=<?php echo $host->getId(); ?>">Detail</a></li>
    <li role="presentation"><a href="reports.php?hostId=<?php echo $host->getId(); ?>">Reports</a></li>
    <li role="presentation"><a href="packages.php?hostId=<?php echo $host->getId(); ?>">Packages</a></li>
    <li role="presentation"><a href="cves.php?hostId=<?php echo $host->getId(); ?>">CVEs</a></li>
</ul>

<br><br>

<table class="table table-striped table-condensed">
    <tr>
        <td>HostGroup</td>
        <td>
            <?php foreach ($hostGroups as $hostGroup) { ?> 
                <a href="hosts.php?hostGroupId=<?php echo $hostGroup->getId(); ?>"><?php echo $hostGroup->getName(); ?> </a>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td width="300">Operating system</td>
        <td><?php echo $host->getOsName(); ?></td>
    </tr>
    <tr>
        <td>Architecture</td>
        <td><?php echo $host->getArchName(); ?></td>
    </tr>
    <tr>
        <td>Kernel</td>
        <td><?php echo $host->getKernel(); ?></td>
    </tr>
    <tr>
        <td>Domain</td>
        <td><?php echo $host->getDomainName(); ?></td>
    </tr>
    <tr>
        <td>Reporter hostname</td>
        <td><?php echo $host->getReporterHostname(); ?></td>
    </tr>
    <tr>
        <td>Reporter IP</td>
        <td><?php echo $host->getReporterIp(); ?></td>
    </tr>
    <tr>
        <td>Installed packages</td>
        <td><a href="packages.php?hostId=<?php echo $host->getId(); ?>"><?php echo $report->getNumOfInstalledPkgs(); ?></a></td>
    </tr>
    <tr>
        <td>Cves</td>
        <td><a href="cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getNumOfCves(); ?></a></td>
    </tr>
    <tr>
        <td>Cves with Tag</td>
        <td><a href="cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"><?php echo $host->getNumOfCvesWithTag(); ?></a></td>
    </tr>
    <tr>
        <td>Last report received on</td>
        <td><?php echo $report->getReceivedOn(); ?></td>
    </tr>
    <tr>
        <td>Reports</td>
        <td><a href="reports.php?hostId=<?php echo $host->getId(); ?>"><?php echo $reportsCount; ?></a></td>
    </tr>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
