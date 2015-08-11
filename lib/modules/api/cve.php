<?php
/**
 * User: Vadym Yanovskyy
 * Date: 8/2/15
 * Time: 6:29 PM
 */
include(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$pakiti = new Pakiti();
$osName = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("os"));
$cveName = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("cve"));
$type = $pakiti->getManager("DbManager")->escape(Utils::getHttpGetVar("type"));

//Default output type is CSV
if ($type == "") {
    $type = "csv";
}

//TODO: Add authorization

$vulnerabilities = & $pakiti->getManager("VulnerabilitiesManager")->getVulnerabilitiesByCveNameAndOsName($cveName, $osName);
switch($type){
    case "csv":
        header("Content-Type: text/plain");
        print "CVE,Os,Package name,Operator,Package version\n";
        foreach($vulnerabilities as $vulnerability){
            print $cveName . "," . $osName . "," . $vulnerability->getName() .
                "," . $vulnerability->getOperator() .
                "," . $vulnerability->getVersion(). "-" .$vulnerability->getRelease() . "\n";
        }
        break;
    case "xml":
        print header("Content-Type: text/xml; charset=utf-8");
        header("Content-Type: text/xml; charset=utf-8");
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?> <xml></xml>');
        foreach($vulnerabilities as $vulnerability) {
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
}

?>
