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

include(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$pakiti = new Pakiti();
$osName = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("os"));
$cveName = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("cve"));
$type = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("type"));

$os = new Os();
$os->setName($osName);
$pakiti->getManager("OsesManager")->storeOs($os);

# Default output type is CSV
if ($type == "") {
    $type = "csv";
}

$vulnerabilities = & $pakiti->getManager("VulnerabilitiesManager")->getVulnerabilitiesByCveName($cveName, $os->getId());
switch ($type) {
    case "csv":
        header("Content-Type: text/plain");
        print "CVE,Os,Package name,Operator,Package version\n";
        foreach ($vulnerabilities as $vulnerability) {
            print $cveName . "," . $osName . "," . $vulnerability->getName() .
                "," . $vulnerability->getOperator() .
                "," . $vulnerability->getVersion(). "-" .$vulnerability->getRelease() . "\n";
        }
        break;
    case "xml":
        print header("Content-Type: text/xml; charset=utf-8");
        header("Content-Type: text/xml; charset=utf-8");
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <xml></xml>');
        foreach ($vulnerabilities as $vulnerability) {
            $cve = $xml->addChild("cve");
            $cve->addAttribute("name", $cveName);
            $cve->addAttribute("advisory_url", "");
            $os = $cve->addChild("os");
            $os->addAttribute("name", $osName);
            $pkg = $os->addChild("pkg");
            $pkg->addAttribute("name", $vulnerability->getName());
            $pkg->addAttribute("operator", $vulnerability->getOperator());
            $pkg->addAttribute("version", $vulnerability->getVersion(). "-" . $vulnerability->getRelease());
        }
        print($xml->asXML());
        break;
}
