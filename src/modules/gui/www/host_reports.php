<?php

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
$html->setDefaultSorting("receivedOn");
$html->setNumOfEntities($pakiti->getManager("ReportsManager")->getHostReportsCount($host->getId()));

$reports = $pakiti->getManager("ReportsManager")->getHostReports($host->getId(), $html->getSortBy(), $html->getPageSize(), $html->getPageNum());

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $host->getHostname(); ?></h1>
<ul class="nav nav-tabs">
    <li role="presentation"><a href="host.php?hostId=<?php echo $host->getId(); ?>">Detail</a></li>
    <li role="presentation" class="active"><a href="host_reports.php?hostId=<?php echo $host->getId(); ?>">Reports</a></li>
    <li role="presentation"><a href="host_packages.php?hostId=<?php echo $host->getId(); ?>">Packages</a></li>
    <li role="presentation"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>">CVEs</a></li>
</ul>

<br><br>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "id")); ?>">ID</a>
                <?php if ($html->getSortBy() == "id") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "receivedOn")); ?>">Received on</a>
                <?php if ($html->getSortBy() == "receivedOn") { ?>
                    <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "processedOn")); ?>">Processed on</a>
                <?php if ($html->getSortBy() == "processedOn") { ?>
                    <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
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
