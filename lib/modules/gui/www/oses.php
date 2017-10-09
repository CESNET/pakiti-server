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
                    <?php foreach ($osOsGroups as $osOsGroup) { ?>
                        <?php echo $osOsGroup->getName(); ?>
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
