#!/usr/bin/php
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
