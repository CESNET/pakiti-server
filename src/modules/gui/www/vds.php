<?php

require(realpath(dirname(__FILE__)) . '/../../../common/DefaultModule.php');
require(realpath(dirname(__FILE__)) . '/../../../modules/vds/VdsModule.php');
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("vds");

$vds = new VdsModule($pakiti);

// Process operations
switch ($html->getHttpPostVar("act")) {
    case "create":
        $defName = $html->getHttpPostVar("name");
        $defUri = $html->getHttpPostVar("uri");
        @list ($sourceId, $subSourceId) = explode(' ', $html->getHttpPostVar("ids"));

        $source = $vds->getSourceById($sourceId);
        $subSource = $source->getSubSourceById($subSourceId);

        $subSourceDef = new SubSourceDef();
        $subSourceDef->setName($defName);
        $subSourceDef->setUri($defUri);
        $subSourceDef->setSubSourceId($subSource->getId());
        $subSource->addSubSourceDef($subSourceDef);
        break;
    case "delete":
        $sourceId = $html->getHttpPostVar("sourceId");
        $subSourceId = $html->getHttpPostVar("subSourceId");
        $subSourceDefId = $html->getHttpPostVar("subSourceDefId");

        $source = $vds->getSourceById($sourceId);
        $subSource = $source->getSubSourceById($subSourceId);
        $subSourceDef = new SubSourceDef();
        $subSourceDef->setId($subSourceDefId);
        $subSource->removeSubSourceDef($subSourceDef);
        break;
    default:
        break;
}


$html->setTitle("Vulnerability Definition System");
$html->setMenuActiveItem("vds.php");

$sources = $vds->getSources();

// HTML
?>


<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>


<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-2">
        <button class="btn btn-success btn-block" type="submit" data-toggle="modal" data-target="#add">Add subSourceDef</button>
    </div>
    <div class="col-md-5"></div>
</div>

<br>
<br>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th>Source</th>
            <th>SubSource</th>
            <th>Name</th>
            <th>URI</th>
            <th>Last check</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sources as $source) { ?>
            <?php foreach ($source->getSubSources() as $subSource) { ?>
                <?php foreach ($subSource->getSubSourceDefs() as $subSourceDef) { ?>
                    <tr>
                        <td><?php echo $source->getName(); ?></td>
                        <td><?php echo $subSource->getName(); ?></td>
                        <td><?php echo $subSourceDef->getName(); ?></td>
                        <td><?php echo $subSourceDef->getUri(); ?></td>
                        <td><?php echo $subSourceDef->getLastChecked(); ?></td>
                        <td>
                            <button type="button" class="btn btn-xs btn-danger"
                                onclick="document.form.act.value='delete';
                                    document.form.sourceId.value='<?php echo $source->getId(); ?>';
                                    document.form.subSourceId.value='<?php echo $subSource->getId(); ?>';
                                    document.form.subSourceDefId.value='<?php echo $subSourceDef->getId(); ?>';"
                                data-toggle="modal" data-target="#myModal">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>


<form action="" name="form" method="post">
    <input type="hidden" name="act" />
    <input type="hidden" name="sourceId" />
    <input type="hidden" name="subSourceId" />
    <input type="hidden" name="subSourceDefId" />
</form>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Are you sure to delete this subSourceDef?</h4>
            </div>
            <div class="modal-body text-right">
                <button type="button" class="btn btn-danger" onclick="document.form.submit();">Delete</button>
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
                <h4 class="modal-title" id="addLabel">Add subSourceDef</h4>
            </div>
            <div class="modal-body">
                <form name="addForm" method="post">
                    <input type="hidden" name="act" value="create">
                    <div class="form-group">
                        <label for="ids">SubSource</label>
                        <select class="form-control" name="ids" id="ids">
                            <option value="N/A" selected></option>
                            <?php foreach ($sources as $source) { ?>
                                <?php foreach ($source->getSubSources() as $subSource) { ?>
                                <option value="<?php echo $source->getId()." ".$subSource->getId(); ?>"><?php print $subSource->getName() . " [" . $source->getName() . "]"; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name">
                    </div>
                    <div class="form-group">
                        <label for="uri">URI</label>
                        <input type="text" class="form-control" name="uri" id="uri">
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
