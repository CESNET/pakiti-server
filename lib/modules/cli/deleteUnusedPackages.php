<?php
/**
 * User: Vadym Yanovskyy
 * Date: 27.10.15
 * Time: 14:46
 */
require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');
$pkgsToDelete = $pakiti->getManager("PkgsManager")->getUnusedPkgs();
foreach($pkgsToDelete as $pkgToDelete){
    $pakiti->getManager("PkgsManager")->deletePkg($pkgToDelete);
}
print "Number of deleted packages: " . count($pkgsToDelete) . "\n";