<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

class VulnerabilitiesManager extends DefaultManager
{
    private $_pakiti;

    public function __construct(Pakiti &$pakiti)
    {
        $this->_pakiti =& $pakiti;
    }

    public function getPakiti()
    {
        return $this->_pakiti;
    }

    public function getVulnerabilityById($id)
    {
        return $this->getPakiti()->getDao("Vulnerability")->getById($id);
    }

    /**
     * Load Host vulnerable packages
     * Save vulnerable pkgId and corresponding cveDefId and osGroupId to PkgCveDef table
     * @param Host $host
     *
     */
    public function loadVulnerablePkgs(Host $host)
    {
        $osGroup = $this->_pakiti->getManager("HostsManager")->getHostOsGroup($host);

        //Get installed Pkgs on Host
        $installedPkgs = $this->_pakiti->getManager("PkgsManager")->getInstalledPkgs($host);

        //For each vulnerable package get Cvedef
        foreach ($installedPkgs as $installedPkg) {
            $confirmedVulnerabilities = array();
            $potentialVulnerabilities = $this->getVulnerabilitiesByPkgNameOsGroupIdArch($installedPkg->getName(), $osGroup->getId(), $installedPkg->getArch());
            if (!empty($potentialVulnerabilities)) {
                foreach ($potentialVulnerabilities as $potentialVulnerability) {
                    switch ($potentialVulnerability->getOperator()) {
                        //TODO: Add more operator cases
                        case "<":
                            if ($this->vercmp($host->getType(), $installedPkg->getVersion(), $installedPkg->getRelease(), $potentialVulnerability->getVersion(), $potentialVulnerability->getRelease()) < 0) {
                                array_push($confirmedVulnerabilities, $potentialVulnerability);
                            }
                    }
                }
                //For each confirmed Vulnerability get CveDefs
                if (!empty($confirmedVulnerabilities)){
                    $cveDefs = array();
                    foreach ($confirmedVulnerabilities as $confirmedVulnerability) {
                        # Assign the Cvedef to the Package
                        //TODO: Delete previos results or not?
                        $sql = "insert ignore into PkgCveDef set pkgId=".$installedPkg->getId().", cveDefId=".$this->getCveDefForVulnerability($confirmedVulnerability)->getId().",
                        osGroupId=".$osGroup->getId();
                        $this->_pakiti->getManager("DbManager")->query($sql);
                    }
                }
            }
        }
    }

    public function getCvesForVulnerablePkgs(Host $host){

        $pkgsCves = array();

        //Get OS group
        $osGroup = $this->_pakiti->getManager("HostsManager")->getHostOsGroup($host);

        //Get installed Pkgs on Host
        $installedPkgs = $this->_pakiti->getManager("PkgsManager")->getInstalledPkgs($host);

        //Get CveDef for Vulnerable packages
        foreach($installedPkgs as $installedPkg) {
            $sql = "select * from CveDef inner join PkgCveDef on CveDef.id = PkgCveDef.cveDefId
                    where PkgCveDef.pkgId={$installedPkg->getId()} and PkgCveDef.osGroupId={$osGroup->getId()}";
            $cveDefsDb =& $this->_pakiti->getManager("DbManager")->queryToMultiRow($sql);

            # Create objects
            $cveDefs = array();
            if ($cveDefsDb != null) {
                foreach ($cveDefsDb as $cveDefDb) {
                    $cveDef = new CveDef();
                    $cveDef->setId($cveDefDb["id"]);
                    $cveDef->setDefinitionId($cveDefDb["definitionId"]);
                    $cveDef->setTitle($cveDefDb["title"]);
                    $cveDef->setRefUrl($cveDefDb["refUrl"]);
                    $cveDef->setVdsSubSourceDefId($cveDefDb["vdsSubSourceDefId"]);
                    $this->_pakiti->getManager("CvesDefManager")->FillCves($cveDef);
                    array_push($cveDefs, $cveDef);
                }
            }

            $cves = array();
            foreach ($cveDefs as $cveDef) {
                foreach($cveDef->getCves() as $cve) {
                    array_push($cves, $cve->getName());
                }
            }

            $pkgsCves[$installedPkg->getId()] = $cves;
        }
        return $pkgsCves;
    }

