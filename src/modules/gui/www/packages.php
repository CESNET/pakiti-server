<?php

require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

// Access control
$html->checkPermission("packages");

$_search = $html->getHttpGetVar("search", null);

$html->setTitle("Packages");
$html->setMenuActiveItem("packages.php");
$html->setNumOfEntities($pakiti->getManager("PkgsManager")->getPkgsCount(-1, $_search));

$pkgs = $pakiti->getManager("PkgsManager")->getPkgs($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), -1, $_search);

// HTML
?>

<?php include(realpath(dirname(__FILE__)) . "/../common/header.php"); ?>

<br>
<br>

<div class="row">
    <div class="col-md-4">
        <form>
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="<?php if ($_search != null) echo $_search; ?>" placeholder="Search term...">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                </span>
            </div>
        </form>
    </div>
    <div class="col-md-8"></div>
</div>

<br>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<table class="table table-hover table-condensed">
    <thead>
        <tr>
            <th width="300">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "name")); ?>">Name</a>
                <?php if ($html->getSortBy() == "name") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th width="300">Installed version</th>
            <th width="200">
                <a href="<?php echo $html->getQueryString(array("sortBy" => "arch")); ?>">Architecture</a>
                <?php if ($html->getSortBy() == "arch") { ?>
                    <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                <?php } ?>
            </th>
            <th>Hosts</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pkgs as $pkg) { ?>
            <?php $hosts = $pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, null, null, -1, null, $pkg->getId(), $html->getUserId(), false); ?>
            <tr>
                <td><?php echo $pkg->getName(); ?></td>
                <td><?php echo $pkg->getVersionRelease(); ?></td>
                <td><?php echo $pkg->getArchName(); ?></td>
                <td>
                    <?php foreach ($hosts as $host) { ?>
                        <a href="host.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getHostname(); ?></a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

<?php include(realpath(dirname(__FILE__)) . "/../common/footer.php"); ?>
