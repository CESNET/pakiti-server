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
$html->checkPermission("cveTags");

$html->addHtmlAttribute("title", "CVE Tags");

/* TEMPORARY SENT FORM */
switch (Utils::getHttpPostVar("act")) {
    case "create":
        $cveName = Utils::getHttpPostVar("cveName");
        $tagName = Utils::getHttpPostVar("tagName");
        if ($cveName !== "N/A" && $tagName !== "N/A") {
            // Check if exists
            $id = $pakiti->getManager("CveTagsManager")->getCveTagIdByCveNameTagName($cveName, $tagName);
            if ($id == -1) {
                $cveTag = new CveTag();
                $cveTag->setCveName($cveName);
                $cveTag->setTagName($tagName);
                $cveTag->setReason(Utils::getHttpPostVar("reason"));
                $cveTag->setInfoUrl(Utils::getHttpPostVar("infoUrl"));
                $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
            } else {
                $html->setMessage("Cve with this tag already exists");
            }
        }
        break;
    case "update":
        $id = Utils::getHttpPostVar("id");
        $enabled = Utils::getHttpPostVar("enabled");
        $cveTag = $pakiti->getManager("CveTagsManager")->getCveTagById($id);
        $cveTag->setEnabled($enabled);
        $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
        break;
    case "delete":
        $id = Utils::getHttpPostVar("id");
        $pakiti->getManager("CveTagsManager")->deleteCveTagById($id);
        break;
}

$cveNames = $pakiti->getManager("CveDefsManager")->getCveNames();
$tagNames = Config::$TAGS;

$sort = $html->getHttpGetVar("sortBy", null);
$cveTags = $pakiti->getManager("CveTagsManager")->getCveTags($sort);

//---- Output HTML
$html->printHeader();
?>

<table class="tableList">
  <tr>
    <th>Add new entry</th>
  </tr>
  <tr>
    <td>
      <form action="" name="cveTag" method="post">

        <label for="cveName">CVE: </label>
        <select name="cveName">
          <option value="N/A" selected></option>
<?php foreach ($cveNames as $cveName) {
    print "<option value=\"" . $cveName . "\">" . $cveName . "</option>";
} ?>
        </select>
        &nbsp;&nbsp;

        <label for="tagName">Tag: </label>
        <select name="tagName">
          <option value="N/A" selected></option>
<?php foreach ($tagNames as $tag) {
    print "<option value=\"" . $tag . "\">" . $tag . "</option>";
} ?>
        </select>
        &nbsp;&nbsp;

        <label for="reason">Reason: </label>
        <input type="text" name="reason" size="50">
        &nbsp;&nbsp;

        <label for="reason">Info URL: </label>
        <input type="text" name="infoUrl" size="50">
        &nbsp;&nbsp;

        <input type="submit" value="Add">

        <input type="hidden" name="act" value="create" />
        <input type="hidden" name="id" value="" />
        <input type="hidden" name="enabled" value="" />
      </form>
    </td>
  </tr>
</table>


<table class="tableList">

<?php

    print "
      <tr>
        <th><a href=\"" . $html->getQueryString(array("sortBy" => "enabled")) . "\">Enabled</a></th>
        <th><a href=\"" . $html->getQueryString(array("sortBy" => "cveName")) . "\">CVE</a></th>
        <th><a href=\"" . $html->getQueryString(array("sortBy" => "tagName")) . "\">Tag</a></th>
        <th>Reason</th>
        <th>InfoUrl</th>
        <th><a href=\"" . $html->getQueryString(array("sortBy" => "modifier")) . "\">Modifier</a></th>
        <th><a href=\"" . $html->getQueryString(array("sortBy" => "timestamp")) . "\">Timestamp</a></th>
        <th></th>
      </tr>
    ";

    $i = 0;
    foreach ($cveTags as $cveTag) {
        $i++;

        $delete = "<span class=\"delete-button\" onclick=\"
            document.cveTag.act.value='delete';
            document.cveTag.id.value='".$cveTag->getId()."';
            document.cveTag.submit();
            \" ><a>Delete</a></span>";

        $enabled = "<input type=\"checkbox\" onClick=\"
            document.cveTag.act.value='update';
            document.cveTag.id.value='".$cveTag->getId()."';
            document.cveTag.enabled.value=this.checked;
            document.cveTag.submit();
            \"".(($cveTag->isEnabled()) ? " checked" : "").">";

        print "
          <tr class=\"a" . ($i & 1) . "\">
            <td>" . $enabled . "</td>
            <td>" . $cveTag->getCveName() . "</td>
            <td>" . $cveTag->getTagName() . "</td>
            <td>" . $cveTag->getReason() . "</td>
            <td><a href='" . $cveTag->getInfoUrl() . "' target='_blank'>" . $cveTag->getInfoUrl() . "</a></td>
            <td>" . $cveTag->getModifier() . "</td>
            <td>" . $cveTag->getTimestamp() . "</td>
            <td>" . $delete . "</td>
          </tr>
        ";
    }
?>

</table>

<?php
    $html->printFooter();
?>
