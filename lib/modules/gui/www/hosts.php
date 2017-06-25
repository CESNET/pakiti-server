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
$html->checkPermission("hosts");


// Process operations
switch (Utils::getHttpPostVar("act")) {
  case "delete":
    $host = $pakiti->getManager("HostsManager")->getHostById(Utils::getHttpPostVar("id"), $html->getUserId());
    if ($host != null) {
        $pakiti->getManager("HostsManager")->deleteHost($host);
    } else {
        $html->setError("Cannot delete host, host with id " . $hostId . " doesn't exist or access denied");
    }
    break;
}


$html->setTitle("List of hosts");
$html->setMenuActiveItem("hosts.php");
$html->setNumOfEntities($html->getPakiti()->getManager("HostsManager")->getHostsCount($html->getHttpGetVar("search", null), $html->getHttpGetVar("hostGroupId", -1), $html->getUserId()));

$hosts = $html->getPakiti()->getManager("HostsManager")->getHosts($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), $html->getHttpGetVar("search", null), $html->getHttpGetVar("hostGroupId", -1), $html->getUserId());
$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
$hostGroup = new HostGroup(); $hostGroup->setName("All host groups"); $hostGroups[] = $hostGroup;

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-3">
        <form>
            <input type="hidden" name="hostGroupId" value="<?php echo $html->getHttpGetVar("hostGroupId", -1); ?>" />
            <div class="input-group">
                <input name="search" type="text" class="form-control" placeholder="Search by hostname..." value="<?php echo $html->getHttpGetVar("search", ""); ?>">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                    </button>
                </span>
            </div>
        </form>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-3">
        <div class="text-right">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle btn-block" type="button" id="hostGroups" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <?php foreach ($hostGroups as $hostGroup) { ?> 
                    <?php if ($hostGroup->getId() == $html->getHttpGetVar("hostGroupId", -1)) { ?>
                        <?php $hostCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, $hostGroup->getId(), $html->getUserId()); ?>
                            <div class="text-left"><?php echo $hostGroup->getName(); ?> (<?php echo $hostCount; ?> host<?php if($hostCount != 1) echo 's'; ?>)
                        <?php } ?>
                    <?php } ?>
                    <span class="caret"></span></div>
                </button>
                <ul class="dropdown-menu dropdown-menu-right col-xs-12" aria-labelledby="hostGroups">
                    <?php foreach ($hostGroups as $hostGroup) { ?> 
                        <?php if ($hostGroup->getId() != $html->getHttpGetVar("hostGroupId", -1)) { ?>
                            <?php $hostCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, $hostGroup->getId(), $html->getUserId()); ?>
                            <li>
                                <a href="<?php echo $html->getQueryString(array("hostGroupId" => $hostGroup->getId())); ?>">
                                    <?php echo $hostGroup->getName(); ?> (<?php echo $hostCount; ?> host<?php if($hostCount != 1) echo 's'; ?>)
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "hostname")); ?>">Hostname</a>
                <?php if ($html->getSortBy() == "hostname") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>HostGroup</th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "os")); ?>">Os</a>
                <?php if ($html->getSortBy() == "os") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "kernel")); ?>">Kernel</a>
                <?php if ($html->getSortBy() == "kernel") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "arch")); ?>">Architecture</a>
                <?php if ($html->getSortBy() == "arch") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>#InstalledPkgs</th>
            <th>#CVEs</th>
            <th>#CVEs with Tag</th>
            <th>#Reports</th>
            <th>LastReport</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hosts as $host) { ?> 
            <?php $report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId()); ?>
            <?php $hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host); ?>
            <?php $cveCount = $html->getPakiti()->getManager("CveDefsManager")->getCvesCount($host); ?>
            <?php $cveWithTagCount = $html->getPakiti()->getManager("CveDefsManager")->getCvesCount($host, true); ?>
            <?php $reportsCount = $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host); ?>

            <tr>
                <td>
                    <a href="host.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getHostname(); ?></a>
                </td>
                <td>
                    <?php foreach ($hostGroups as $hostGroup) { ?> 
                        <a href="hosts.php?hostGroupId=<?php echo $hostGroup->getId(); ?>"><?php echo $hostGroup->getName(); ?> </a>
                    <?php } ?> 
                </td>
                <td><?php echo $host->getOs()->getName() ?></td>
                <td>
                    <span style="color: #<?php echo substr(md5($host->getKernel()), 0, 6); ?>;"><?php echo $host->getKernel(); ?></span>
                </td>
                <td><?php echo $host->getArch()->getName(); ?></td>
                <td>
                    <a href="packages.php?hostId=<?php echo $host->getId(); ?>"><?php echo $report->getNumOfInstalledPkgs(); ?></a>
                </td>
                <td>
                    <a href="cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $cveCount; ?></a>
                </td>
                <td>
                    <span<?php if ($cveWithTagCount > 0) echo ' class="text-danger"'; ?>><?php echo $cveWithTagCount; ?></span>
                </td>
                <td>
                    <a href="reports.php?hostId=<?php echo $host->getId(); ?>"><?php echo $reportsCount; ?></a>
                </td>
                <td>
                    <span<?php if (strtotime("now") - strtotime($report->getReceivedOn()) > (2 * (60 * 60 * 24))) echo ' class="text-warning"'; ?>><?php echo $report->getReceivedOn(); ?></span>
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger"
                        onclick="document.form.act.value='delete'; document.form.id.value='<?php echo $host->getId(); ?>';"
                        data-toggle="modal" data-target="#myModal">Delete</button>
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


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
