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
            <td><?php echo sizeof($html->getPakiti()->getManager("CveDefsManager")->getCveNames()); ?></td>
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
