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
$_activeIn = Utils::getHttpGetVar("activeIn");

$hosts = $pakiti->getManager("HostsManager")->getHosts(null, -1, -1, null, $_cveName, $_tag, $_hostGroupId, $_activeIn);

$out = fopen('php://output', 'w');

$values = array("hostname", "hostGroup", "os", "kernel", "arch", "cve", "tag");
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
                $values[] = $host->getHostName();
                $values[] = $hostGroup->getName();
                $values[] = $host->getOsName();
                $values[] = $host->getKernel();
                $values[] = $host->getArchName();
                $values[] = $cveName;
                $values[] = $cveTag->getTagName();
                fputcsv($out, $values);
            }
        }
    }
}

fclose($out);
