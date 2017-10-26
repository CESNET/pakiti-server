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
