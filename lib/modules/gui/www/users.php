<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

$html->addHtmlAttribute("title", "User management");

/* TEMPORARY SENT FORM */
switch (Utils::getHttpVar("act")) {
  case "store":
    if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_AUTOCREATE || Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
        break;
    }
    $user = new User();
    $user->setUid(Utils::getHttpPostVar("uid"));
    $user->setName(Utils::getHttpPostVar("name"));
    $user->setEmail(Utils::getHttpPostVar("email"));
    $pakiti->getManager("UsersManager")->storeUser($user);
    break;
  case "update":
    if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
        break;
    }
    $id = Utils::getHttpPostVar("id");
    $admin = Utils::getHttpPostVar("admin");
    $user = $pakiti->getManager("UsersManager")->getUserById($id);
    $user->setAdmin($admin);
    $pakiti->getManager("UsersManager")->storeUser($user);
    break;
  case "delete":
    if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
        break;
    }
    $id = Utils::getHttpPostVar("id");
    $pakiti->getManager("UsersManager")->deleteUser($id);
    break;
  case "add":
    $id = Utils::getHttpPostVar("id");
    $hostId = Utils::getHttpPostVar("hostId");
    $hostGroupId = Utils::getHttpPostVar("hostGroupId");
    if ($hostId != -1) {
        $pakiti->getManager("UsersManager")->assignHostToUser($id, $hostId);
    }
    if ($hostGroupId != -1) {
        $pakiti->getManager("UsersManager")->assignHostGroupToUser($id, $hostGroupId);
    }
    break;
  case "remove":
    $id = Utils::getHttpPostVar("id");
    $hostId = Utils::getHttpPostVar("hostId");
    $hostGroupId = Utils::getHttpPostVar("hostGroupId");
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

$users = $pakiti->getManager("UsersManager")->getUsers();
$hosts = $pakiti->getManager("HostsManager")->getHosts();
$hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups();
//---- Output HTML

$html->printHeader();
?>

<?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_AUTOCREATE && Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) {
    ?>

<table class="tableList">
  <tr>
    <th>Store user</th>
  </tr>
  <tr>
    <td>
      <form action="" method="post">
        <label for="uid">UID: </label>
        <input type="text" name="uid" size="20">
        &nbsp;&nbsp;

        <label for="name">Name: </label>
        <input type="text" name="name" size="20">
        &nbsp;&nbsp;

        <label for="email">Email: </label>
        <input type="text" name="email" size="20">
        &nbsp;&nbsp;

        <input type="submit" value="Store">
        <input type="hidden" name="act" value="store" />
      </form>
    </td>
  </tr>
</table>
<br><br>

<?php 
} ?>

<form action="" name="user" method="post">
  <input type="hidden" name="act" />
  <input type="hidden" name="id" />
  <input type="hidden" name="admin" />
</form>

<table class="tableList">
  <tr>
    <th>Add permission</th>
  </tr>
  <tr>
    <td>
      <form action="" name="permission" method="post">
        <label for="id">User: </label>
        <select name="id">
          <option value="-1"></option>
<?php foreach ($users as $user) {
    print "<option value=\"".$user->getId()."\">".$user->getUid()."</option>";
} ?>
        </select>
        &nbsp;&nbsp;

        <label for="hostId">Host: </label>
        <select name="hostId">
          <option value="-1"></option>
<?php foreach ($hosts as $host) {
    print "<option value=\"".$host->getId()."\">".$host->getHostname()."</option>";
} ?>
        </select>
        &nbsp;&nbsp;

        <label for="hostGroupId">HostGroup: </label>
        <select name="hostGroupId">
          <option value="-1"></option>
<?php foreach ($hostGroups as $hostGroup) {
    print "<option value=\"".$hostGroup->getId()."\">".$hostGroup->getName()."</option>";
} ?>
        </select>
        &nbsp;&nbsp;
        <input type="submit" value="Add">
        <input type="hidden" name="act" value="add" />
      </form>
    </td>
  </tr>
</table>
<br><br>

<table class="tableList">
    <tr>
        <th>UID</th>
        <th>Name</th>
        <th>Email</th>
        <th>CreatedAt</th>
        <th>Admin</th>
        <th>Hosts</th>
        <th>HostGroups</th>
        <th></th>
    </tr>
<?php
  $i = 0;
  foreach ($users as $user) {

      # Prepare assigned hosts to user
      $hostsArray = array();
      foreach ($pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, $user->getId(), true) as $host) {
          $hostsArray[] = "<span class=\"delete-button\" onclick=\"
            document.permission.act.value='remove';
            document.permission.hostId.value='".$host->getId()."';
            document.permission.id.value='".$user->getId()."';
            document.permission.submit();
            \" ><a title=\"remove permission\">".$host->getHostname()."</a></span>";
      }
      $hosts = implode(", ", $hostsArray);

      # Prepare assigned hostGroups to user
      $hostGroupsArray = array();
      foreach ($pakiti->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $user->getId()) as $hostGroup) {
          $hostGroupsArray[] = "<span class=\"delete-button\" onclick=\"
            document.permission.act.value='remove';
            document.permission.hostGroupId.value='".$hostGroup->getId()."';
            document.permission.id.value='".$user->getId()."';
            document.permission.submit();
            \" ><a title=\"remove permission\">".$hostGroup->getName()."</a></span>";
      }
      $hostGroups = implode(", ", $hostGroupsArray);

      # Prepare delete button
      $delete = "";
      if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) {
          $delete = "<span class=\"delete-button\" onclick=\"
            document.user.act.value='delete';
            document.user.id.value='".$user->getId()."';
            document.user.submit();
            \" ><a>Delete</a></span>";
      }

      # Prepare admin button
      $admin = "<input type=\"checkbox\" ".(($user->isAdmin()) ? " checked" : "")." disabled>";
      if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) {
          $admin = "<input type=\"checkbox\" onClick=\"
            document.user.act.value='update';
            document.user.id.value='".$user->getId()."';
            document.user.admin.value=this.checked;
            document.user.submit();
            \"".(($user->isAdmin()) ? " checked" : "").">";
      }

      $i++;
      print "
        <tr class=\"a" . ($i & 1) . "\">
          <td>".$user->getUid()."</td>
          <td>".$user->getName()."</td>
          <td>".$user->getEmail()."</td>
          <td>".$user->getCreatedAt()."</td>
          <td>".$admin."</td>
          <td>".$hosts."</td>
          <td>".$hostGroups."</td>
          <td>".$delete."</td>
        </tr>
      ";
  }
?>
</table>

<?php $html->printFooter(); ?>
