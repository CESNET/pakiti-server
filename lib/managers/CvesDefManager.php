<?php

/**
 * User: Vadym Yanovskyy
 * Date: 7/20/15
 * Time: 2:59 PM
 */
class CvesDefManager extends DefaultManager
{
    private $_pakiti;

    public function __construct(Pakiti &$pakiti) {
        $this->_pakiti =& $pakiti;
    }

    public function getPakiti() {
        return $this->_pakiti;
    }

    public function storeCveDef(CveDef &$cveDef){
        $this->getPakiti()->getDao("CveDef")->create($cveDef);
        foreach ($cveDef->getCves() as $cve){
            if ($this->getPakiti()->getDao("Cve")->getCve($cve->getName(), $cveDef->getId()) == null) {
                $cve->setCveDefId($cveDef->getId());
                $this->getPakiti()->getDao("Cve")->create($cve);
            }
        }
    }

    public function FillCves(CveDef &$cveDef)
    {
        $sql = "select * from Cve where Cve.cveDefId={$cveDef->getId()}";
        $cvesDb =& $this->_pakiti->getManager("DbManager")->queryToMultiRow($sql);

        # Create objects
        $cves = array();
        if ($cvesDb != null) {
            foreach ($cvesDb as $cveDb) {
                $cve = new Cve();
                $cve->setId($cveDb["id"]);
                $cve->setName($cveDb["name"]);
                $cve->setCveDefId($cveDb["cveDefId"]);
                array_push($cves, $cve);
            }
            $cveDef->setCves($cves);

        }
    }
}