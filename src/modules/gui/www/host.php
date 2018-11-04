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

$report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
$reportsCount = $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host->getId());

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $host->getHostname(); ?></h1>
<ul class="nav nav-tabs">
    <li role="presentation" class="active"><a href="host.php?hostId=<?php echo $host->getId(); ?>">Detail</a></li>
    <li role="presentation"><a href="host_reports.php?hostId=<?php echo $host->getId(); ?>">Reports</a></li>
    <li role="presentation"><a href="host_packages.php?hostId=<?php echo $host->getId(); ?>">Packages</a></li>
    <li role="presentation"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>">CVEs</a></li>
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
        <td><a href="host_packages.php?hostId=<?php echo $host->getId(); ?>"><?php echo $report->getNumOfInstalledPkgs(); ?></a></td>
    </tr>
    <tr>
        <td>Cves</td>
        <td><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getNumOfCves(); ?></a></td>
    </tr>
    <tr>
        <td>Cves with Tag</td>
        <td><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"><?php echo $host->getNumOfCvesWithTag(); ?></a></td>
    </tr>
    <tr>
        <td>Last report received on</td>
        <td><?php echo $report->getReceivedOn(); ?></td>
    </tr>
    <tr>
        <td>Reports</td>
        <td><a href="host_reports.php?hostId=<?php echo $host->getId(); ?>"><?php echo $reportsCount; ?></a></td>
    </tr>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
