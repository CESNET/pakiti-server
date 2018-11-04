<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

header("Content-Type: text/xml; charset=utf-8");

$pakiti = new Pakiti();

$cveTags = $pakiti->getManager("CveTagsManager")->getCveTags();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <cveTags></cveTags>');

foreach ($cveTags as $cveTag) {
    $cveTagNode = $xml->addChild("cveTag");
    $cveTagNode->addChild("cveName", $cveTag->getCveName());
    $cveTagNode->addChild("tagName", $cveTag->getTagName());
    $cveTagNode->addChild("reason", $cveTag->getReason());
    $cveTagNode->addChild("infoUrl", $cveTag->getInfoUrl());
    $cveTagNode->addChild("enabled", $cveTag->isEnabled());
    $cveTagNode->addChild("modifier", Config::$PAKITI_NAME);
    $cveTagNode->addChild("timestamp", $cveTag->getTimestamp());
}

print($xml->asXML());
