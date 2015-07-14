<?php

include('GenerateTestData.php');

class PkgDaoTest extends PHPUnit_Framework_TestCase {

    private static $_testData;

    public static function setUpBeforeClass()
    {   
        self::$_testData = new GenerateTestData();
    }

    public function testGet()
    {
        //get
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $pkg3 = self::$_testData->getPakiti() -> getDao("Pkg") -> getPkg("test_pkg1", "11.11", "release1_BB", self::$_testData->getArch()->getName());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals(self::$_testData->getPkg1()->getId(), $pkg3->getId());
    }

    public function testGetById(){

        //get by Id
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $pkg3 = self::$_testData->getPakiti() -> getDao("Pkg") -> getById(self::$_testData->getPkg1()->getId());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals(self::$_testData->getPkg1()->getId(), $pkg3->getId());

    }

    public function testGetPkgsByPkgIds()
    {
        $ids = array(self::$_testData->getPkg1()->getId(), self::$_testData->getPkg2()->getId());
        $pkgs1 = array(self::$_testData->getPkg1(), self::$_testData->getPkg2());

        //test testGetPkgsByPkgIds
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $pkgs2 = self::$_testData->getPakiti() -> getDao("Pkg") -> getPkgsByPkgIds($ids);
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals($pkgs1, $pkgs2);
    }

    public function testUpdate()
    {
        //change package name
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        self::$_testData->getPkg1()->setName("test3_pkg03");
        self::$_testData->getPakiti() -> getDao("Pkg") -> update(self::$_testData->getPkg1());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        //get by Id
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $pkg3 = self::$_testData->getPakiti() -> getDao("Pkg") -> getById(self::$_testData->getPkg1()->getId());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertNotEquals(self::$_testData->getPkg1(), $pkg3);

    }


    public static function tearDownAfterClass()
    {
        self::$_testData->deleteGeneratedTestData();   
    }


}
