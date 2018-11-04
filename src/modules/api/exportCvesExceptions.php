<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

header("Content-Type: text/xml; charset=utf-8");

$pakiti = new Pakiti();

$cveExceptions = $pakiti->getDao("CveException")->getCvesExceptions();

$pkgDao = $pakiti->getDao("Pkg");
$osGroupsManager = $pakiti->getManager("OsGroupsManager");

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <cveExceptions></cveExceptions>');

foreach ($cveExceptions as $cveException) {
    $cveExceptionNode = $xml->addChild("cveException");
    $cveExceptionNode->addChild("cveName", $cveException->getCveName());
    $cveExceptionNode->addChild("reason", $cveException->getReason());
    $cveExceptionNode->addChild("modifier", Config::$PAKITI_NAME);
    $pkg = $pkgDao->getById($cveException->getPkgId());
    $pkgNode = $cveExceptionNode->addChild("pkg");
    $pkgNode->addChild("name", $pkg->getName());
    $pkgNode->addChild("version", $pkg->getVersion());
    $pkgNode->addChild("release", $pkg->getRelease());
    $pkgNode->addChild("arch", $pkg->getArchName());
    $pkgNode->addChild("type", $pkg->getPkgTypeName());
    $osGroup = $osGroupsManager->getOsGroupById($cveException->getOsGroupId());
    $osGroupNode = $cveExceptionNode->addChild("osGroup");
    $osGroupNode->addChild("name", $osGroup->getName());
}

print($xml->asXML());
