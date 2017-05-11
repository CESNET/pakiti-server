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
require(realpath(dirname(__FILE__)) . '/../../../common/DefaultModule.php');
require(realpath(dirname(__FILE__)) . '/../../../modules/vds/VdsModule.php');
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("vds");

$html->addHtmlAttribute("title", "Vulnerability Definition System");

$vds = new VdsModule($pakiti);

/* TEMPORARY SENT FORM */
switch(Utils::getHttpVar("act")){
  case "create":
    $defName = Utils::getHttpPostVar("defName");
    $defUri = Utils::getHttpPostVar("defUri");
    @list ($sourceId, $subSourceId) = explode(' ', Utils::getHttpPostVar("ids"));

    $source = $vds->getSourceById($sourceId);
    $subSource = $source->getSubSourceById($subSourceId);

    $html->setMessage("Adding subsource definition for the subsource ".$subSource->getName()."");

    $subSourceDef = new SubSourceDef();
    $subSourceDef->setName($defName);
    $subSourceDef->setUri($defUri);
    $subSourceDef->setSubSourceId($subSource->getId());
    $subSource->addSubSourceDef($subSourceDef);
    break;
  case "delete":
    @list ($sourceId, $subSourceId) = explode(' ', Utils::getHttpPostVar("ids"));
    $subSourceDefId = Utils::getHttpPostVar("subSourceDefId");

    $source = $vds->getSourceById($sourceId);
    $subSource = $source->getSubSourceById($subSourceId);
    $subSourceDef = new SubSourceDef();
    $subSourceDef->setId($subSourceDefId);
    $subSource->removeSubSourceDef($subSourceDef);
    break;
  default:
    break;
}

$sources = $vds->getSources();
//---- Output HTML

$html->printHeader();

# Print table with oses
?>

<table class="tableList">
  <tr>
    <th>Add new entry</th>
  </tr>
  <tr>
    <td>
      <form action="" name="vds" method="post">
        <label for="defName">Name: </label>
        <input type="text" name="defName" size="20">
        &nbsp;&nbsp;

        <label for="defUri">URI: </label>
        <input type="text" name="defUri" size="60">
        &nbsp;&nbsp;

        <label for="ids">SubSource: </label>
        <select name="ids">
          <?php foreach($sources as $source){?>
            <?php foreach($source->getSubSources() as $subSource){?>
              <option value="<?php print $source->getId()." ".$subSource->getId(); ?>"> <?php print $subSource->getName() . " [" . $source->getName() . "]"; ?> </option>
            <?php } ?>
          <?php } ?>
        </select>
        &nbsp;&nbsp;

        <input type="submit" value="Add">
        <input type="hidden" name="act" value="create" />
        <input type="hidden" name="subSourceDefId" value="-1" />
      </form>
    </td>
  </tr>
</table>
<br><br>

<table class="tableList">
  <tr>
    <th>VDS Sources</th>
  </tr>
<?php
  $i = 0;
  foreach ($sources as $source) {
    $i++;
    print "<tr class=\"a" . ($i & 1) . "\"><td><b>{$source->getName()}</b></td></tr>\n";
    foreach ($source->getSubSources() as $subSource) {
      $i++;
      print "<tr class=\"a" . ($i & 1) . "\"><td>&nbsp;<font color=\"red\"><b>{$subSource->getName()}</b></font></td></tr>\n";
      foreach ($subSource->getSubSourceDefs() as $subSourceDef) {
	$i++;
    print "";
        print "<tr class=\"a" . ($i & 1) . "\"><td><div>
	<div>&nbsp;&nbsp;<u>Name:</u> {$subSourceDef->getName()}</div>
	<div>&nbsp;&nbsp;<u>URI:</u> {$subSourceDef->getUri()}</div>
	<div>&nbsp;&nbsp;<u>Last check:</u> {$subSourceDef->getLastChecked()}</div>
  <div><span style='color: #002BFF; font-weight: bold; cursor: pointer;' onclick=\"document.vds.act.value='delete'; document.vds.ids.value='" . $source->getId() . " " . $subSource->getId() . "';
  document.vds.subSourceDefId.value='" . $subSourceDef->getId() . "'; document.vds.submit();\" ><a>[remove]</a></span></div>
	</div></td></tr>\n";
      }
    }
  }
?>
</table>

<?php $html->printFooter(); ?>
