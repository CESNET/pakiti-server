<?php
/**
 * User: Vadym Yanovskyy
 * Date: 8/5/15
 * Time: 12:21 PM
 */

include(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$pakiti = new Pakiti();
$htag = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("tag")); //host Tag
$country = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("country"));
$hostGroup = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("group"));
$cve = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("cve"));
$roc = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("roc"));
$type = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("type"));

# Default output type is CSV
if ($type == "") {
    $type = "csv";
}

//TODO: Add authorization

$hosts = &$pakiti->getManager("VulnerabilitiesManager")->getHostsWithCvesThatContainsSomeTag($htag, $hostGroup, $cve);

switch ($type) {
    case "csv":
        header("Content-Type: text/plain");
        print "CVE Tag,Site Country,ROC,Host Group,Hostname,Host Architecture,Host OS,CVE Name,CSIRT Mails\n";
        foreach ($hosts as $host) {
            foreach ($host["HostGroups"] as $hostGroup) {
                foreach ($host["CVE"] as $cve) {
                    foreach ($cve->getTag() as $tag) {
                        print $tag->getName() . "," . "," . "," . $hostGroup->getName() . "," .
                            $host["Host"]->getHostname() . "," . $host["Host"]->getArch()->getName() . "," .
                            $host["Host"]->getOs()->getName() . "," . $cve->getName() . "," . ",";
                    }
                }
            }

        }
        break;
}