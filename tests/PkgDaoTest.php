<?php
# Copyright (c) 2017, CESNET. All rights reserved.
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

include('GenerateTestData.php');

/**
 * @author Michal Prochazka
 */
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
