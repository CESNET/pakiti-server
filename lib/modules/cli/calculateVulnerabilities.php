<?php
require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');
$pakiti = new Pakiti();
$pakiti->getManager("VulnerabilitiesManager")->calculateVulnerabilitiesForEachPkg();