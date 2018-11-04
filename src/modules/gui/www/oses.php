<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("oses");


$html->setTitle("Oses");
$html->setMenuActiveItem("oses.php");

$oses = $pakiti->getManager("OsesManager")->getOses();
$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();

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
<h1>Oses</h1>
<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="50%">Name</th>
            <th>Os groups</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($oses as $os) { ?>
            <tr>
                <td><a href="hosts.php?search=<?php echo $os->getName(); ?>"><?php echo $os->getName(); ?></a></td>
                <td>
                    <?php $osOsGroups = $pakiti->getManager("OsGroupsManager")->getOsGroupsByOs($os->getId()); ?>
                    <?php $n = 0; ?>
                    <?php foreach ($osOsGroups as $osOsGroup) { ?>
                        <?php printf("%s%s", ($n++ > 0) ? ", " : "", $osOsGroup->getName()); ?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<br>
<br>
<h1>OS groups</h1>
<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="50%">Name</th>
            <th>Regex</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($osGroups as $osGroup) { ?>
            <tr>
                <td><?php echo $osGroup->getName(); ?></td>
                <td><?php echo Config::$OS_GROUPS_MAPPING[$osGroup->getName()]; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