    private function getCveDefForVulnerability(Vulnerability $vul)
    {
        $sql = "select id as _id, definitionId as _definitionId, title as _title, refUrl as _refUrl, vdsSubSourceDefId as _vdsSubSourceDefId from CveDef where CveDef.id=(select Vulnerability.cveDefId from Vulnerability where Vulnerability.id={$vul->getId()})";
        $cveDef = $this->_pakiti->getManager("DbManager")->queryObject($sql, "CveDef");
        return $cveDef;
    }

    /**
     * Return array of Vulnerabilities for a specific Package name, Os Group and Arch name
     * @param $name
     * @param $osGroupId
     * @param $arch
     * @return array
     */
    private function getVulnerabilitiesByPkgNameOsGroupIdArch($name, $osGroupId, $arch)
    {

        $sql = "select * from Vulnerability where Vulnerability.name='" . $this->_pakiti->getManager("DbManager")->escape($name) . "'
                and Vulnerability.osGroupId={$osGroupId} and (Vulnerability.arch='all' or Vulnerability.arch='" . $this->_pakiti->getManager("DbManager")->escape($arch) . "')";

        $vulnerabilitiesDb =& $this->_pakiti->getManager("DbManager")->queryToMultiRow($sql);

        # Create objects
        $vulnerabilities = array();
        if ($vulnerabilitiesDb != null) {
            foreach ($vulnerabilitiesDb as $vulnerabilityDb) {
                $vulnerability = new Vulnerability();
                $vulnerability->setId($vulnerabilityDb["id"]);
                $vulnerability->setName($vulnerabilityDb["name"]);
                $vulnerability->setVersion($vulnerabilityDb["version"]);
                $vulnerability->setRelease($vulnerabilityDb["release"]);
                $vulnerability->setArch($vulnerabilityDb["arch"]);
                $vulnerability->setOsGroupId($vulnerabilityDb["osGroupId"]);
                $vulnerability->setOperator($vulnerabilityDb["operator"]);
                $vulnerability->setCveDefId($vulnerabilityDb["cveDefId"]);
                array_push($vulnerabilities, $vulnerability);
            }
        }
        return $vulnerabilities;
    }

    /*
     * Compare packages version based on type of packages
     * deb - compare version first, it they are equal then compare releases
     * rpm - compare version and release together
     * Returns 0 if $a and $b are equal
     * Returns 1 if $a is greater than $b
     * Returns -1 if $a is lower than $b
     */

    private function vercmp($os, $ver_a, $rel_a, $ver_b, $rel_b)
    {
        if (($ver_a === $ver_b) && ($rel_a === $rel_b)) return 0;
        switch ($os) {
            case "dpkg":
                # We need to split version and release
                if (strpos($ver_a, '-')) {
                    $vera = substr($ver_a, 0, strpos($ver_a, '-'));
                    $rela = substr($ver_a, strpos($ver_a, '-') + 1);
                } else {
                    $vera = $ver_a;
                    $rela = $rel_a;
                }
                if (strpos($ver_b, '-')) {
                    $verb = substr($ver_b, 0, strpos($ver_b, '-'));
                    $relb = substr($ver_b, strpos($ver_b, '-') + 1);
                } else {
                    $verb = $ver_b;
                    $relb = $rel_b;
                }
                return $this->dpkgvercmp($vera, $rela, $verb, $relb);
                break;
            case "rpm":
                $cmp_ret = $this->rpmvercmp($ver_a, $ver_b);
                if ($cmp_ret == 0)
                    return $this->rpmvercmp($rel_a, $rel_b);
                else return $cmp_ret;
                break;
            default:
                return $this->rpmvercmp($ver_a . "-" . $rel_a, $ver_b . "-" . $rel_b);
        }
    }

