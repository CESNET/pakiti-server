<?php

include('GenerateTestData.php');


class InstalledPkgDaoTest extends \PHPUnit_Framework_TestCase {
    private static $_testData;

    public static function setUpBeforeClass()
    {
       self::$_testData = new GenerateTestData();
    }

    public function testGet()
    {
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $installedPkg2 = self::$_testData->getPakiti() -> getDao("InstalledPkg") -> get(self::$_testData->getHost()->getId(), self::$_testData->getPkg1()->getId());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals(self::$_testData->getInstalledPkg1(), $installedPkg2);
    }

    public function testGetIdsByHostId(){
        $row1 = self::$_testData->getInstalledPkg1()->getPkgId();
        $row2 = self::$_testData->getInstalledPkg2()->getPkgId();
        $installedPkgs1 = array($row1, $row2);

        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $installedPkgs2 = self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getIdsByHostId(self::$_testData->getHost()->getId());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testGetInstalledPkgs()
    {
        $installedPkgs1 = array(self::$_testData->getPkg1(), self::$_testData->getPkg2());

        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $installedPkgs2 = self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getInstalledPkgs(self::$_testData->getHost(), "name", 5, 0);
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testgetInstalledPkgsAsArray(){
        $record1 = array(
            "pkgVersion" => self::$_testData->getPkg1()->getVersion(),
            "pkgRelease" => self::$_testData->getPkg1()->getRelease(),
            "pkgArch" => self::$_testData->getPkg1()->getArch()
        );

        $record2 = array(
            "pkgVersion" => self::$_testData->getPkg2()->getVersion(),
            "pkgRelease" => self::$_testData->getPkg2()->getRelease(),
            "pkgArch" => self::$_testData->getPkg2()->getArch()
        );

        $installedPkgs1 = array(self::$_testData->getPkg1()->getName() => $record1, self::$_testData->getPkg2()->getName() => $record2);

        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $installedPkgs2 = self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getInstalledPkgsAsArray(self::$_testData->getHost());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testGetInstalledPkgsCount()
    {
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $packages_num= self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getInstalledPkgsCount(self::$_testData->getHost());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals(2, $packages_num);
    }


    public static function tearDownAfterClass()
    {
        self::$_testData->deleteGeneratedTestData();
    }
}
