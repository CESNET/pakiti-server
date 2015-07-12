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

$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);

$html->addHtmlAttribute("title", "List of all Archs");

$archs = $pakiti->getManager("HostsManager")->getArchs("name", $pageSize, $pageNum);
$archsCount = sizeof($archs);

//---- Output HTML

$html->printHeader();

# Print table with oses
?>

<div class="paging">
<?php print $html->paging($archsCount, $pageSize, $pageNum) ?>
</div>

<table class="tableList">
  <tr>
    <th>Name</th>
  </tr>
<?php
  $i = 0;
  foreach ($archs as $arch) {
    $i++;
    print "<tr class=\"a" . ($i & 1) . "\"><td>{$arch->getName()}</td></tr>\n";
  }
?>
</table>

<div class="paging">
<?php print $html->paging($archsCount, $pageSize, $pageNum) ?>
</div>

<?php $html->printFooter(); ?>
