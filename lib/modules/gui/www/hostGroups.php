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
$html->checkPermission("hostGroups");

$userId = $html->getUserId();

$html->addHtmlAttribute("title", "List of host groups");

$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);
$sort = $html->getHttpGetVar("sortBy", "name");

$hostGroupsCount = $pakiti->getManager("HostGroupsManager")->getHostGroupsCount($userId);
$hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups($sort, $pageSize, $pageNum, $userId);

//---- Output HTML

$html->printHeader();

# Print table with hosts
?>
<p>Total host groups: <?php print $hostGroupsCount?></p>

<div class="paging">
<?php print $html->paging($hostGroupsCount, $pageSize, $pageNum) ?>
</div>

<table class="tableList">
	<tr>
		<th width="250"><a href="<?php print $html->getQueryString(array ("sortBy" => "name")); ?>">Host Group Name</a></th>
		<th>Hosts Count</th>
	</tr>
<?php 
  $i = 0;
  foreach ($hostGroups as $hostGroup) { 
    $i++;

    $hostsCount = $pakiti->getManager("HostGroupsManager")->getHostsCount($hostGroup);
?>
	<tr class="a<?php print ($i & 1) ?>">
		<td><a href="hostGroup.php?hostGroupId=<?php print $hostGroup->getId() ?>"><?php print $hostGroup->getName(); ?></a></td>
		<td><?php print $hostsCount ?></td>
	</tr>
<?php } ?>
</table>

<div class="paging">
<?php print $html->paging($hostGroupsCount, $pageSize, $pageNum) ?>
</div>

<?php $html->printFooter(); ?>
