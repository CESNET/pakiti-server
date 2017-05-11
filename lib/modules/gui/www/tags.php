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
$html->checkPermission("tags");

/* TEMPORARY SENT FORM */
if(Utils::getHttpPostVar("tag-create-form") == "sent"){
    
    $tagName = Utils::getHttpPostVar("tag-name");
    $tagDescription = Utils::getHttpPostVar("tag-description");
    
    $tag = new Tag();
    $tag -> setName($tagName);
    $tag -> setDescription($tagDescription);    
    $pakiti -> getDao("Tag") -> create($tag);
    $html -> setMessage(sprintf("Tag %s created.", $tagName));

}
/* TEMPORARY SENT FORM END */


// Ordering
$pageNum = $html->getHttpGetVar("pageNum", 0);
$pageSize = $html->getHttpGetVar("pageSize", HtmlModule::$DEFAULTPAGESIZE);

// Setting the title
$html->addHtmlAttribute("title", "Tags overview");

// Loading the tags
$tags = $pakiti->getManager("TagsManager")->getTags("name", $pageSize, $pageNum);
$tagsCount = sizeof($tags);


//---- Output HTML

$html->printHeader();


?>
<h2>Create tag</h2>
<form action="" method="post">
    <div class="form-item">
        <label for="tag-name">Name: </label>
        <input type="text" name="tag-name" />
    </div>
    <div class="form-item">
        <label for="tag-description">Description: </label>
        <input type="text" name="tag-description" />
    </div>
    <div class="form-buttons">  
        <input type="submit" value="Create" />
    </div>
    <input type="hidden" name="tag-create-form" value="sent" />
</form>
        


<h2>List of all tags</h2>

<p>Total tags: <?php print $tagsCount?></p>

<div class="paging">
<?php print $html->paging($tagsCount, $pageSize, $pageNum) ?>
</div>

<table class="tableList">
  <tr>
    <th>Name</th>
    <th>Description</th>
  </tr>
<?php
  $i = 0;
  foreach ($tags as $tag) {
    $i++;
    $line = ($i & 1);
    print "<tr class=\"a{$line}\">
                <td>{$tag->getName()}</td>
                <td>{$tag->getDescription()}</td>
            </tr>\n";
  }
?>
</table>

<div class="paging">
<?php print $html->paging($tagsCount, $pageSize, $pageNum) ?>
</div>

<?php $html->printFooter(); ?>
