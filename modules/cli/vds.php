#!/usr/bin/php
<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

require(realpath(dirname(__FILE__)) . '/../../loader.php');
require(realpath(dirname(__FILE__)) . '/../vds/VdsModule.php');

$vds = new VdsModule($pakiti);

$shortopts = "c:"; # Command

$longopts = array(
      "sourceId:",       # VDS source ID
      "subSourceId:",    # VDS subsource ID
      "defName:",           # Name of the source def
      "defUri:",            # Source def URI
);

$opt = getopt($shortopts, $longopts);

switch ($opt["c"]) {
  # List all registered VDS sources
  case "listSources":
    print "Registered VDS sources:\n";
    $sources = $vds->getVdsSources();
    foreach ($sources as &$source) {
      print $source->getId()." ".$source->getName()."\n";
    }
    break;
     
  # List all VDS subsources
  case "listSubSources":
    if (!isset($opt["sourceId"])) {
      die ("sourceId missing\n");
    }
    $sourceId = $opt["sourceId"];
    
    print "Registered VDS subsources for VDS source ID $sourceId:\n";
    $source =& $vds->getVdsSourceById($sourceId);
    $subSources = $source->getSubSources();
    
    foreach ($subSources as &$subSource) {
      print $subSource->getId()." ".$subSource->getName()."\n";
    }
    break;
    
  # List all sources registered under particular VDS source
  case "listSourceDefs":
    if (!isset($opt["sourceId"])) {
      die ("sourceId missing\n");
    }
    $vdsSourceId = $opt["sourceId"];
    $source = $vds->getVdsSourceById($vdsSourceId);
    var_dump($source->getOvalSourceDefs($source));
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
  
    $source =& $vds->getVdsSourceById($sourceId);
    $subSource =& $source->getSubSourceById($subSourceId);
    
    print "Adding subsource definition for the subsource ".$subSource->getName()."\n";
    
    $params = array (
      "defName" => $defName,
      "defUri"  => $defUri);
    
    $subSource->addSubSourceDef($params);
    
    break;
}

?>