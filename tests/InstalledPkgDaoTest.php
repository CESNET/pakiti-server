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
class InstalledPkgDaoTest extends \PHPUnit_Framework_TestCase
{
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

    public function testGetIdsByHostId()
    {
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

    public function testgetInstalledPkgsAsArray()
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

        $installedPkgs1 = array(self::$_testData->getPkg1()->getName() => $record1, self::$_testData->getPkg2()->getName() => $record2);

        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $installedPkgs2 = self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getInstalledPkgsAsArray(self::$_testData->getHost());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals($installedPkgs1, $installedPkgs2);
    }

    public function testGetInstalledPkgsCount()
    {
        self::$_testData->getPakiti()->getManager("DbManager")->begin();
        $packages_num= self::$_testData->getPakiti() -> getDao("InstalledPkg") -> getInstalledPkgsCount(self::$_testData->getHost()->getId());
        self::$_testData->getPakiti()->getManager("DbManager")->commit();

        $this->assertEquals(2, $packages_num);
    }


    public static function tearDownAfterClass()
    {
        self::$_testData->deleteGeneratedTestData();
    }
}
