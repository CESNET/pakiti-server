<?php
require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

header("Content-Type: text/xml; charset=utf-8");

$pakiti = new Pakiti();

$cveExceptions = $pakiti->getDao("CveException")->getCvesExceptions();

$pkgDao = $pakiti->getDao("Pkg");
$osGroupDao = $pakiti->getDao("OsGroup");

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <xml></xml>');

foreach($cveExceptions as $cveException){
    $cveExceptionNode = $xml->addChild("cveException");
    $cveExceptionNode->addChild("cveName", $cveException->getCveName());
    $cveExceptionNode->addChild("reason", $cveException->getReason());
    $pkg = $pkgDao->getById($cveException->getPkgId());
    $pkgNode = $cveExceptionNode->addChild("pkg");
    $pkgNode->addChild("name", $pkg->getName());
    $pkgNode->addChild("version", $pkg->getVersion());
    $pkgNode->addChild("release", $pkg->getRelease());
    $pkgNode->addChild("arch", $pkg->getArch());
    $osGroup = $osGroupDao->getById($cveException->getOsGroupId());
    $osGroupNode = $cveExceptionNode->addChild("osGroup");
    $osGroupNode->addChild("name",$osGroup->getName());
}

print($xml->asXML());

?>
