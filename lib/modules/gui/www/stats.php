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


$NAMES_DEFINITIONS = array (
    "savedReports"      => "Received reports which have been saved to database",
    "unsavedReports"    => "Received reports which haven't been saved to database",
    "checkedPkgs"       => "Checked packages",
    "vulnerablePkgs"    => "Checked packages which contains vulnerability"
);

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);


$html->addHtmlAttribute("title", "Statistics");
$stats = $pakiti->getManager("StatsManager")->listAll();

//---- Output HTML

$html->printHeader();

# Print table with oses
?>
<table class="tableList">
    <tr>
        <th>Name</th>
        <th>Value</th>
        <th></th>
    </tr>
    <?php
    $i = 0;
    foreach ($stats as $stat) {
        print "<tr class=\"a" . ($i & 1) . "\">\n<td>{$NAMES_DEFINITIONS[$stat->getName()]}</td><td>{$stat->getValue()}</td></tr>\n";
        $i++;
    }
    ?>
</table>
<?php $html->printFooter(); ?>
