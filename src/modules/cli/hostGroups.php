#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "c:h";

$longopts = array(
    "name:",
    "url:",
    "contact:",
    "note:",
    "help",
);

function usage()
{
    die("Usage: hostGroups [-h|--help] (-c store (--name=<name>) [--url=<url>] [--contact=<contact>] [--note=<note>] | delete (--name=<name>) | list)\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"]) || !isset($opt["c"])) {
    usage();
}

switch ($opt["c"]) {

    # store hostGroup
    case "store":
        $hostGroup = new HostGroup();
        if (isset($opt["name"])) {
            $hostGroup->setName($opt["name"]);
        } else {
            die("required option name is missing\n");
        }
        if (isset($opt["url"])) {
            $hostGroup->setUrl($opt["url"]);
        }
        if (isset($opt["contact"])) {
            $hostGroup->setContact($opt["contact"]);
        }
        if (isset($opt["note"])) {
            $hostGroup->setNote($opt["note"]);
        }
        if ($pakiti->getManager("HostGroupsManager")->storeHostGroup($hostGroup)) {
            die("hostGroup was created\n");
        } else {
            die("hostGroup was updated\n");
        }
        break;

    # delete hostGroup
    case "delete":
        if (isset($opt["name"])) {
            $id = $pakiti->getManager("HostGroupsManager")->getHostGroupIdByName($opt["name"]);
        } else {
            die("required option name is missing\n");
        }
        if ($pakiti->getManager("HostGroupsManager")->deleteHostGroup($id)) {
            die("hostGroup was deleted\n");
        } else {
            die("hostGroup wasn't deleted\n");
        }
        break;

    # list hostGroups
    case "list":
        $hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups();
        print "name\turl\tcontact\tnote\n";
        foreach ($hostGroups as $hostGroup) {
            print $hostGroup->getName()."\t".$hostGroup->getUrl()."\t".$hostGroup->getContact()."\t".$hostGroup->getNote()."\n";
        }
        break;

    default:
        die("option -c has unknown value\n");
        break;
}
