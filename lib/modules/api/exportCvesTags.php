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

header("Content-Type: text/xml; charset=utf-8");

$pakiti = new Pakiti();

$cveTags = $pakiti->getManager("CveTagsManager")->getCveTags();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <cveTags></cveTags>');

foreach($cveTags as $cveTag){
    $cveTagNode = $xml->addChild("cveTag");
    $cveTagNode->addChild("cveName", $cveTag->getCveName());
    $cveTagNode->addChild("tagName", $cveTag->getTagName());
    $cveTagNode->addChild("reason", $cveTag->getReason());
    $cveTagNode->addChild("infoUrl", $cveTag->getInfoUrl());
    $cveTagNode->addChild("enabled", $cveTag->isEnabled());
    $cveTagNode->addChild("modifier", $cveTag->getModifier());
    $cveTagNode->addChild("timestamp", $cveTag->getTimestamp());
}

print($xml->asXML());

?>
