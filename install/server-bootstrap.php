#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../src/common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../src/modules/vds/VdsModule.php');

$vds_defs = [
/* "defName" => [ "subSource", "defURI" ] */
	"RHEL 7" => [ "RedHat OVAL", "https://www.redhat.com/security/data/oval/v2/RHEL7/rhel-7.oval.xml.bz2" ],
	"RHEL 8" => [ "RedHat OVAL", "https://www.redhat.com/security/data/oval/v2/RHEL8/rhel-8.oval.xml.bz2" ],
	"RHEL 9" => [ "RedHat OVAL", "https://www.redhat.com/security/data/oval/v2/RHEL9/rhel-9.oval.xml.bz2" ],
	"AlmaLinux 8" => [ "Local OVAL", "https://repo.almalinux.org/security/oval/org.almalinux.alsa-8.xml" ],
	"AlmaLinux 9" => [ "Local OVAL", "https://repo.almalinux.org/security/oval/org.almalinux.alsa-9.xml" ],
	"Debian Security" => [ "Debian Advisories", "https://salsa.debian.org/security-tracker-team/security-tracker/raw/master/data/DSA/list" ],
	"Debian LTS Security" => [ "Debian Advisories", "https://salsa.debian.org/security-tracker-team/security-tracker/raw/master/data/DLA/list" ],
    "Ubuntu 20.04" => ["Ubuntu OVAL", "https://security-metadata.canonical.com/oval/com.ubuntu.focal.cve.oval.xml.bz2"],
    "Ubuntu 22.04" => ["Ubuntu OVAL", "https://security-metadata.canonical.com/oval/com.ubuntu.jammy.cve.oval.xml.bz2"],
    "Ubuntu 24.04" => ["Ubuntu OVAL", "https://security-metadata.canonical.com/oval/com.ubuntu.noble.cve.oval.xml.bz2"],
];

$shortopts = "h"; 

$longopts = array(
    "config:",          # Config file - N.B. we don't handle the config parameter here but in an included file
    "help",
);

function usage($ret)
{
    print("Usage: server-bootstrap [-h|--help] [--config <pakiti_config>]\n");
	exit($ret);
}

function do_exit($msg)
{
	fwrite(STDERR, $msg . PHP_EOL);
	exit(1);
}

function get_SourceId($vds, $name)
{
	$sources = $vds->getSources();
	foreach ($sources as $source) {
		if ($source->getName() == $name)
			return $source->getId();
	}

	return -1;
}

function add_def($source, $subSource_id, $defName, $defUri)
{
        $subSource = $source->getSubSourceById($subSource_id);

        $subSourceDef = new SubSourceDef();
        $subSourceDef->setName($defName);
        $subSourceDef->setUri($defUri);
        $subSourceDef->setSubSourceId($subSource->getId());

        $subSource->addSubSourceDef($subSourceDef);
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"]))
    usage(0);

/* the constructor populates the database based on the current VDS class files */
$vds = new VdsModule($pakiti);

$cve_source_id = get_SourceId($vds, "Cve");
if ($cve_source_id == -1)
	do_exit("No CVE source found");
$cve_source = $vds->getSourceById($cve_source_id);

$subSources = array();
foreach ($cve_source->getSubSources() as $subSource)
	$subSources[$subSource->getId()] = $subSource->getName();

foreach ($vds_defs as $def_name => $def_desc) {
	$subSource_id = array_search($def_desc[0], $subSources);
	if ($subSource_id === False)
		do_exit(sprintf("SubSource %s not activated in server", $def_desc[0]));

	add_def($cve_source, $subSource_id, $def_name, $def_desc[1]);
	print(sprintf("Added vulnerability descriptions for %s.\n", $def_name));
}

print("Downloading all vulnerability definitions (be patient) ...");
$vds->synchronize();
print(" Done\n");

print("\n");
Print("Basic configuration of the server has been finished. Please adapt it to your actual environment.\n");
