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

/* TEMPORARY SENT FORM */
if(Utils::getHttpPostVar("tag-create-form") == "sent"){

    $cveName = Utils::getHttpPostVar("cve-name");

    $tagName = Utils::getHttpPostVar("cve-tag");

    $tagReason = Utils::getHttpPostVar("tag-reason");

    $tag = new Tag();
    $tag->setName($tagName);
    $tag -> setReason($tagReason);
    $cves = $pakiti ->getManager("CveDefsManager")->getCvesByName($cveName);


    try{

     foreach($cves as $cve){
        $pakiti->getManager("TagsManager")->assignTagToCve($cve, $tag);
     }

    $html -> setMessage(sprintf("Tag %s has been associated to %s.", $tagName, $cveName));

    } catch (Exception $e){
        $html -> setMessage(sprintf("%s", $e->getMessage()));
    }



}

$html->addHtmlAttribute("title", "CVE Tags overview");
$html->printHeader();
$cveNames = $pakiti -> getManager("CveDefsManager")->getCveNames();
$cvesWithTags = $pakiti -> getManager("CveDefsManager")->getCvesWithTags();

?>

        <table class="tableList">
            <tr align="top">
                <th>Add new entry</th>
            </tr>
            <form action="" method="post">
            <td>

                <label for="cve-name">CVE: </label>

                <select name="cve-name">
                    <?php foreach($cveNames as $cveName){?>
                        <option> <?php print $cveName; ?> </option>
                    <?php } ?>
                </select>



                <label for="cve-tag">CVE Tag: </label>
                <select name="cve-tag">
                    <option value="Critical" selected>Critical</option>
                    <option value="High">High</option>
                </select>



                <label for="tag-reason">Reason: </label>
                <input type="text" name="tag-reason" size="50">



                <input type="submit" value="Add">

                <input type="hidden" name="tag-create-form" value="sent" />
            </td>

            <tr>
                <th>All CVE tags</th>
            </tr>
        </table>
    </form>




<?php
$html->printCveTags($cvesWithTags);
$html->printFooter();
?>