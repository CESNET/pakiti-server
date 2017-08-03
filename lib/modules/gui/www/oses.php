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


// Process operations
switch (Utils::getHttpPostVar("act")) {
    case "update":
        $osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();
        foreach ($osGroups as $osGroup) {
            $osGroup->setRegex(Utils::getHttpPostVar("regex_" . $osGroup->getId()));
            $pakiti->getManager("OsGroupsManager")->storeOsGroup($osGroup);
        }
        break;
    default:
        break;
}


$html->setTitle("Oses mapping");
$html->setMenuActiveItem("oses.php");

$oses = $pakiti->getManager("OsesManager")->getOses();
$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2">
        <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#edit">Edit OS mapping</button>
    </div>
    <div class="col-md-5"></div>
</div>

<br>
<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="300">Name</th>
            <th>Os groups</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($oses as $os) { ?>
            <tr>
                <td><?php echo $os->getName(); ?></td>
                <td>
                    <?php $osOsGroups = $pakiti->getManager("OsGroupsManager")->getOsGroupsByOsName($os->getName()); ?>
                    <?php foreach ($osOsGroups as $osOsGroup) { ?>
                        <?php echo $osOsGroup->getName(); ?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="editLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editLabel">Edit OS mapping</h4>
            </div>
            <div class="modal-body">
                <form name="editForm" method="post">
                    <input type="hidden" name="act" value="update">
                    <?php foreach ($osGroups as $osGroup) { ?>
                        <?php if ($osGroup->getName() !== "unknown") { ?>
                            <div class="form-group">
                                <label for="regex_<?php echo $osGroup->getId(); ?>"><?php echo $osGroup->getName(); ?></label>
                                <input type="text" class="form-control" name="regex_<?php echo $osGroup->getId(); ?>" id="regex_<?php echo $osGroup->getId(); ?>" value="<?php echo $osGroup->getRegex(); ?>" placeholder="insert regular expression here">
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
