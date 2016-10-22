<?php
require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

header("Content-Type: text/xml; charset=utf-8");

$pakiti = new Pakiti();

$tags = $pakiti->getManager("TagsManager")->getCvesTags();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <xml></xml>');

foreach($tags as $tag){
    $cveTagNode = $xml->addChild("cveTag");
    $cveTagNode->addChild("cveName", $tag->getCveName());
    $cveTagNode->addChild("reason", $tag->getReason());
    $cveTagNode->addChild("enabled", $tag->getEnabled());
    $tagNode = $cveTagNode->addChild("tag");
    $tagNode->addChild("name", $tag->getName());
    $tagNode->addChild("description", $tag->getDescription());
}

print($xml->asXML());

?>
