#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "c:h";

$longopts = array(
    "hostId:",
    "search:",
    "help",
);

function usage()
{
    die("Usage: packages [-h|--help] (-c list (--hostId=<hostId>) (--search=<search>))\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"]) || !isset($opt["c"])) {
    usage();
}

switch ($opt["c"]) {

    # list packages
    case "list":
        $hostId = isset($opt["hostId"]) ? $opt["hostId"] : -1;
        $search = isset($opt["search"]) ? $opt["search"] : null;
        $pkgs = $pakiti->getManager("PkgsManager")->getPkgs(null, -1, -1, $hostId, $search);

        print "\nname\tversion\trelease\tarch\tpkgType\n";
        print "----------------------------------------------------------------------\n";
        foreach ($pkgs as $pkg) {
            print
                $pkg->getName() ."\t" .
                $pkg->getVersion() ."\t" .
                $pkg->getRelease() ."\t" .
                $pkg->getArchName() ."\t" .
                $pkg->getPkgTypeName() ."\t" .
                "\n";
        }
        break;

    default:
        die("option -c has unknown value\n");
        break;
}
