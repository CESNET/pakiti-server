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

$hostId = $html->getHttpGetVar("hostId");
$hostname = $html->getHttpGetVar("hostname");
$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);
$sort = $html->getHttpGetVar("sortBy", "receivedOn");

$host = null;
if ($hostId != null) {
  $host =& $pakiti->getManager("HostsManager")->getHostById($hostId);
} else if ($hostname != null) {
  $host =& $pakiti->getManager("HostsManager")->getHostByHostname($hostname);
}

if ($host == null) {
  $html->fatalError("HostId nor Hostname was supplied");
}

$html->addHtmlAttribute("title", "Host: " . $host->getHostname());

$reports =& $pakiti->getManager("ReportsManager")->getHostReports($host, $sort, $pageSize, $pageNum);
$reportsCount =& $pakiti->getManager("ReportsManager")->getHostReportsCount($host);

//---- Output HTML

$html->printHeader();

?>
<div class="paging">
<?php print $html->paging($reportsCount, $pageSize, $pageNum) ?>
</div>

<table class="tableList">
	<tr>
		<th width="300"><a href="<?php print $html->getQueryString(array("sortBy" => "id")); ?>">ID</a></th>
		<th width="300"><a href="<?php print $html->getQueryString(array("sortBy" => "receivedOn")); ?>">Received on</a></th>
		<th width="300"><a href="<?php print $html->getQueryString(array("sortBy" => "processedOn")); ?>">Processed on</a></th>
		<th>Through proxy</th>
		<th>#Installed Pkgs</th>
		<th>#Pkgs to security update</th>
		<th>#Pkgs to update</th>
		<th>#CVE</th>
	</tr>
<?php 
  $i = 0;
  foreach ($reports as $report) { 
    $i++;
?>
	<tr class="a<?php print ($i & 1) ?>">
		<td><?php print$report->getId()?></td>
		<td><?php print$report->getReceivedOn()?></td>
		<td><?php print$report->getProcessedOn()?></td>
		<td><?php print$report->getThroughProxy() == 0 ? "No" : "{$report->getProxyHostname()}" ?></td>
		<td><?php print$report->getNumOfInstalledPkgs()?></td>
		<td><?php print$report->getNumOfVulnerablePkgsSec()?></td>
		<td><?php print$report->getNumOfVulnerablePkgsNorm()?></td>
		<td><?php print$report->getNumOfCves()?></td>
	</tr>
<?php } ?>
</table>

<div class="paging">
<?php print $html->paging($reportsCount, $pageSize, $pageNum) ?>
</div>

<?php $html->printFooter(); ?>
