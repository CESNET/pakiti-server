<?php

include('GenerateTestData.php');

class PkgsManagerTest extends PHPUnit_Framework_TestCase
{
    private static $_testData;

    public static function setUpBeforeClass()
    {
        self::$_testData = new GenerateTestData();
    }

    public function testGetInstalledPkgsAsArray()
    {
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
        //Expexted Array
        $installedPkgs1 = array(
            self::$_testData->getPkg1()->getName() => $record1,
            self::$_testData->getPkg2()->getName() => $record2
        );

        //Actual Array
        $installedPkgs2 = self::$_testData->getPakiti()->getManager("PkgsManager")->getInstalledPkgsAsArray(self::$_testData->getHost());

        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testGetInstalledPkgs()
    {

       $installedPkgs2 = self::$_testData->getPakiti()->getManager("PkgsManager")->getInstalledPkgs(self::$_testData->getHost());

       $this->assertEquals(array(self::$_testData->getPkg1(), self::$_testData->getPkg2()), $installedPkgs2);
    }

    public function testGetInstalledPkgsCount()
    {

        $pkgs_num = self::$_testData->getPakiti()->getManager("PkgsManager")->getInstalledPkgsCount(self::$_testData->getHost());
        $this->assertEquals(2, $pkgs_num);

    }

    public function testAddPkgs()
    {
        $record1 = array(
            "pkgVersion" => self::$_testData->getPkg1()->getVersion(),
            "pkgRelease" => self::$_testData->getPkg1()->getRelease(),
            "pkgArch" => "64"
        );

        $record2 = array(
            "pkgVersion" => "deb7u1",
            "pkgRelease" => self::$_testData->getPkg2()->getRelease(),
            "pkgArch" => self::$_testData->getPkg2()->getArch()
        );
        //Expexted Array
        $installedPkgs1 = array(
            "Test_Package1" => $record1,
            "Test_Package2" => $record2
        );

        self::$_testData->getPakiti()->getManager("PkgsManager")->addPkgs(self::$_testData->getHost(),  $installedPkgs1 );

        $pkg1 =  self::$_testData->getPakiti()->getDao("Pkg")->getPkg("Test_Package1", self::$_testData->getPkg1()->getVersion(), self::$_testData->getPkg1()->getRelease(), "64");
        $pkg2 = self::$_testData->getPakiti()->getDao("Pkg")->getPkg("Test_Package2", "deb7u1", self::$_testData->getPkg2()->getRelease(), self::$_testData->getPkg2()->getArch());


        $this->assertEquals($installedPkgs1, array(
            $pkg1->getName() => array(
                "pkgVersion" => $pkg1->getVersion(),
                "pkgRelease" => $pkg1->getRelease(),
                "pkgArch" => $pkg1->getArch()
                ),
            $pkg2->getName() => array(
                "pkgVersion" => $pkg2->getVersion(),
                "pkgRelease" => $pkg2->getRelease(),
                "pkgArch" => $pkg2->getArch()
            )));


    }

    public function testUpdatePkgs()
    {
        $record1 = array(
            "pkgVersion" => "updated_pkg_vesion",
            "pkgRelease" => self::$_testData->getPkg1()->getRelease(),
            "pkgArch" => self::$_testData->getPkg1()->getArch()
        );

        $record2 = array(
            "pkgVersion" => "updated_pkg_vesion1",
            "pkgRelease" => self::$_testData->getPkg2()->getRelease(),
            "pkgArch" => self::$_testData->getPkg2()->getArch()
        );

        $record3 = array(
            "pkgVersion" => self::$_testData->getPkg1()->getVersion(),
            "pkgRelease" => self::$_testData->getPkg1()->getRelease(),
            "pkgArch" => "64"
        );

        $record4 = array(
            "pkgVersion" => "deb7u1",
            "pkgRelease" => self::$_testData->getPkg2()->getRelease(),
            "pkgArch" => self::$_testData->getPkg2()->getArch()
        );

        $installedPkgs1 = array(
            self::$_testData->getPkg1()->getName() => $record1,
            self::$_testData->getPkg2()->getName() => $record2,
            "Test_Package1" => $record3,
            "Test_Package2" => $record4
        );

        self::$_testData->getPakiti()->getManager("PkgsManager")->updatePkgs(self::$_testData->getHost(), $installedPkgs1);
        $installedPkgs2 = self::$_testData->getPakiti()->getManager("PkgsManager")->getInstalledPkgsAsArray(self::$_testData->getHost());
        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testRemovePkgs()
    {
        $record1 = array(
            "pkgVersion" => self::$_testData->getPkg1()->getVersion(),
            "pkgRelease" => self::$_testData->getPkg1()->getRelease(),
            "pkgArch" => "64"
        );

        $record2 = array(
            "pkgVersion" => "deb7u1",
            "pkgRelease" => self::$_testData->getPkg2()->getRelease(),
            "pkgArch" => self::$_testData->getPkg2()->getArch()
        );

        $installedPkgs1 = array(
            "Test_Package1" => $record1,
            "Test_Package2" => $record2
        );

        self::$_testData->getPakiti()->getManager("PkgsManager")->removePkgs(self::$_testData->getHost(), $installedPkgs1);
    }

    public function testRemoveHostPackages()
    {
        //self::$_testData->getPakiti()->getManager("PkgsManager")->removeHostPackages(self::$_testData->getHost());
        //$instPkgs = self::$_testData->getPakiti()->getManager("PkgsManager")->getInstalledPkgsAsArray(self::$_testData->getHost());
        //$this->assertEquals($instPkgs, array());
    }



    public static function tearDownAfterClass()
    {
       //self::$_testData->deleteGeneratedTestData();
    }
}
