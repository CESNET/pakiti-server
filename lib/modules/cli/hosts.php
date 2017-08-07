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
      "id:",
      "inactiveDays:",
      "help",
);

function usage()
{
    die("Usage: hosts [-h|--help] (-c delete (--id=<id> | --inactiveDays=<inactiveDays>) | list)\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"]) || !isset($opt["c"])) {
    usage();
}

switch ($opt["c"]) {

    # delete hosts
    case "delete":
        if (isset($opt["id"])) {
            if ($pakiti->getManager("HostsManager")->deleteHost($opt["id"])) {
                die("host was deleted\n");
            } else {
                die("host wasn't deleted\n");
            }
        } elseif (isset($opt["inactiveDays"])) {
            $manager = $pakiti->getManager("HostsManager");
            $hosts = $manager->getInactiveHostsLongerThan($opt["inactiveDays"]);

            $number = 0;
            foreach ($hosts as $host) {
                if ($manager->deleteHost($host->getId())) {
                    $number++;
                    print "host [".$host->getId()." - ".$host->getHostname()."] was deleted\n";
                } else {
                    print "host [".$host->getId()." - ".$host->getHostname()."] wasn't deleted\n";
                }
            }
            die($number." hosts was deleted\n");
        } else {
            die("required option id or inactiveDays is missing\n");
        }
        break;

    # list hosts
    case "list":
        $hosts = $pakiti->getManager("HostsManager")->getHosts();
        print "id\thostname\tos\tkernel\tarchitecture\t#CVEs\t#taggedCVEs\n";
        foreach ($hosts as $host) {
            print
                $host->getId()."\t".
                $host->getHostname()."\t".
                $host->getOs()->getName()."\t".
                $host->getKernel()."\t".
                $host->getArch()->getName()."\t".
                $host->getNumOfCves()."\t".
                $host->getNumOfCvesWithTag()."\n";
        }
        break;

    default:
        die("option -c has unknown value\n");
        break;
}
