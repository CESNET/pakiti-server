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


// Process operations
switch ($html->getHttpPostVar("act")) {
    case "create":
        $cveName = $html->getHttpPostVar("cveName");
        $tagName = $html->getHttpPostVar("tagName");
        if ($cveName != "N/A" && $tagName != "N/A") {
            // Check if exists
            $id = $pakiti->getManager("CveTagsManager")->getCveTagIdByCveNameTagName($cveName, $tagName);
            if ($id == -1) {
                $cveTag = new CveTag();
                $cveTag->setCveName($cveName);
                $cveTag->setTagName($tagName);
                $cveTag->setReason($html->getHttpPostVar("reason"));
                $cveTag->setInfoUrl($html->getHttpPostVar("infoUrl"));
                $cveTag->setModifier($html->getUsername());
                $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
            } else {
                $html->setError("Cve [" . $cveName . "] with tag [" . $tagName . "] already exists");
            }
        }
        break;
    case "update":
        $id = $html->getHttpPostVar("id");
        $cveTag = $pakiti->getManager("CveTagsManager")->getCveTagById($id);
        if ($cveTag != null) {
            $cveTag->setEnabled($html->getHttpPostVar("enabled") == "true");
            $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
        } else {
            $html->setError("CveTag [" . $id . "] doesn't exists");
        }
        break;
    case "delete":
        $id = $html->getHttpPostVar("id");
        $cveTag = $pakiti->getManager("CveTagsManager")->getCveTagById($id);
        if ($cveTag != null) {
            $pakiti->getManager("CveTagsManager")->deleteCveTagById($id);
        } else {
            $html->setError("CveTag [" . $id . "] doesn't exists");
        }
        break;
    default:
        break;
}


$html->setTitle("CVE Tags");
$html->setMenuActiveItem("tags.php");
$html->setNumOfEntities($html->getPakiti()->getManager("CveTagsManager")->getCveTagsCount());

$cveTags = $html->getPakiti()->getManager("CveTagsManager")->getCveTags($html->getSortBy(), $html->getPageSize(), $html->getPageNum());

$cveNames = $pakiti->getManager("CvesManager")->getCvesNames();
$tagNames = Config::$TAGS;

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2">
        <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#add">Add CVE tag</button>
    </div>
    <div class="col-md-5"></div>
</div>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "enabled")); ?>">Enabled</a>
                <?php if ($html->getSortBy() == "enabled") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "cveName")); ?>">CVE</a>
                <?php if ($html->getSortBy() == "cveName") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "tagName")); ?>">Tag</a>
                <?php if ($html->getSortBy() == "tagName") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>Reason</th>
            <th>InfoUrl</th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "modifier")); ?>">Modifier</a>
                <?php if ($html->getSortBy() == "modifier") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>
                <a href="<?php echo $html->getQueryString(array("sortBy" => "timestamp")); ?>">Timestamp</a>
                <?php if ($html->getSortBy() == "timestamp") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>#CVE Exceptions</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cveTags as $cveTag) { ?>
            <?php $cveTagExceptionsCount = $html->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsCountByCveName($cveTag->getCveName()); ?>
            <tr>
                <td>
                    <input type="checkbox" onClick="document.form.act.value='update'; document.form.id.value='<?php echo $cveTag->getId(); ?>';
                        document.form.enabled.value=this.checked; document.form.submit();"<?php if ($cveTag->isEnabled()) echo ' checked'; ?>>
                </td>
                <td><a href="cve.php?cveName=<?php echo $cveTag->getCveName(); ?>"><?php echo $cveTag->getCveName(); ?></a></td>
                <td><?php echo $cveTag->getTagName(); ?></td>
                <td><?php echo $cveTag->getReason(); ?></td>
                <td><a href="<?php echo $cveTag->getInfoUrl(); ?>" target='_blank'><?php echo $cveTag->getInfoUrl(); ?></a></td>
                <td><?php echo $cveTag->getModifier(); ?></td>
                <td><?php echo $cveTag->getTimestamp(); ?></td>
                <td><a href="exceptions.php?cveName=<?php echo $cveTag->getCveName(); ?>"><?php echo $cveTagExceptionsCount; ?></a></td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger"
                        onclick="document.form.act.value='delete'; document.form.id.value='<?php echo $cveTag->getId(); ?>';"
                        data-toggle="modal" data-target="#myModal">Delete</button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>


<form action="" name="form" method="post">
    <input type="hidden" name="act">
    <input type="hidden" name="id">
    <input type="hidden" name="enabled">
</form>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure to delete this CVE tag?</h4>
            </div>
            <div class="modal-body text-right">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="document.form.submit();">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="addLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addLabel">Add CVE tag</h4>
            </div>
            <div class="modal-body">
                <form name="addForm" method="post">
                    <input type="hidden" name="act" value="create">
                    <div class="form-group">
                        <label for="cveName">CVE</label>
                        <select class="form-control" name="cveName" id="cveName">
                            <option value="N/A" selected></option>
                            <?php foreach ($cveNames as $cveName) { ?>
                                <option value="<?php echo $cveName; ?>"><?php echo $cveName; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tagName">Tag</label>
                        <select class="form-control" name="tagName" id="tagName">
                            <option value="N/A" selected></option>
                            <?php foreach ($tagNames as $tagName) { ?>
                                <option value="<?php echo $tagName; ?>"><?php echo $tagName; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <input type="text" class="form-control" name="reason" id="reason">
                    </div>
                    <div class="form-group">
                        <label for="infoUrl">Info URL</label>
                        <input type="text" class="form-control" name="infoUrl" id="infoUrl">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
