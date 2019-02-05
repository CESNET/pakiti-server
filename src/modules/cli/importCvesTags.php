#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "hu:rv";

# N.B. we don't handle the config parameter here but in an included file
$longopts = array(
    "config:",
    "help",
    "url:",
    "remove",
    "verbose",
);

function usage()
{
    die("Usage: importCvesTags [--config <pakiti config>] (-u <url> | --url=<url>) [-r | --remove] [-v | --verbose]\n");
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
        if (! in_array($cveTagNode->cveName, $cveNames)) {
            if (isset($opt["v"]) || isset($opt["verbose"])) {
                fwrite(STDERR, "CVE " . $cveTagNode->cveName ." is not defined here\n");
            }
            continue;
        }
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

    if (isset($opt["r"]) || isset($opt["remove"])) {
        $allCveTagsIds = $pakiti->getManager("CveTagsManager")->getCveTagsIds();
        foreach ($allCveTagsIds as $id) {
            if (!in_array($id, $cveTagsIds)) {
                $pakiti->getManager("CveTagsManager")->deleteCveTagById($id);
            }
        }
    }
}
