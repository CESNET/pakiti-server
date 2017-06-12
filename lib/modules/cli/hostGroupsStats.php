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

$shortopts = "hf:t:"; # Command

$longopts = array(
      "help",
      "from:",
      "to:"
);

function usage()
{
    die("Usage: hostGroupsStats.php [OPTIONS]
    -h, --help \t Display this help and exit.
    -f, --from \t The start date(Y-m-d) of the statistics (incl.)
    -t, --to \t The last date(Y-m-d) of the statistics (incl.)
    \n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
    usage();
}

# From
$from = null;
$startOfDay = " 00:00:00";
if (isset($opt["f"])) {
    $from = $opt["f"] . $startOfDay;
} elseif (isset($opt["from"])) {
    $from = $opt["from"] . $startOfDay;
}

# To
$to = null;
$endOfDay = " 23:59:59";
if (isset($opt["t"])) {
    $to = $opt["t"] . $endOfDay;
} elseif (isset($opt["to"])) {
    $to = $opt["to"] . $endOfDay;
}

print "HostGroupName\tNumOfHosts\tNumOfVulnerableHosts\tNumOfReports\tNumOfVulnerableReports\n";

$hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups();
foreach ($hostGroups as $hostGroup) {
    $numOfVulnerableHosts = 0;
    $numOfHosts = 0;
    $numOfVulnerableReports = 0;
    $numOfReports = 0;
    $hosts = $pakiti->getManager("HostsManager")->getHostsByHostGroupId($hostGroup->getId());
    foreach ($hosts as $host) {
        $numOfReportsForHost = 0;
        $numOfVulnerableReportsForHost = 0;
        $reports = $pakiti->getManager("ReportsManager")->getReportsByHostIdFromTo($host->getId(), $from, $to);
        foreach ($reports as $report) {
            $numOfReportsForHost += 1;
            if ($report->getNumOfCvesWithTag() > 0) {
                $numOfVulnerableReportsForHost += 1;
            }
        }
        if ($numOfReportsForHost > 0) {
            $numOfHosts += 1;
        }
        if ($numOfVulnerableReportsForHost > 0) {
            $numOfVulnerableHosts += 1;
        }
        $numOfReports += $numOfReportsForHost;
        $numOfVulnerableReports += $numOfVulnerableReportsForHost;
    }
    print $hostGroup->getName()."\t".$numOfHosts."\t".$numOfVulnerableHosts."\t".$numOfReports."\t".$numOfVulnerableReports."\n";
}
