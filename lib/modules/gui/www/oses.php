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
$entries = Utils::getHttpPostVar("entries");
if ($entries == "") {
    $entries = 0;
}

if ($entries > 0) {
    for ($i = 0; $i < $entries; $i++) {
        if (isset($_POST["ososgroup$i"])) {
            $data = explode(' ', Utils::getHttpPostVar("ososgroup$i"));
            $os = $pakiti->getManager("OsGroupsManager")->getOsById($data[0]);
            # data[0] - osId
            # data[1] - osGroupId
            if ($data[1] !== "N/A") {
                $osGroup = $pakiti->getManager("OsGroupsManager")->getOsGroupById($data[1]);
                $pakiti->getManager("OsGroupsManager")->assignOsToOsGroup($os, $osGroup);
            } else {
                $pakiti->getManager("OsGroupsManager")->removeOsFromOsGroups($os);
            }
        }
    }
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
<form action="" method="post" name="os_osgroup_form">
    <table class="tableList">
        <tr>
            <th>OS Name</th>
            <th>OS Group</th>
            <th></th>
        </tr>
        <?php
        $i = 0;
        foreach ($oses as $os) {
            print "<tr class=\"a" . ($i & 1) . "\">
    <td>{$os->getName()}</td>
    <td>\n<select name=\"" . "ososgroup$i" . "\" style=\"width: 300px;\">\n";
            $actualOsOsGroup = $pakiti->getManager("OsGroupsManager")->getOsGroupByOsId($os->getId());
            print "<option value=\"" . $os->getId() . " " . "N/A" . "\"";
            if ($actualOsOsGroup == null) print " class=\"bold\" selected";
            print ">N/A</option>\n";
            foreach ($osGroups as $osGroup) {
                print "<option";
                if ($actualOsOsGroup !== null) {
                    if ($actualOsOsGroup->getName() === $osGroup->getName()) print " class=\"bold\" selected";
                }
                print " value=\"" . $os->getId() . " " . $osGroup->getId() . "\">" . $osGroup->getName() . "</option>\n";
            }
            print "</select>\n
    </td>\n
    </tr>\n";
            $i++;
        }
        ?>
    </table>
    <?php print "<td><input type=\"hidden\" name=\"entries\" value=\"" . $i . "\"></td>" ?>
    <input type="submit" value="Save">
</form>
<?php $html->printFooter(); ?>
