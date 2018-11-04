<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("exceptions");


// Process operations
switch ($html->getHttpPostVar("act")) {
    case "create":
        $cveName = $html->getHttpGetVar("cveName");
        $reason = $html->getHttpPostVar("reason");
        $pkgs = $html->getHttpPostVar("pkgs");
        if (!empty($pkgs)) {
            foreach ($pkgs as $pkg) {
                @list ($pkgId, $osGroupId) = explode(' ', $pkg);
                $exception = new CveException();
                $exception->setCveName($cveName);
                $exception->setPkgId($pkgId);
                $exception->setOsGroupId($osGroupId);
                $exception->setReason($reason);
                $exception->setModifier($html->getUsername());
                $pakiti->getManager("CveExceptionsManager")->storeCveException($exception);
            }
        } else {
            $html->setError("No selected packages");
        }
        break;
    case "delete":
        $id = $html->getHttpPostVar("id");
        $cveTag = $pakiti->getManager("CveExceptionsManager")->getCveExceptionById($id);
        if ($cveTag != null) {
            $pakiti->getManager("CveExceptionsManager")->removeCveExceptionById($id);
        } else {
            $html->setError("CveEexception [" . $id . "] doesn't exists");
        }
        break;
    default:
        break;
}


$html->setTitle("CVE Exceptions");
$html->setMenuActiveItem("exceptions.php");


$selectedCveName = $html->getHttpGetVar("cveName", null);

$cveNames = $pakiti->getManager("CvesManager")->getCvesNames(true);
$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();
$cveExceptions = $pakiti->getManager("CveExceptionsManager")->getCveExceptionsByCveName($selectedCveName);

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-2"></div>
    <div class="col-md-2">
        <?php if($selectedCveName != null) { ?>
            <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#add">Add CVE exception</button>
        <?php } ?>
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-3">
        <div class="text-right">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle btn-block" type="button" id="cveNames" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <div class="text-left">
                        <?php echo ($selectedCveName != null) ? $selectedCveName : "All CVEs"; ?> (<?php echo sizeof($cveExceptions); ?> exception<?php if(sizeof($cveExceptions) != 1) echo 's'; ?>)
                        <span class="caret"></span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-right col-xs-12" aria-labelledby="cveNames">
                    <?php if ($selectedCveName != null) { ?>
                        <li>
                            <a href="?">All CVEs</a>
                        </li>
                    <?php } ?>
                    <?php foreach ($cveNames as $cveName) { ?>
                        <?php if ($cveName != $selectedCveName) { ?>
                            <li>
                                <a href="<?php echo $html->getQueryString(array("cveName" => $cveName)); ?>">
                                    <?php echo $cveName; ?>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<br>
<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>CVE</th>
            <th>Package</th>
            <th>Reason</th>
            <th>Modifier</th>
            <th>Timestamp</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cveExceptions as $cveException) { ?>
            <?php $pkg = $pakiti->getManager("PkgsManager")->getPkgById($cveException->getPkgId()); ?>
            <?php $osGroup = $pakiti->getManager("OsGroupsManager")->getOsGroupById($cveException->getOsGroupId()); ?>
            <tr>
                <td>
                    <a href="cve.php?cveName=<?php echo $cveException->getCveName(); ?>"><?php echo $cveException->getCveName(); ?></a>
                </td>
                <td><?php echo $pkg->getName() . " " . $pkg->getVersionRelease() . "/ " . "<i>" . "(" . $pkg->getArchName() . ") [" . $pkg->getPkgTypeName() . "] " . "</i> " . $osGroup->getName(); ?></td>
                <td><?php echo $cveException->getReason(); ?></td>
                <td><?php echo $cveException->getModifier(); ?></td>
                <td><?php echo $cveException->getTimestamp(); ?></td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger"
                        onclick="document.form.act.value='delete'; document.form.id.value='<?php echo $cveException->getId(); ?>';"
                        data-toggle="modal" data-target="#myModal">Delete</button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<form action="" name="form" method="post">
    <input type="hidden" name="act" />
    <input type="hidden" name="id" />
</form>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure to delete this CVE exception?</h4>
            </div>
            <div class="modal-body text-right">
                <button type="button" class="btn btn-danger" onclick="document.form.submit();">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if($selectedCveName != null) { ?>
    <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="addLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addLabel">Add CVE exception</h4>
                </div>
                <div class="modal-body">
                    <form name="addForm" method="post">
                        <input type="hidden" name="act" value="create">
                        <div class="form-group">
                            <label for="cveName">CVE</label>
                            <input type="text" class="form-control" name="cveName" id="cveName" value="<?php echo $selectedCveName; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <input type="text" class="form-control" name="reason" id="reason">
                        </div>
                        <label>Packages</label>
                        <?php foreach ($osGroups as $osGroup) { ?>
                            <?php $pkgs = $pakiti->getManager("PkgsManager")->getPkgsByCveNameAndOsGroupId($selectedCveName, $osGroup->getId()); ?>
                            <?php foreach ($pkgs as $pkg) { ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="pkgs[]" value="<?php echo $pkg->getId() . ' ' . $osGroup->getId(); ?>">
                                        <?php echo $pkg->getName() . " " . $pkg->getVersionRelease() . "/ " . "<i>" . "(" . $pkg->getArchName() . ") [" . $pkg->getPkgTypeName() . "] " . "</i> " . $osGroup->getName(); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="text-right">
                            <button type="submit" class="btn btn-success">Add</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>


<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
