#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');
$pakiti = new Pakiti();
$pakiti->getManager("VulnerabilitiesManager")->calculateVulnerabilitiesForEachPkg();
$pakiti->getManager("HostsManager")->recalculateCvesCountForEachHost();
