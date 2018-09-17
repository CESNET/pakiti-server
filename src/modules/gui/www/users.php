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
$html->checkPermission("users");


// Process operations
switch ($html->getHttpPostVar("act")) {
    case "store":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_AUTOCREATE || Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot create users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $user = new User();
        $user->setUid($html->getHttpPostVar("uid"));
        $user->setName($html->getHttpPostVar("name"));
        $user->setEmail($html->getHttpPostVar("email"));
        $pakiti->getManager("UsersManager")->storeUser($user);
        break;
    case "update":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot edit users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $id = $html->getHttpPostVar("id");
        $admin = $html->getHttpPostVar("admin");
        $user = $pakiti->getManager("UsersManager")->getUserById($id);
        $user->setAdmin($admin == "true");
        $pakiti->getManager("UsersManager")->storeUser($user);
        break;
    case "delete":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot delete users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $id = $html->getHttpPostVar("id");
        $pakiti->getManager("UsersManager")->deleteUser($id);
        break;
    case "add":
        $id = $html->getHttpPostVar("id");
        $hostId = $html->getHttpPostVar("hostId");
        $hostGroupId = $html->getHttpPostVar("hostGroupId");
        if ($hostId != -1) {
            $pakiti->getManager("UsersManager")->assignHostToUser($id, $hostId);
        }
        if ($hostGroupId != -1) {
            $pakiti->getManager("UsersManager")->assignHostGroupToUser($id, $hostGroupId);
        }
        break;
    case "remove":
        $id = $html->getHttpPostVar("id");
        $hostId = $html->getHttpPostVar("hostId");
        $hostGroupId = $html->getHttpPostVar("hostGroupId");
        if ($hostId != -1) {
            $pakiti->getManager("UsersManager")->unassignHostToUser($id, $hostId);
        }
        if ($hostGroupId != -1) {
            $pakiti->getManager("UsersManager")->unassignHostGroupToUser($id, $hostGroupId);
        }
        break;
    default:
        break;
}


$html->setTitle("User management");
$html->setMenuActiveItem("users.php");

$users = $pakiti->getManager("UsersManager")->getUsers();
$hosts = $pakiti->getManager("HostsManager")->getHosts();
$hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups();

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2">
        <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_AUTOCREATE && Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
            <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#add">Add user</button>
        <?php } ?>
    </div>
    <div class="col-md-5"></div>
</div>

<br>
<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>UID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Timestamp</th>
            <th>Admin</th>
            <th>Hosts</th>
            <th>HostGroups</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user) { ?>
            <?php $userHosts = $pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, null, null, -1, null, -1, $user->getId(), true); ?>
            <?php $userHostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $user->getId()); ?>

            <tr>
                <td><?php echo $user->getUid(); ?></td>
                <td><?php echo $user->getName(); ?></td>
                <td><?php echo $user->getEmail(); ?></td>
                <td><?php echo $user->getTimestamp(); ?></td>
                <td>
                    <?php if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) { ?>
                        <input type="checkbox"<?php if ($user->isAdmin()) echo ' checked'; ?> disabled>
                    <?php } else { ?>
                        <input type="checkbox" onClick="
                            document.form.act.value='update';
                            document.form.id.value='<?php echo $user->getId(); ?>';
                            document.form.admin.value=this.checked;
                            document.form.submit();"<?php if ($user->isAdmin()) echo ' checked'; ?>>
                    <?php } ?>
                </td>
                <td>
                    <?php foreach ($userHosts as $userHost) { ?>
                        <a href="host.php?hostId=<?php echo $userHost->getId(); ?>"><?php echo $userHost->getHostname(); ?></a>
                        <a onclick="document.editForm.act.value='remove';
                            document.editForm.hostGroupId.value='-1';
                            document.editForm.hostId.value='<?php echo $userHost->getId(); ?>';
                            document.editForm.id.value='<?php echo $user->getId(); ?>';
                            document.editForm.submit();" class="pointer text-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a><br>
                    <?php } ?>
                </td>
                <td>
                    <?php foreach ($userHostGroups as $userHostGroup) { ?>
                        <a href="hosts.php?hostGroupId=<?php echo $userHostGroup->getId(); ?>"><?php echo $userHostGroup->getName(); ?></a>
                        <a onclick="document.editForm.act.value='remove';
                            document.editForm.hostId.value='-1';
                            document.editForm.hostGroupId.value='<?php echo $userHostGroup->getId(); ?>';
                            document.editForm.id.value='<?php echo $user->getId(); ?>';
                            document.editForm.submit();" class="pointer text-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a><br>
                    <?php } ?>
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-success"
                        onclick="document.editForm.uid.value='<?php echo $user->getUid(); ?>'; document.editForm.id.value='<?php echo $user->getId(); ?>';"
                        data-toggle="modal" data-target="#edit">Edit</button>
                </td>
                <td>
                    <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
                        <button type="button" class="btn btn-xs btn-danger"
                            onclick="document.form.act.value='delete'; document.form.id.value='<?php echo $user->getId(); ?>';"
                            data-toggle="modal" data-target="#myModal">Delete</button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<form action="" name="form" method="post">
    <input type="hidden" name="act" />
    <input type="hidden" name="id" />
    <input type="hidden" name="admin" />
</form>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure to delete this user?</h4>
            </div>
            <div class="modal-body text-right">
                <button type="button" class="btn btn-danger" onclick="document.form.submit();">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="addLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addLabel">Store user</h4>
            </div>
            <div class="modal-body">
                <form name="addForm" method="post">
                    <input type="hidden" name="act" value="store">
                    <div class="form-group">
                        <label for="uid">UID</label>
                        <input type="text" class="form-control" name="uid" id="uid">
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" name="email" id="email">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                * If you use an existing UID, the user will be edited.
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="editLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editLabel">Edit permissions</h4>
            </div>
            <div class="modal-body">
                <form name="editForm" method="post">
                    <input type="hidden" name="act" value="add">
                    <input type="hidden" name="id">
                    <div class="form-group">
                        <label for="uid">UID</label>
                        <input type="text" class="form-control" name="uid" id="uid" disabled>
                    </div>
                    <div class="form-group">
                        <label for="hostId">Host</label>
                        <select class="form-control" name="hostId" id="hostId">
                            <option value="-1" selected></option>
                            <?php foreach ($hosts as $host) { ?>
                                <option value="<?php echo $host->getId(); ?>"><?php echo $host->getHostname(); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hostGroupId">HostGroup</label>
                        <select class="form-control" name="hostGroupId" id="hostGroupId">
                            <option value="-1" selected></option>
                            <?php foreach ($hostGroups as $hostGroup) { ?>
                                <option value="<?php echo $hostGroup->getId(); ?>"><?php echo $hostGroup->getName(); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                * Click on host or hostgroup to delete permission.
            </div>
        </div>
    </div>
</div>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
