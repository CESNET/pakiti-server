#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "h";

# N.B. we don't handle the config parameter here but in an included file
$longopts = array(
    "config:",
    "help",
);

function usage()
{
    die("Usage: recalculateOsGroupsMapping [-h|--help] [--config <pakiti config>]\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
    usage();
}

$manager = $pakiti->getManager("OsesManager");
$oses = $manager->getOses();

foreach ($oses as $os)
    $manager->recalculateOsGroups($os);
