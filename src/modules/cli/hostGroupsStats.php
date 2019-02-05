#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "hf:t:";

# N.B. we don't handle the config parameter here but in an included file
$longopts = array(
    "help",
    "from:",
    "to:",
    "config:",
);

function usage()
{
    die("Usage: hostGroupsStats.php [OPTIONS]
        -h, --help \t Display this help and exit.
        -f, --from \t The start date(Y-m-d) of the statistics (incl.)
        -t, --to \t The last date(Y-m-d) of the statistics (incl.)
        --config \t Pakiti configuration to use
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
    $hostsIds = $pakiti->getManager("HostsManager")->getHostsIds(null, -1, -1, null, null, null, $hostGroup->getId(), null, -1, -1, false);
    foreach ($hostsIds as $hostId) {
        $numOfReportsForHost = 0;
        $numOfVulnerableReportsForHost = 0;
        $reports = $pakiti->getManager("ReportsManager")->getReportsByHostIdFromTo($hostId, $from, $to);
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
