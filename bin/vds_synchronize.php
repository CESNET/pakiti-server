<?php
/**
 * User: Vadym Yanovskyy
 * Date: 7/26/15
 * Time: 2:33 PM
 */
 
//include(realpath(dirname(__FILE__)) . '/../lib/managers/DbManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/DefaultManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/HostsManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/CvesDefManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/VulnerabilitiesManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/ReportsManager.php');
//include(realpath(dirname(__FILE__)) . '/../lib/managers/PkgsManager.php');
//
//include(realpath(dirname(__FILE__)) . '/../lib/model/Pkg.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/Domain.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/Report.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/Host.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/CveDef.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/Os.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/Vulnerability.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/VdsSource.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/InstalledPkg.php');
//include(realpath(dirname(__FILE__)) . '/../lib/model/OsGroup.php');
//
//include(realpath(dirname(__FILE__)) . '/../lib/dao/InstalledPkgDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/ReportDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/CveDefDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/OsGroupDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/OsDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/PkgDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/HostDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/VulnerabilityDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/VdsSubSourceDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/VdsSourceDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/DomainDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/ArchDao.php');
//include(realpath(dirname(__FILE__)) . '/../lib/dao/CveDao.php');

require(realpath(dirname(__FILE__)) . '/../lib/common/Loader.php');
//include_once(realpath(dirname(__FILE__)) . '/../lib/common/DefaultModule.php');
//include_once(realpath(dirname(__FILE__)) . '/../lib/common/Pakiti.php');
//include_once(realpath(dirname(__FILE__)) . '/../lib/common/Utils.php');
include_once(realpath(dirname(__FILE__)) . '/../lib/modules/vds/VdsModule.php');


$pakiti = new Pakiti();
$vds = new VdsModule($pakiti);

//Add Oval
$sourceId = $pakiti->getDao("VdsSource")->getIdByName("Cve", $pakiti);
$subSourceId = $pakiti->getDao("VdsSubSource")->getIdByName("RedHat Oval", $pakiti);
$defName = "oval:com.redhat.rhsa:def:20150001";
$defUri = "https://www.redhat.com/security/data/oval/com.redhat.rhsa-2015.xml";
#$defUri = "http://pakiti.com/pakiti3/tests/com.redhat.rhsa-2015.xml";
$source =& $vds->getSourceById($sourceId);
$subSource =& $source->getSubSourceById($subSourceId);

$subSourceDef = new SubSourceDef();
$subSourceDef->setName($defName);
$subSourceDef->setUri($defUri);
$subSourceDef->setSubSourceId($subSource->getId());
$subSource->addSubSourceDef($subSourceDef);

//Add DSA
$sourceId = $pakiti->getDao("VdsSource")->getIdByName("Cve", $pakiti);
$subSourceId = $pakiti->getDao("VdsSubSource")->getIdByName("Debian", $pakiti);
$defName = "anonscm.debian.org";
$defUri = "svn://anonscm.debian.org/svn/secure-testing/data/DSA/";

$subSourceDef = new SubSourceDef();
$subSourceDef->setName($defName);
$subSourceDef->setUri($defUri);
$subSourceDef->setSubSourceId($subSourceId);
$subSource->addSubSourceDef($subSourceDef);

$vds->synchronize();

#$pakiti->getManager("VulnerabilitiesManager")->findVulnerablePkgsForEachHost();