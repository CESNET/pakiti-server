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

$_hostGroupId = $html->getHttpGetVar("hostGroupId", -1);
$_search = $html->getHttpGetVar("search", null);
$_tag = $html->getHttpGetVar("tag", null);
if ($_tag == "true") {
    $_tag = true;
}
$_cveName = $html->getHttpGetVar("cveName", null);
$_activeIn = $html->getHttpGetVar("activeIn", null);
$_listTaggedCves = $html->getHttpGetVar("listTaggedCves", false);
if ($_listTaggedCves !== false) {
    $_listTaggedCves = true;
}

// Process operations
switch (Utils::getHttpPostVar("act")) {
  case "delete":
    $host = $pakiti->getManager("HostsManager")->getHostById(Utils::getHttpPostVar("id"), $html->getUserId());
    if ($host != null) {
        $pakiti->getManager("HostsManager")->deleteHost($host->getId());
    } else {
        $html->setError("Cannot delete host, host with id " . Utils::getHttpPostVar("id") . " doesn't exist or access denied");
    }
    break;
  case "edit":
    $hostGroup = $pakiti->getManager("HostGroupsManager")->getHostGroupById(Utils::getHttpPostVar("id"), $html->getUserId());
    if ($hostGroup != null) {
        $hostGroup->setName(Utils::getHttpPostVar("name"));
        $hostGroup->setUrl(Utils::getHttpPostVar("url"));
        $hostGroup->setContact(Utils::getHttpPostVar("contact"));
        $hostGroup->setNote(Utils::getHttpPostVar("note"));
        $pakiti->getManager("HostGroupsManager")->storeHostGroup($hostGroup);
    } else {
        $html->setError("Cannot delete hostGroup, hostGroup with id " . Utils::getHttpPostVar("id") . " doesn't exist or access denied");
    }
    break;
}


$html->setTitle("List of hosts");
$html->setMenuActiveItem("hosts.php");
$html->setDefaultSorting("lastReport");
$html->setNumOfEntities($html->getPakiti()->getManager("HostsManager")->getHostsCount($_search, $_cveName, $_tag, $_hostGroupId, $_activeIn, $html->getUserId()));

$hosts = $html->getPakiti()->getManager("HostsManager")->getHosts($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), $_search, $_cveName, $_tag, $_hostGroupId, $_activeIn, $html->getUserId());
$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
$hostGroupTmp = new HostGroup(); $hostGroupTmp->setName("All host groups"); $hostGroups[] = $hostGroupTmp;
$selectedHostGroup = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupById($_hostGroupId);

