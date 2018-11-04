<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("stats");

$html->setTitle("Statistics");
$html->setMenuActiveItem("stats.php");

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2"></div>
    <div class="col-md-5"></div>
</div>

<br>
<br>
<h1>Current statistics</h1>
<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="600">Name</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Number of hosts</td>
            <td><?php echo $html->getPakiti()->getManager("HostsManager")->getHostsCount(); ?></td>
        </tr>
        <tr>
            <td>Number of host groups</td>
            <td><?php echo $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsCount(); ?></td>
        </tr>
        <tr>
            <td>Number of reports</td>
            <td><?php echo $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount(); ?></td>
        </tr>
        <tr>
            <td>Number of CVEs</td>
            <td><?php echo sizeof($html->getPakiti()->getManager("CvesManager")->getCvesNames()); ?></td>
        </tr>
        <tr>
            <td>Number of packages</td>
            <td><?php echo $html->getPakiti()->getManager("PkgsManager")->getPkgsCount(); ?></td>
        </tr>
        <tr>
            <td>Number of CVE tags</td>
            <td><?php echo $html->getPakiti()->getManager("CveTagsManager")->getCveTagsCount(); ?></td>
        </tr>
        <tr>
            <td>Number of CVE exceptions</td>
            <td><?php echo $html->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsCountByCveName(); ?></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<h1>Cumulative statistics</h1>
<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="600">Name</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total number of reports that were stored in the database</td>
            <td>
                <?php $savedReports = $html->getPakiti()->getManager("StatsManager")->get("savedReports"); ?>
                <?php echo ($savedReports == null) ? 0 : $savedReports->getValue(); ?>
            </td>
        </tr>
        <tr>
            <td>Total number of reports that were not stored in the database</td>
            <td>
                <?php $unsavedReports = $html->getPakiti()->getManager("StatsManager")->get("unsavedReports"); ?>
                <?php echo ($unsavedReports == null) ? 0 : $unsavedReports->getValue(); ?>
            </td>
        </tr>
        <tr>
            <td>Total number of all received packages in all reports</td>
            <td>
                <?php $checkedPkgs = $html->getPakiti()->getManager("StatsManager")->get("checkedPkgs"); ?>
                <?php echo ($checkedPkgs == null) ? 0 : $checkedPkgs->getValue(); ?>
            </td>
        </tr>
        <tr>
            <td>Total number of reports when the host did not send new data</td>
            <td>
                <?php $sameReports = $html->getPakiti()->getManager("StatsManager")->get("sameReports"); ?>
                <?php echo ($sameReports == null) ? 0 : $sameReports->getValue(); ?>
            </td>
        </tr>
    </tbody>
</table>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
