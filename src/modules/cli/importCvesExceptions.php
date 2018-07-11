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

$shortopts = "hu:r";

$longopts = array(
    "help",
    "url:",
    "remove"
);

function usage()
{
    die("Usage: importCvesExceptions (-u <url> | --url=<url>) [-r | --remove]\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
    usage();
}

$url = isset($opt["u"]) ? $opt["u"] : (isset($opt["url"]) ? $opt["url"] : null);

if ($url == null) {
    usage();
} else {
    $xml = simplexml_load_string(Utils::getContent($url));

    if ($xml == null) {
        die("Xml parsing error! Check log for curl errors.");
    }

    foreach ($xml->cveException as $cveExceptionNode) {

        # If pkg->type is missing, iterate over all types
        if (!isset($cveExceptionNode->pkg->type)) {
            $typesIds = $pakiti->getManager("PkgTypesManager")->getPkgTypesIds();
        } else {
            $typesIds = [$pakiti->getManager("PkgTypesManager")->getPkgTypeIdByName($cveExceptionNode->pkg->type->__toString())];
        }

        # If pkg->arch is missing, iterate over all archs
        if (!isset($cveExceptionNode->pkg->arch)) {
            $archsIds = $pakiti->getManager("ArchsManager")->getArchsIds();
        } else {
            $archsIds = [$pakiti->getManager("ArchsManager")->getArchIdByName($cveExceptionNode->pkg->arch->__toString())];
        }

        # If osGroup->name is missing, iterate over all osGroups
        if (!isset($cveExceptionNode->osGroup->name)) {
            $osGroupIds = $pakiti->getManager("OsGroupsManager")->getOsGroupsIds();
        } else {
            $osGroupIds = [$pakiti->getManager("OsGroupsManager")->getOsGroupIdByName($cveExceptionNode->osGroup->name->__toString())];
        }

        $cvesExceptionsIds = array();
        foreach ($typesIds as $typeId) {
            foreach ($archsIds as $archId) {
                $pkgId = $pakiti->getManager("PkgsManager")->getPkgId($cveExceptionNode->pkg->name, $cveExceptionNode->pkg->version, $cveExceptionNode->pkg->release, $archId, $typeId);
                if ($pkgId != -1) {
                    foreach ($osGroupIds as $osGroupId) {
                        if ($pakiti->getManager("CveExceptionsManager")->isExceptionCandidate($cveExceptionNode->cveName, $pkgId, $osGroupId)) {
                            $cveException = new CveException();
                            $cveException->setCveName($cveExceptionNode->cveName->__toString());
                            $cveException->setReason($cveExceptionNode->reason->__toString());
                            $cveException->setModifier($cveExceptionNode->modifier->__toString());
                            $cveException->setPkgId($pkgId);
                            $cveException->setOsGroupId($osGroupId);
                            $pakiti->getManager("CveExceptionsManager")->storeCveException($cveException);
                            array_push($cvesExceptionsIds, $cveException->getId());
                        }
                    }
                }
            }
        }

        if (isset($opt["r"]) || isset($opt["remove"])) {
            $allCvesExceptionsIds = $pakiti->getManager("CveExceptionsManager")->getCvesExceptionsIds();
            foreach ($allCvesExceptionsIds as $id) {
                if (!in_array($id, $cvesExceptionsIds)) {
                    $pakiti->getManager("CveExceptionsManager")->removeCveExceptionById($id);
                }
            }
        }
    }
}
