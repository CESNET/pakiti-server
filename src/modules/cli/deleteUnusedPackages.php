#!/usr/bin/php
<?php

require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$pkgsIds = $pakiti->getManager("PkgsManager")->getUnusedPkgsIds();
foreach ($pkgsIds as $pkgsId) {
    $pakiti->getManager("PkgsManager")->deletePkg($pkgsId);
}
print "Number of deleted packages: " . count($pkgsIds) . "\n";
