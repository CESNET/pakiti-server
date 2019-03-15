<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

header("Content-Type: text/csv; charset=utf-8");

$pakiti = new Pakiti();

$hostGroupName = Utils::getHttpGetVar("hostGroup");
if ($hostGroupName == null) {
    $hostGroupName = "";
}

$_cveName = Utils::getHttpGetVar("cveName");
$_tag = Utils::getHttpGetVar("tag");
if ($_tag == null) {
    $_tag = true;
}
$_hostGroupId = $pakiti->getmanager("HostGroupsManager")->getHostGroupIdByName($hostGroupName);
$_activity = Utils::getHttpGetVar("activity");

$hosts = $pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, $_cveName, $_tag, $_hostGroupId, $_activity);

$out = fopen('php://output', 'w');

$values = array('CVE_Tag', "Country", "ROC", "Site_Name", "Hostname", "Host_Architecture", "Host_OS", "CVE_Name", "CSIRT_Mails");
fputcsv($out, $values);

foreach ($hosts as $host) {
    $hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroupsByHost($host);
    foreach ($hostGroups as $hostGroup) {
        $cvesNames = $pakiti->getManager("CvesManager")->getCvesNamesForHost($host->getId(), $_tag);
        foreach ($cvesNames as $cveName) {
            if ($_cveName != null && $_cveName != $cveName) {
                continue;
            }
            $cveTags = $pakiti->getManager("CveTagsManager")->getCveTagsByCveName($cveName);
            foreach ($cveTags as $cveTag) {
                if ($_tag != null && $_tag !== true && $_tag != $cveTag->getName()) {
                    continue;
                }
                $values = array();
                $values[] = $cveTag->getTagName();
                $values[] = "";
                $values[] = "";
                $values[] = $hostGroup->getName();
                $values[] = $host->getHostName();
                $values[] = $host->getArchName();
                $values[] = $host->getOsName();
                $values[] = $cveName;
                $values[] = "";
                fputcsv($out, $values);
            }
        }
    }
}

fclose($out);
