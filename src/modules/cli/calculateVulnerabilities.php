#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "c:h";

# N.B. we don't handle the config parameter here but in an included file
$longopts = array(
    "help",
    "config:",
);

function usage()
{
    die("Usage: calculateVulnerabilities.php [-h|--help] [--config <pakiti config>]\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
    usage();
}

$pakiti->getManager("VulnerabilitiesManager")->calculateVulnerabilitiesForEachPkg();
$pakiti->getManager("HostsManager")->recalculateCvesCountForEachHost();
