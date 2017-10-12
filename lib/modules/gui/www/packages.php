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
$html->checkPermission("packages");

$_search = $html->getHttpGetVar("search", null);

$html->setTitle("Packages");
$html->setMenuActiveItem("packages.php");
$html->setNumOfEntities($pakiti->getManager("PkgsManager")->getPkgsCount(-1, $_search));

$pkgs = $pakiti->getManager("PkgsManager")->getPkgs($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), -1, $_search);

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<br>
<br>

<div class="row">
    <div class="col-md-4">
        <form>
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="<?php if ($_search != null) echo $_search; ?>" placeholder="Search term...">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                </span>
            </div>
        </form>
    </div>
    <div class="col-md-8"></div>
</div>

<br>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="300">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "name")); ?>">Name</a>
                <?php if ($html->getSortBy() == "name") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th width="300">Installed version</th>
            <th width="200">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "arch")); ?>">Architecture</a>
                <?php if ($html->getSortBy() == "arch") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>Hosts</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pkgs as $pkg) { ?>
            <?php $hosts = $pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, null, null, -1, null, $pkg->getId(), $html->getUserId(), false); ?>
            <tr>
                <td><?php echo $pkg->getName(); ?></td>
                <td><?php echo $pkg->getVersionRelease(); ?></td>
                <td><?php echo $pkg->getArch(); ?></td>
                <td>
                    <?php foreach ($hosts as $host) { ?>
                        <a href="host.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getHostname(); ?></a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
