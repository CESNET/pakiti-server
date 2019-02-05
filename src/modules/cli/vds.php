#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../vds/VdsModule.php');

$vds = new VdsModule($pakiti);

$shortopts = "c:h"; # Command

$longopts = array(
    "config:",          # Config file - N.B. we don't handle the config parameter here but in an included file
    "sourceId:",        # VDS source ID
    "subSourceId:",     # VDS subsource ID
    "subSourceDefId:",  # VDS subsource Def ID
    "defName:",         # Name of the source def
    "defUri:",          # Source def URI
    "osId:",            # osId
    "help",             # Print help message
);

function usage()
{
    die("Usage: vds [-h|--help] [-c listSources|listSubSources|listSubSrouceDefs|addSubSourceDef|removeSubSourceDef|retrieveDefinitions|assignOsToSubSourceDef|synchronize] --sourceId [sourceId] --subSourceId [subSourceId] --subSourceDefId [subSourceDefId] --defName [definition name] --defUri [definition uri] --osId [osId]\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
    usage();
}

$cmd = isset($opt["c"]) ? $opt["c"] : usage();

switch ($cmd) {

    # List all registered VDS sources
    case "listSources":
        print "Registered VDS sources:\n";
        $sources = $vds->getSources();
        foreach ($sources as $source) {
            print $source->getId()." ".$source->getName()."\n";
        }
        break;

    # List all VDS subsources
    case "listSubSources":
        if (!isset($opt["sourceId"])) {
            die("sourceId missing\n");
        }
        $sourceId = $opt["sourceId"];
        
        $source = $vds->getSourceById($sourceId);
        print "Registered VDS subsources for VDS source {$source->getName()}:\n";
        $subSources = $source->getSubSources();
        
        foreach ($subSources as $subSource) {
            print $subSource->getId()." ".$subSource->getName()."\n";
        }
        break;

    # List all subsources registered under particular VDS source
    case "listSubSourceDefs":
        if (!isset($opt["sourceId"])) {
            die("sourceId missing\n");
        }
        $sourceId = $opt["sourceId"];

        $source = $vds->getSourceById($sourceId);
        $subSources = $source->getSubSources();
        foreach ($subSources as $subSource) {
            $subSourceDefs = $subSource->getSubSourceDefs();
            foreach ($subSourceDefs as $subSourceDef) {
                print "SubSource: {$subSource->getName()} - Id: {$subSourceDef->getId()}, Name: {$subSourceDef->getName()}, URI: {$subSourceDef->getUri()}\n";
            }
        }
        break;

    # Adds the sub source definition to the particular VDS source
    case "addSubSourceDef":
        if (!isset($opt["sourceId"]) || !isset($opt["subSourceId"]) || !isset($opt["defName"]) || !isset($opt["defUri"])) {
            die("--sourceId, --subSourceId, --defName and --defUri must be specified\n");
        }

        $sourceId = $opt["sourceId"];
        $subSourceId = $opt["subSourceId"];
        $defName = $opt["defName"];
        $defUri = $opt["defUri"];

        $source = $vds->getSourceById($sourceId);
        $subSource = $source->getSubSourceById($subSourceId);
        
        print "Adding subsource definition for the subsource ".$subSource->getName()."\n";

        $subSourceDef = new SubSourceDef();
        $subSourceDef->setName($defName);
        $subSourceDef->setUri($defUri);
        $subSourceDef->setSubSourceId($subSource->getId());

        $subSource->addSubSourceDef($subSourceDef);
        break;

    # Removes sub source definition
    case "removeSubSourceDef":
        if (!isset($opt["subSourceDefId"]) || !isset($opt["sourceId"]) || !isset($opt["subSourceId"])) {
            die("--sourceId, --subSourceId, --subSourceDefId must be specified\n");
        }

        $sourceId = $opt["sourceId"];
        $subSourceId = $opt["subSourceId"];

        $source = $vds->getSourceById($sourceId);
        $subSource = $source->getSubSourceById($subSourceId);

        $subSourceDefId = $opt["subSourceDefId"];

        $subSourceDef = new SubSourceDef();
        $subSourceDef->setId($subSourceDefId);

        $subSource->removeSubSourceDef($subSourceDef);

        break;

    # Retrieve definitions from Source
    case "retrieveDefinitions":
        if (!isset($opt["sourceId"])) {
            die("--sourceId must be specified\n");
        }
        $sourceId = $opt["sourceId"];
        $source = $vds->getSourceById($sourceId);
        foreach ($source->getSubSources() as $subSource) {
            $subSource->retrieveDefinitions();
        }
        break;

    # Synchronize all sources
    case "synchronize":
        $vds->synchronize();
        break;

    # Assign OS to subSourceDef
    case "assignOsToSubSourceDef":
        if (!isset($opt["subSourceDefId"]) || !isset($opt["osId"])) {
            die("--subSourceDefId and --osId must be specified\n");
        }
        $subSourceDefId = $opt["subSourceDefId"];
        $subSourceDef = $vds->getSubSourceDefById($subSourceDefId);
        print $subSourceDef->getName();
        break;
}
