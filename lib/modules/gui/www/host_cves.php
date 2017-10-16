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
$html->checkPermission("host");

$_id = $html->getHttpGetVar("hostId", -1);
$_tag = $html->getHttpGetVar("tag", null);
if ($_tag == "true") {
    $_tag = true;
}

$host = $pakiti->getManager("HostsManager")->getHostById($_id, $html->getUserId());
if ($host == null) {
    $html->fatalError("Host with id " . $_id . " doesn't exist or access denied");
    exit;
}

$html->setTitle("Host: " . $host->getHostname());

$pkgs = $pakiti->getManager("PkgsManager")->getVulnerablePkgsForHost($host->getId(), $_tag);

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<h1><?php echo $host->getHostname(); ?></h1>
<ul class="nav nav-tabs">
    <li role="presentation"><a href="host.php?hostId=<?php echo $host->getId(); ?>">Detail</a></li>
    <li role="presentation"><a href="host_reports.php?hostId=<?php echo $host->getId(); ?>">Reports</a></li>
    <li role="presentation"><a href="host_packages.php?hostId=<?php echo $host->getId(); ?>">Packages</a></li>
    <li role="presentation" class="active"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>">CVEs</a></li>
</ul>

<br>

<div class="checkbox">
    <label>
        <input type="checkbox" onclick="location.href='?hostId=<?php echo $host->getId(); ?><?php if ($_tag !== true) echo '&tag=true'; ?>';"<?php if ($_tag === true) echo ' checked'; ?>> Only tagged CVEs
    </label>
</div>

<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="300">Name</th>
            <th width="300">Installed version</th>
            <th width="200">Architecture</th>
            <th>CVEs</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pkgs as $pkg) { ?>
            <?php $cvesNames = $pakiti->getManager("CvesManager")->getCvesNamesForPkgAndOs($pkg->getId(), $host->getOsId(), $_tag); ?>
            <tr>
                <td><?php echo $pkg->getName(); ?></td>
                <td><?php echo $pkg->getVersionRelease(); ?></td>
                <td><?php echo $pkg->getArch(); ?></td>
                <td>
                    <?php foreach ($cvesNames as $cveName) { ?>
                        <?php $tags = $pakiti->getManager("CveTagsManager")->getCveTagsByCveName($cveName); ?>
                        <a href="cve.php?cveName=<?php echo $cveName; ?>"<?php if(!empty($tags)) echo ' class="text-danger"'; ?>><?php echo $cveName; ?></a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
