<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);

$html->checkPermission("cve");

$_hostGroupId = $html->getHttpGetVar("hostGroupId", -1);
$_tag = $html->getHttpGetVar("tag", null);
if ($_tag == "true") {
	$_tag = true;
}
$_cveName = $html->getHttpGetVar("cveName", null);
$_activity = $html->getHttpGetVar("activity", null);

$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
$allCveNames = $pakiti->getManager("CvesManager")->getCvesNames();
if ($_cveName != null)
	$cveNames[] = $_cveName;
else {
	/* XXX add a count call */
        $start = microtime(true);
	$cveNames = $pakiti->getManager("CvesManager")->getCvesNamesForHosts(-1, -1, $_tag, $_hostGroupId, $_activity);
	$html->setNumOfEntities(count($cveNames));
	$start = microtime(true);
	$cveNames = $pakiti->getManager("CvesManager")->getCvesNamesForHosts($html->getPageSize(), $html->getPageNum(), $_tag, $_hostGroupId, $_activity);
}

$html->setTitle("Monitored Vulnerabilities");
$html->setMenuActiveItem("vulnerabilities.php");

$tagNames = $pakiti->getManager("CveTagsManager")->getTagNames();
$activity = array("Last 24 hours" => "24h", "Last 2 days" => "2d", "Last week" => "1w", "Inactive 48 hours" => "-48h", "Inactive 7 days" => "-7d");

include(realpath(dirname(__FILE__)) . "/../common/header.php");
?>

<form>
    <div class="row background-grey">
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-md-8 col-lg-6 col-sm-10"><h3></h3></div>
        <div class="col-md-3 col-lg-4 col-sm-2"></div>
    </div>
    <div class="row background-grey">
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-md-8 col-lg-6 col-sm-10">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="cveName">CVE</label>
                        <select class="form-control" name="cveName" id="cveName" onchange="submit();">
                            <option value="">All detected</option>
                            <?php foreach ($allCveNames as $cveName) { ?>
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
        <div class="col-md-1 col-lg-2 col-sm-0"></div>
        <div class="col-sm-12">
            <br><br>
        </div>
    </div>
</form>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
  <thead><tr>
    <th>CVE</th>
    <th>Affected Hosts</th>
    <th class="col-md-1"></th> <!-- Probably not the right thing to do -->
  </tr></thead>
  <tbody>
    <?php
       
	foreach ($cveNames as $cveName) { 
		$start = microtime(true);
		$hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, $cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId());
		/* XXX This shouldn't be needed actually, cveNames is supposed to contain only detected vulns*/
		/* we shortent page content here .... */
/*
		if ($hostsCount == 0)
			continue; */

	?>
	  <tr>
	        <td><a href="cve.php?cveName=<?php echo $cveName;?>"><?php echo $cveName;?></a></td>
		<td><a href="hosts.php?cveName=<?php echo $cveName;?>&hostGroupId=<?php echo $_hostGroupId;?>&activity=<?php echo $_activity;?>"><?php echo $hostsCount; ?></a></td>
		<td>
                    <button type="button" class="btn btn-success btn-block"
			onclick="window.location.href='exceptions.php?cveName=<?php echo $cveName;?>'">Exceptions</button>
                </td>
	  </tr>
	<?php } ?>
  </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