$cveNames = $pakiti->getManager("CvesManager")->getCvesNames(true);
$tagNames = $pakiti->getManager("CveTagsManager")->getTagNames();
// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-5">
        <form>
            <input type="hidden" name="hostGroupId" value="<?php echo $_hostGroupId; ?>" />
            <div class="input-group">
                <input name="search" type="text" class="form-control" placeholder="Search by hostname..." value="<?php if ($_search != null) echo $_search; ?>" style="width: 28%;">

                <select class="form-control" name="cveName" id="cveName" onchange="submit();" style="width: 28%;">
                    <option value="">CVE (no matter)</option>
                    <?php foreach ($cveNames as $cveName) { ?>
                        <option value="<?php echo $cveName; ?>"<?php if ($_cveName === $cveName) echo ' selected'; ?>><?php echo $cveName; ?></option>
                    <?php } ?>
                </select>

                <select class="form-control" name="tag" id="tag" onchange="submit();" style="width: 28%;">
                    <option value="">Tag (no matter)</option>
                    <option value="true"<?php if ($_tag === true) echo ' selected'; ?>>Any tag</option>
                    <?php foreach ($tagNames as $tagName) { ?>
                        <option value="<?php echo $tagName; ?>"<?php if ($_tag === $tagName) echo ' selected'; ?>><?php echo $tagName; ?></option>
                    <?php } ?>
                </select>

                <input name="activeIn" type="text" class="form-control" placeholder="Active in" title="for example: inactive 2 days -> -2d, active this week -> +1w" value="<?php if ($_activeIn != null) echo $_activeIn; ?>" style="width: 16%;">

                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                    </button>
                </span>
            </div>
            <div class="checkbox">
                <label>
                    <input name="listTaggedCves" type="checkbox" <?php if($_listTaggedCves) echo ' checked'; ?> onchange="submit();"> Show tagged CVEs
                </label>
            </div>
            <br>
            <?php foreach (Config::$GUI_HOSTS_FAVORITE_FILTERS as $name => $value) { ?>
                <button class="btn btn-default" type="button" onclick="location.href='?<?php echo $value; ?>';">
                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span> <?php echo $name; ?>
                </button>
            <?php } ?>
        </form>
    </div>
    <div class="col-md-2">
        <?php if($_hostGroupId != -1) { ?>
            <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#edit">Edit host group</button>
        <?php } ?>
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-3">
        <div class="text-right">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle btn-block" type="button" id="hostGroups" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <?php foreach ($hostGroups as $hostGroup) { ?> 
                    <?php if ($hostGroup->getId() == $_hostGroupId) { ?>
                        <?php $hostCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, null, $hostGroup->getId(), null, $html->getUserId()); ?>
                            <div class="text-left"><?php echo $hostGroup->getName(); ?> (<?php echo $hostCount; ?> host<?php if($hostCount != 1) echo 's'; ?>)
                        <?php } ?>
                    <?php } ?>
                    <span class="caret"></span></div>
                </button>
                <ul class="dropdown-menu dropdown-menu-right col-xs-12" aria-labelledby="hostGroups">
                    <?php foreach ($hostGroups as $hostGroup) { ?> 
                        <?php if ($hostGroup->getId() != $_hostGroupId) { ?>
                            <?php $hostCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, null, $hostGroup->getId(), null, $html->getUserId()); ?>
                            <li>
                                <a href="<?php echo $html->getQueryString(array("hostGroupId" => $hostGroup->getId(), "pageNum" => 0)); ?>">
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

<?php if($selectedHostGroup != null) { ?>
    <div class="row">
        <div class="col-md-12 text-right">
            <?php echo ($selectedHostGroup->getUrl() != "") ? '<a href="'.$selectedHostGroup->getUrl() . '" target="_blank">'.$selectedHostGroup->getUrl() . '</a><br>' : '' ?>
            <?php echo ($selectedHostGroup->getContact() != "") ? $selectedHostGroup->getContact() . "<br>" : "" ?>
            <?php echo ($selectedHostGroup->getNote() != "") ? $selectedHostGroup->getNote() . "<br>" : "" ?>
        </div>
    </div>
<?php } ?>

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
            <th>HostGroups</th>
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
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "cves")); ?>">#CVEs</a>
                <?php if ($html->getSortBy() == "cves") { ?>
                    <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <?php if ($_listTaggedCves) { echo 'TaggedCVEs'; } else { ?>
                    <a href="<?php echo $html->getQueryString(array("sortBy" => "taggedCves")); ?>">#TaggedCVEs</a>
                    <?php if ($html->getSortBy() == "taggedCves") { ?>
                        <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                    <?php } ?>
                <?php } ?>
            </th>
            <th>#Reports</th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "lastReport")); ?>">LastReport</a>
                <?php if ($html->getSortBy() == "lastReport") { ?>
                    <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hosts as $host) { ?> 
            <?php $report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId()); ?>
            <?php $hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host); ?>
            <?php $reportsCount = $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host->getId()); ?>

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
                    <a href="cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getNumOfCves(); ?></a>
                </td>
                <td>
                    <?php if ($_listTaggedCves) { ?>
                        <?php foreach ($html->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($host->getId(), true) as $cveName) { ?>
                            <a href="cve.php?cveName=<?php echo $cveName; ?>" class="text-danger"><?php echo $cveName; ?></a><br>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"<?php if ($host->getNumOfCvesWithTag() > 0) echo ' class="text-danger"'; ?>><?php echo $host->getNumOfCvesWithTag(); ?></a>
                    <?php } ?>
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

<?php if($selectedHostGroup != null) { ?>
    <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="editLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="editLabel">Edit host groupp</h4>
                </div>
                <div class="modal-body">
                    <form name="editForm" method="post">
                        <input type="hidden" name="act" value="edit">
                        <input type="hidden" name="id" value="<?php echo $_hostGroupId; ?>">
                        <input type="hidden" name="name" value="<?php echo $selectedHostGroup->getName(); ?>">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" value="<?php echo $selectedHostGroup->getName(); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="url">URL</label>
                            <input type="text" class="form-control" name="url" id="url" value="<?php echo $selectedHostGroup->getUrl(); ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact</label>
                            <input type="text" class="form-control" name="contact" id="contact" value="<?php echo $selectedHostGroup->getContact(); ?>">
                        </div>
                        <div class="form-group">
                            <label for="note">Note</label>
                            <input type="text" class="form-control" name="note" id="note" value="<?php echo $selectedHostGroup->getNote(); ?>">
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-success">Save</button>
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
<?php } ?>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
