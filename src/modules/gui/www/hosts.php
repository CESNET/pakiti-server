<?php

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
$_activity = $html->getHttpGetVar("activity", null);
$_listTaggedCves = $html->getHttpGetVar("listTaggedCves", false);
if ($_listTaggedCves !== false) {
    $_listTaggedCves = true;
}

// Process operations
switch ($html->getHttpPostVar("act")) {
  case "delete":
    $host = $pakiti->getManager("HostsManager")->getHostById($html->getHttpPostVar("id"), $html->getUserId());
    if ($host != null) {
        $pakiti->getManager("HostsManager")->deleteHost($host->getId());
    } else {
        $html->setError("Cannot delete host, host with id " . $html->getHttpPostVar("id") . " doesn't exist or access denied");
    }
    break;
}

$hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount($_search, $_cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId());

$html->setTitle("List of hosts");
$html->setMenuActiveItem("hosts.php");
$html->setDefaultSorting("lastReport");
$html->setNumOfEntities($hostsCount);

$hosts = $html->getPakiti()->getManager("HostsManager")->getHosts($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), $_search, $_cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId());

$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
$cveNames = $pakiti->getManager("CvesManager")->getCvesNames(true);
$tagNames = $pakiti->getManager("CveTagsManager")->getTagNames();
$activity = array("Last 24 hours" => "24h", "Last 2 days" => "2d", "Last week" => "1w", "Inactive 48 hours" => "-48h", "Inactive 7 days" => "-7d");

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<div class="row">
    <div class="col-md-12 text-center">
        Shortcuts:
            <?php foreach (Config::$GUI_HOSTS_FAVORITE_FILTERS as $key => $value) { ?>
                <button class="btn btn-info" type="button" onclick="location.href='?<?php echo $value; ?>';">
                    <?php echo $key; ?>
                </button>
            <?php } ?>
    </div>
</div>
<br>
<form>
    <div class="row background-grey">
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-md-8 col-lg-6 col-sm-10"><h3>Search Term:</h3></div>
        <div class="col-md-3 col-lg-4 col-sm-2"></div>
    </div>
    <div class="row background-grey">
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-md-8 col-lg-6 col-sm-10">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <input type="text" class="form-control" name="search" id="search" value="<?php if ($_search != null) echo $_search; ?>">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="cveName">CVE</label>
                        <select class="form-control" name="cveName" id="cveName" onchange="submit();">
                            <option value="">All</option>
                            <?php foreach ($cveNames as $cveName) { ?>
                                <option value="<?php echo $cveName; ?>"<?php if ($_cveName === $cveName) echo ' selected'; ?>><?php echo $cveName; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="tag">CVE Tag</label>
                        <select class="form-control" name="tag" id="tag" onchange="submit();">
                            <option value="">All</option>
                            <option value="true"<?php if ($_tag === true) echo ' selected'; ?>>Any tag</option>
                            <?php foreach ($tagNames as $tagName) { ?>
                                <option value="<?php echo $tagName; ?>"<?php if ($_tag === $tagName) echo ' selected'; ?>><?php echo $tagName; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="activity">Activity</label>
                        <select class="form-control" name="activity" id="activity" onchange="submit();">
                            <option value="">All</option>
                            <?php foreach ($activity as $key => $value) { ?>
                                <option value="<?php echo $value; ?>"<?php if ($value == $_activity) echo ' selected'; ?>><?php echo $key; ?></option>
                            <?php } ?>
                            <?php if(!in_array($_activity, $activity) && $_activity != null){ ?>
                                <option value="<?php echo $_activity; ?>" selected><?php echo $_activity; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="hostGroupId">Host group</label>
                        <select class="form-control" name="hostGroupId" id="hostGroupId" onchange="submit();">
                            <option value="">All</option>
                            <?php foreach ($hostGroups as $hostGroup) { ?>
                                <option value="<?php echo $hostGroup->getId(); ?>"<?php if ($hostGroup->getId() == $_hostGroupId) echo ' selected'; ?>><?php echo $hostGroup->getName(); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-lg-2 col-sm-2">
            <button class="btn btn-primary btn-block" type="submit">
                <span class="glyphicon glyphicon-search" aria-hidden="true"></span> Search
            </button>
        </div>
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-sm-12">
            <br><br>
        </div>
    </div>
</form>
<div>
    <h4><?php echo $hostsCount; ?> host<?php if($hostsCount != 1) echo 's'; ?> found</h4>
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
                <input name="listTaggedCves" type="checkbox" title="show list of CVEs" onchange="location.href='<?php echo $html->getQueryString(array("listTaggedCves" => ($_listTaggedCves ? "" : "true"))); ?>';" <?php if($_listTaggedCves) echo ' checked'; ?>>
                <?php if($_listTaggedCves && $_tag !== null && $_tag !== true) { ?>
                    <br><small class="text-normal">(only <?php echo $_tag; ?>)</small>
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
                        <?php echo $hostGroup->getName(); ?> 
                    <?php } ?> 
                </td>
                <td><?php echo $host->getOsName(); ?></td>
                <td>
                    <span style="color: #<?php echo substr(md5($host->getKernel()), 0, 6); ?>;"><?php echo $host->getKernel(); ?></span>
                </td>
                <td><?php echo $host->getArchName(); ?></td>
                <td>
                    <a href="host_packages.php?hostId=<?php echo $host->getId(); ?>"><?php echo $report->getNumOfInstalledPkgs(); ?></a>
                </td>
                <td>
                    <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getNumOfCves(); ?></a>
                </td>
                <td>
                    <?php if ($_listTaggedCves) { ?>
                        <?php foreach ($html->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($host->getId(), ($_tag == null ? true : $_tag)) as $cveName) { ?>
                            <a href="cve.php?cveName=<?php echo $cveName; ?>" class="text-danger"><?php echo $cveName; ?></a><br>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"<?php if ($host->getNumOfCvesWithTag() > 0) echo ' class="text-danger"'; ?>><?php echo $host->getNumOfCvesWithTag(); ?></a>
                    <?php } ?>
                </td>
                <td>
                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>"><?php echo $reportsCount; ?></a>
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
