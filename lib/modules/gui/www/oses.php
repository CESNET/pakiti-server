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
$html->checkPermission("oses");

$entries = Utils::getHttpPostVar("entries");
if ($entries == "") {
    $entries = 0;
}

if ($entries > 0) {
    for ($i = 0; $i < $entries; $i++) {
        if (Utils::getHttpPostVar("osGroup$i")) {
            $osGroup = $pakiti->getManager("OsGroupsManager")->getOsGroupById(Utils::getHttpPostVar("osGroup$i"));
            $osGroup->setRegex(Utils::getHttpPostVar("regex$i"));
            $pakiti->getManager("OsGroupsManager")->updateOsGroup($osGroup);
        }
    }
    $html->setMessage(sprintf("The changes have been saved.", $tagName));
}

$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);
$html->addHtmlAttribute("title", "List of all Oses");
$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups("name");
$oses = $pakiti->getManager("HostsManager")->getOses("name", $pageSize, $pageNum);
$osesCount = sizeof($oses);

//---- Output HTML

$html->printHeader();

# Print table with oses
?>
<table class="tableList">
    <tr>
        <th>OS Name</th>
        <th>Assigned groups</th>
        <th></th>
    </tr>
    <?php
    $i = 0;
    foreach ($oses as $os) {
        print "<tr class=\"a" . ($i & 1) . "\">\n<td>{$os->getName()}</td>";
        $actualOsGroups = $pakiti->getManager("OsGroupsManager")->getOsGroupsByOs($os);
        print "<td>" . implode(",", array_map(function ($osGroup) {
                return $osGroup->getName();
            }, $actualOsGroups)) . "</td>
    </td>\n
    </tr>\n";
        $i++;
    }
    ?>
</table>
</div>
<div class="space"></div><h1>List of all OS Groups</h1>
<form action="" name="osGroups" method="post">
    <table class="tableList">
        <tr>
            <th>OS Group</th>
            <th>Regular expression</th>
            <th></th>
        </tr>
        <?php
        $i = 0;
        foreach ($osGroups as $osGroup) {
            if ($osGroup->getName() !== "unknown") {
                print "<tr>
                <td>{$osGroup->getName()}</td>
                <td>
                <span class=\"slash\">/</span>
                <input type=\"text\" name=\"regex$i\" value=\"" . $osGroup->getRegex() . "\" placeholder=\"insert regular expression here\">
                <span class=\"slash\">/</span>
                </td>

                <td><input type=\"hidden\" name=\"osGroup$i\" value=\"" . $osGroup->getId() . "\" /></td>
            </tr>";
                $i++;
            }
        }
        ?>
    </table>
    <?php print "<td><input type=\"hidden\" name=\"entries\" value=\"" . $i . "\"></td>" ?>
    <input type="submit" value="Save changes">
</form>

<?php $html->printFooter(); ?>
