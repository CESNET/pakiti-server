<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("cve");


$html->setTitle("CVE");

$cveName = $html->getHttpGetVar("cveName", null);
$vulnerabilities = $html->getPakiti()->getManager("VulnerabilitiesManager")->getVulnerabilities($cveName);
$exceptionsCount = $html->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsCountByCveName($cveName);

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $cveName; ?></h1>
<a href="exceptions.php?cveName=<?php echo $cveName; ?>">(<?php echo $exceptionsCount; ?> exception<?php if($exceptionsCount!= 1) echo 's'; ?>)</a>

<br><br><br>
Defined by:
<?php
   $vds_defs_list = $html->getPakiti()->getManager("CvesManager")->getSubSourceDefNames($cveName);
   $vds_defs = join(", ", $vds_defs_list);
   echo $vds_defs;
   echo ' (See <a href="vds.php">VDS</a>)'
?>

<br><br><br>
<?php if(preg_match("/^CVE-(.*)-(.*)$/", $cveName, $values) === 1) { ?>
    <a href="https://bugzilla.redhat.com/show_bug.cgi?id=<?php echo $cveName; ?>" target="_blank">Link to the RedHat Bugzilla</a><br>
    <a href="https://security-tracker.debian.org/tracker/<?php echo $cveName; ?>" target="_blank">Link to the Debian Security Tracker</a><br>
    <a href="https://www.suse.com/security/cve/<?php echo $cveName; ?>/" target="_blank">Link to the SUSE Security</a><br>
    <a href="https://people.canonical.com/~ubuntu-security/cve/<?php echo $values[1]; ?>/<?php echo $cveName; ?>.html" target="_blank">Link to Ubuntu Security Tracker</a><br>
<?php } ?>
<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>Name</th>
            <th>Version</th>
            <th>Architecture</th>
            <th>OsGroup</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vulnerabilities as $vulnerability) { ?>
            <tr>
                <td><?php echo $vulnerability->getName(); ?></td>
                <td><?php echo $vulnerability->getOperator() . " " .$vulnerability->getVersion() . "-" . $vulnerability->getRelease(); ?></td>
                <td><?php echo $vulnerability->getArchName(); ?></td>
                <td><?php echo $vulnerability->getOsGroupName(); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
