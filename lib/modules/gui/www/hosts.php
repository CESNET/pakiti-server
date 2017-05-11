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

$userId = $html->getUserId();

// Process operations
switch ($html->getHttpGetVar("op")) {
  case "del":
    // Delete host
    $hostId = $html->getHttpGetVar("hostId");

    $host = $pakiti->getManager("HostsManager")->getHostById($hostId, $userId);
    if ($host != null) {
      $pakiti->getManager("HostsManager")->deleteHost($host);
    } else {
      $html->setError("Cannot delete host, host with id $hostId doesn't exist or access denied");
    }
    break;
}

$html->addHtmlAttribute("title", "List of hosts");

$firstLetter = $html->getHttpGetVar("firstLetter", 'a');
$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);
$sort = $html->getHttpGetVar("sortBy", "hostname");

$hostsCount = $pakiti->getManager("HostsManager")->getHostsCount($userId);

// if displaying all
if($firstLetter == "all"){
  $firstLetter = null;
}
$hosts = $pakiti->getManager("HostsManager")->getHosts($sort, -1, -1, $firstLetter, $userId);


$displayAllLink = $html -> getQueryString(array("firstLetter" => "all"));

//---- Output HTML

$html->printHeader();

# Print table with hosts
?>
<p>Total hosts: <?php print $hostsCount?> (<a href="<?php print $displayAllLink ?>">display all</a>)</p>

<div class="paging">
<?php print $html->alphabeticPaging() ?>
</div>

<?php $html->printHosts($hosts); ?>

<div class="paging">
<?php print $html->alphabeticPaging() ?>
</div>

<?php $html->printFooter(); ?>
