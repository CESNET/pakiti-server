<?php

include(realpath(dirname(__FILE__)) . '/../lib/model/Pkg.php');
include(realpath(dirname(__FILE__)) . '/../lib/model/Host.php');
include(realpath(dirname(__FILE__)) . '/../lib/model/Os.php');
include(realpath(dirname(__FILE__)) . '/../lib/dao/InstalledPkgDao.php');
require(realpath(dirname(__FILE__)) . '/../lib/common/Loader.php');
class GenerateTestData
{

    private static $_pakiti;
    private static $_pkg1;
    private static $_pkg2;
    private static $_os;
    private static $_arch;
    private static $_domain;
    private static $_report;
    private static $_host;
    private static $_installedPkg1;
    private static $_installedPkg2;

    public function __construct()
    {
        self::$_pakiti = new Pakiti;

        //create Arch
        self::$_arch = new Arch();
        self::$_arch->setName("x86_64");

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Arch")->create(self::$_arch);
        self::$_pakiti->getManager("DbManager")->commit();

        //create package
        self::$_pkg1 = new Pkg();
        self::$_pkg1->setName("test_pkg1");
        self::$_pkg1->setVersion("0:11.2.202.428");
        self::$_pkg1->setRelease("1.el5");
        self::$_pkg1->setArch(self::$_arch->getName());

        self::$_pkg2 = new Pkg();
        self::$_pkg2->setName("test_pkg2");
        self::$_pkg2->setVersion("11.12");
        self::$_pkg2->setRelease("release1_BB");
        self::$_pkg2->setArch(self::$_arch->getName());


        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Pkg")->create(self::$_pkg1);
        self::$_pakiti->getDao("Pkg")->create(self::$_pkg2);
        self::$_pakiti->getManager("DbManager")->commit();

        //create OS
        self::$_os = new Os();
        self::$_os->setName("RedHat 7");

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Os")->create(self::$_os);
        self::$_pakiti->getManager("DbManager")->commit();

        //create Domain
        self::$_domain = new Domain();
        self::$_domain->setName("fi.muni.cz");

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Domain")->create(self::$_domain);
        self::$_pakiti->getManager("DbManager")->commit();

        //create Report
        self::$_report = new Report();
        self::$_report->setReceivedOn(strtotime('2013-01-01 00:00:00'));
        self::$_report->setProcessedOn(strtotime('2013-01-01 00:00:01'));
        self::$_report->setTroughtProxy(0);
        self::$_report->setNumOfInstalledPkgs(2);
        self::$_report->setNumOfVulnerablePkgsNorm(0);
        self::$_report->setNumOfVulnerablePkgsSec(0);
        self::$_report->setNumOfCves(0);
        self::$_report->setProxyHostname("proxy");

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Report")->create(self::$_report);
        self::$_pakiti->getManager("DbManager")->commit();

        //create Host
        self::$_host = new Host();
        self::$_host->setHostname("eduroam45.fi.muni.cz");
        self::$_host->setIp("147.251.45.119");
        self::$_host->setReporterHostname("eduroam45.fi.muni.cz");
        self::$_host->setReporterIp("147.251.45.119");
        self::$_host->setKernel("3.2.0-4-486");
        self::$_host->setOs(self::$_os);
        self::$_host->setOsName(self::$_os->getName());
        self::$_host->setOsId(self::$_os->getId());
        self::$_host->setArch(self::$_arch);
        self::$_host->setArchId(self::$_arch->getId());
        self::$_host->setDomain(self::$_domain);
        self::$_host->setDomainId(self::$_domain->getId());
        self::$_host->setLastReportId(self::$_report->getId());
        self::$_host->setType("rpm");
        self::$_host->setOwnRepositoriesDef(0);

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Host")->create(self::$_host);
        self::$_pakiti->getManager("DbManager")->commit();

        //create Installed Pkg
        self::$_installedPkg1 = new InstalledPkg();
        self::$_installedPkg1->setHostId(self::$_host->getId());
        self::$_installedPkg1->setPkgId(self::$_pkg1->getId());

        self::$_installedPkg2 = new InstalledPkg();
        self::$_installedPkg2->setHostId(self::$_host->getId());
        self::$_installedPkg2->setPkgId(self::$_pkg2->getId());

        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("InstalledPkg")->create(self::$_installedPkg1);
        self::$_pakiti->getDao("InstalledPkg")->create(self::$_installedPkg2);
        self::$_pakiti->getManager("DbManager")->commit();
    }


    /**
     * @return mixed
     */
    public function getPakiti()
    {
        return self::$_pakiti;
    }

    /**
     * @return mixed
     */
    public function getPkg1()
    {
        return self::$_pkg1;
    }

    /**
     * @return mixed
     */
    public function getPkg2()
    {
        return self::$_pkg2;
    }

    /**
     * @return mixed
     */
    public function getOs()
    {
        return self::$_os;
    }

    /**
     * @return mixed
     */
    public function getArch()
    {
        return self::$_arch;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return self::$_domain;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return self::$_report;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return self::$_host;
    }

    /**
     * @return mixed
     */
    public function getInstalledPkg1()
    {
        return self::$_installedPkg1;
    }

    /**
     * @return mixed
     */
    public function getInstalledPkg2()
    {
        return self::$_installedPkg2;
    }

    public function deleteGeneratedTestData()
    {
        self::$_pakiti->getManager("DbManager")->begin();
        self::$_pakiti->getDao("Pkg")->delete(self::$_pkg1);
        self::$_pakiti->getDao("Pkg")->delete(self::$_pkg2);
        self::$_pakiti->getDao("Os")->delete(self::$_os);
        self::$_pakiti->getDao("Arch")->delete(self::$_arch);
        self::$_pakiti->getDao("Domain")->delete(self::$_domain);
        self::$_pakiti->getDao("Report")->delete(self::$_report);
        self::$_pakiti->getDao("Host")->delete(self::$_host);
        self::$_pakiti->getDao("InstalledPkg")->delete(self::$_installedPkg1);
        self::$_pakiti->getDao("InstalledPkg")->delete(self::$_installedPkg2);
        self::$_pakiti->getManager("DbManager")->commit();
    }



}