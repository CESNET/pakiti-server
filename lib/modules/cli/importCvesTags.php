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

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$shortopts = "hu:r"; # Command

$longopts = array(
      "help",
      "url:",
      "remove"
);

function usage() {
  die("Usage: importCvesTags (-u <url> | --url=<url>) [-r | --remove]\n");
}

$opt = getopt($shortopts, $longopts);

if (isset($opt["h"]) || isset($opt["help"])) {
  usage();
}

$url = isset($opt["u"]) ? $opt["u"] : (isset($opt["url"]) ? $opt["url"] : null);

if($url == null){
  usage();
} else {
  $xml = simplexml_load_string(Utils::getContent($url));

  if($xml == null){
    die("Xml parsing error! Check log for curl errors.");
  }

  if (isset($opt["r"]) || isset($opt["remove"])) {
    $pakiti->getDao("Tag")->deleteCveTags();
  }

  foreach($xml->cveTag as $cveTagNode){
    if($pakiti->getDao("Cve")->getCvesByName($cveTagNode->cveName) != null){
      $tag = new Tag();
      $tag->setName($cveTagNode->tag->name);
      $tag->setDescription($cveTagNode->tag->description);
      $tag->setReason($cveTagNode->reason);
      $tag->setInfoUrl($cveTagNode->infoUrl);
      $tag->setModifier($url);
      $tag->setEnabled($cveTagNode->enabled);
      $cve = new Cve();
      $cve->setName($cveTagNode->cveName);
      $pakiti->getManager("TagsManager")->assignTagToCve($cve, $tag);
    }
  }
}

?>
