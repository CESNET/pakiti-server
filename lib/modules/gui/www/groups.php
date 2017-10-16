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
$html->checkPermission("groups");

// Process operations
switch (Utils::getHttpPostVar("act")) {
  case "edit":
    $hostGroupId = Utils::getHttpPostVar("id");
    $hostGroup = $pakiti->getManager("HostGroupsManager")->getHostGroupById($hostGroupId, $html->getUserId());
    if ($hostGroup != null) {
        $hostGroup->setUrl(Utils::getHttpPostVar("url"));
        $hostGroup->setContact(Utils::getHttpPostVar("contact"));
        $hostGroup->setNote(Utils::getHttpPostVar("note"));
        $pakiti->getManager("HostGroupsManager")->storeHostGroup($hostGroup);
    } else {
        $html->setError("Cannot update hostGroup, hostGroup with id " . $hostGroupId . " doesn't exist or access denied");
    }
    break;
}


$html->setTitle("Host groups");
$html->setMenuActiveItem("groups.php");
$html->setDefaultSorting("name");
$html->setNumOfEntities($html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsCount($html->getUserId()));

$hostgroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), $html->getUserId());

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2"></div>
    <div class="col-md-5"></div>
</div>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "name")); ?>">Name</a>
                <?php if ($html->getSortBy() == "name") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>#Hosts</th>
            <th>#VulnerableHosts</th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "url")); ?>">URL</a>
                <?php if ($html->getSortBy() == "url") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "contact")); ?>">Contact</a>
                <?php if ($html->getSortBy() == "contact") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "note")); ?>">Note</a>
                <?php if ($html->getSortBy() == "note") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hostgroups as $hostgroup) { ?>
            <?php $hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, null, $hostgroup->getId(), null, -1, $html->getUserId()); ?>
            <?php $taggedHostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, true, $hostgroup->getId(), null, -1, $html->getUserId()); ?>
            <tr>
                <td><?php echo $hostgroup->getName(); ?></td>
                <td><a href="hosts.php?hostGroupId=<?php echo $hostgroup->getId(); ?>"><?php echo $hostsCount; ?></a></td>
                <td><a href="hosts.php?hostGroupId=<?php echo $hostgroup->getId(); ?>&tag=true"><?php echo $taggedHostsCount; ?></a></td>
                <td><?php echo $hostgroup->getUrl(); ?></td>
                <td><?php echo $hostgroup->getContact(); ?></td>
                <td><?php echo $hostgroup->getNote(); ?></td>
                <td>
                    <button type="button" class="btn btn-xs btn-success"
                        onclick="document.editForm.id.value='<?php echo $hostgroup->getId(); ?>';
                            document.editForm.name.value='<?php echo $hostgroup->getName(); ?>';
                            document.editForm.url.value='<?php echo $hostgroup->getUrl(); ?>';
                            document.editForm.contact.value='<?php echo $hostgroup->getContact(); ?>';
                            document.editForm.note.value='<?php echo $hostgroup->getNote(); ?>';"
                        data-toggle="modal" data-target="#edit">Edit</button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>


<form action="" name="form" method="post">
    <input type="hidden" name="act" />
    <input type="hidden" name="id" />
</form>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure to delete this host?</h4>
            </div>
            <div class="modal-body text-right">
                <button type="button" class="btn btn-danger" onclick="document.form.submit();">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="editLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editLabel">Edit host group</h4>
            </div>
            <div class="modal-body">
                <form name="editForm" method="post">
                    <input type="hidden" name="act" value="edit">
                    <input type="hidden" name="id">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" disabled>
                    </div>
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="text" class="form-control" name="url" id="url">
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact</label>
                        <input type="text" class="form-control" name="contact" id="contact">
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        <input type="text" class="form-control" name="note" id="note">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