    private function rpm_split($a)
    {
        $arr = array();
        $i = 0;
        $j = 0;
        $l = strlen($a);
        while ($i < $l) {
            while ($i < $l && !ctype_alnum($a[$i]))
                $i++;
            if ($i == $l)
                break;

            $start = $i;
            if (ctype_digit($a[$i])) {
                while ($i < $l && ctype_digit($a[$i]))
                    $i++;
            } else {
                while ($i < $l && ctype_alpha($a[$i]))
                    $i++;
            }

            $arr[$j] = substr($a, $start, $i - $start);
            $j++;
        }
        return $arr;
    }

    /*
     * Used by dpkgvercmp
     */
    private function order($val)
    {
        if ($val == '') return 0;
        if ($val == '~') return -1;
        if (ctype_digit($val)) return 0;
        if (!ord($val)) return 0;
        if (ctype_alpha($val)) return ord($val);
        return ord($val) + 256;
    }

    /*
     * Used by dpkgvercmp
     */
    private function dpkgvercmp_in($a, $b)
    {
        $i = 0;
        $j = 0;
        $l = strlen($a) - 1;
        $k = strlen($b) - 1;
        while ($i < $l || $j < $k) {
            $first_diff = 0;
            while (($i < $l && !ctype_digit($a[$i])) || ($j < $k && !ctype_digit($b[$j]))) {
                $vc = order($a[$i]);
                $rc = order($b[$j]);
                if ($vc != $rc) return $vc - $rc;
                $i++;
                $j++;
            }
            while ($i < $l && $a[$i] == '0') $i++;
            while ($j < $k && $b[$j] == '0') $j++;
            while (($i < $l && ctype_digit($a[$i])) && ($j < $k && ctype_digit($b[$j]))) {
                if (!$first_diff) $first_diff = ord($a[$i]) - ord($b[$j]);
                $i++;
                $j++;
            }
            if ($i == $j && (ctype_digit($a[$i]) && ctype_digit($b[$j]))) return strcmp($a[$i], $b[$j]);
            if (ctype_digit($a[$i])) return 1;
            if (ctype_digit($b[$j])) return -1;
            if ($first_diff) return $first_diff;
        }
        return 0;
    }

    /*
    * Compare  RPM versions
    * Returns 0 if $a and $b are equal
    * Returns 1 if $a is greater than $b
    * Returns -1 if $a is lower than $b
    */

    private function rpmvercmp($a, $b)
    {
        if (strcmp($a, $b) == 0) return 0;
        $a_arr = $this->rpm_split($a);
        $b_arr = $this->rpm_split($b);
        $arr_len = count($a_arr);
        $barr_len = count($b_arr) - 1;
        for ($i = 0; $i < $arr_len; $i++) {
            if ($i > $barr_len)
                return 1;
            if (ctype_digit($a_arr[$i]) && ctype_alpha($b_arr[$i]))
                return 1;
            if (ctype_alpha($a_arr[$i]) && ctype_digit($b_arr[$i]))
                return -1;
            if ($a_arr[$i] > $b_arr[$i])
                return 1;
            if ($a_arr[$i] < $b_arr[$i])
                return -1;
        }
        if ($i <= $barr_len)
            return -1;
        return 0;
    }

    /*
     * Compare DPKG versions
     * Returns 0 if $a and $b are equal
     * Returns 1 if $a is greater than $b
     * Returns -1 if $a is lower than $b
     */
    private function dpkgvercmp($vera, $rela, $verb, $relb)
    {
        # Get epoch
        $epoch_a = substr($vera, 0, strpos($vera, ':'));
        $epoch_b = substr($verb, 0, strpos($verb, ':'));
        # If epoch is not there => 0
        if ($epoch_a == "") $epoch_a = "0";
        if ($epoch_b == "") $epoch_b = "0";
        if ($epoch_a > $epoch_b) return 1;
        if ($epoch_a < $epoch_b) return -1;
        # Compare versions
        $r = $this->dpkgvercmp_in($vera, $verb);
        if ($r) {
            return $r;
        }
        # Compare release
        return $this->dpkgvercmp_in($rela, $relb);
    }

    public function storeVulnerabilities(&$vulnerabilities)
    {
        return $this->getPakiti()->getDao("Vulnerability")->createMultiple($vulnerabilities);
    }


}
