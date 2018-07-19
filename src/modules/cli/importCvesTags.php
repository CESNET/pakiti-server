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

# N.B. we don't handle the config parameter here but in an included file
$longopts = array(
    "config:",
    "help",
    "url:",
    "remove"
);

function usage()
{
    die("Usage: importCvesTags [--config <pakiti config>] (-u <url> | --url=<url>) [-r | --remove]\n");
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

    # Get all cve names in order to check if cve name exists
    $cveNames = $pakiti->getManager("CvesManager")->getCvesNames();

    $cveTagsIds = array();
    foreach ($xml->cveTag as $cveTagNode) {
        if (in_array($cveTagNode->cveName, $cveNames)) {
            $cveTag = new CveTag();
            $cveTag->setCveName($cveTagNode->cveName->__toString());
            $cveTag->setTagName($cveTagNode->tagName->__toString());
            $cveTag->setReason($cveTagNode->reason->__toString());
            $cveTag->setInfoUrl($cveTagNode->infoUrl->__toString());
            $cveTag->setEnabled($cveTagNode->enabled->__toString());
            $cveTag->setModifier($cveTagNode->modifier->__toString());
            $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
            array_push($cveTagsIds, $cveTag->getId());
        }
    }

    if (isset($opt["r"]) || isset($opt["remove"])) {
        $allCveTagsIds = $pakiti->getManager("CveTagsManager")->getCveTagsIds();
        foreach ($allCveTagsIds as $id) {
            if (!in_array($id, $cveTagsIds)) {
                $pakiti->getManager("CveTagsManager")->deleteCveTagById($id);
            }
        }
    }
}
