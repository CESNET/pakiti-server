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
     * Returns the array of the vulnerable pkgs with assigned Cves for a specific host. Array is sorted by the key.
     * @param Host $host
     * @param string $orderBy
     * @param int $pageSize
     * @param int $pageNum
     * @return mixed
     * @throws Exception
     */

    public function getVulnerablePkgsWithCve(Host &$host, $orderBy = "id", $pageSize = -1, $pageNum = -1)
    {
        if (($host == null) || ($host->getId() == -1)) {
            Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
            throw new Exception("Host object is not valid or Host.id is not set");
        }
        Utils::log(LOG_DEBUG, "Getting the vulnerable packages stored in the DB [hostId=" . $host->getId() . "]", __FILE__, __LINE__);

        $osGroup = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupByOsId($host->getOsId());

        $pkgs = $this->getPakiti()->getDao("Vulnerability")->getVulnerablePkgs($host->getId(), $osGroup->getId(), $orderBy, $pageSize, $pageNum);
        $cves = $this->getPakiti()->getManager("CveDefsManager")->getCvesForHost($host);

        $pkgsWithCves = array();
        foreach ($cves as $pkgId => $pkgCves) {
            foreach ($pkgs as $pkg) {
                if ($pkgId == $pkg->getId()) {
                    $pkgWithCve = array();
                    $pkgWithCve["Pkg"] = $pkg;
                    $pkgWithCve["CVE"] = $pkgCves;
                    $pkgsWithCves[$pkgId] = $pkgWithCve;
                }
            }
        }

        return $pkgsWithCves;
    }

    /**
     * Return Array of arrays that contains keys: [Host], [CVE], [HostGroups]
     * Used in API
     * @param $tag
     * @param $hostgroup
     * @return array
     */
    function getHostsWithCvesThatContainsSomeTag($htag, $hostGroupName, $cveName)
    {
        $hostsWithCvesThatContainsSomeTag = array();
        $this->getPakiti()->getManager("DbManager")->begin();

        if ($htag != "") {
            try {
                $hosts = $this->getPakiti()->getManager("HostsManager")->getHostsByTagName($htag);
            } catch (Exception $e) {
                return $hostsWithCvesThatContainsSomeTag;
            }
        } else {
            $hosts = $this->getPakiti()->getManager("HostsManager")->getHosts("id");
        }


        if ($hostGroupName != "") {
            $hostGroup = $this->getPakiti()->getManager("HostGroupsManager")->getHostGroupByName($hostGroupName);
            if ($hostGroup == null) {
                return $hostsWithCvesThatContainsSomeTag;
            }
        }
        foreach ($hosts as $host) {
            $hostWithCvesThatContainsSomeTag = array();
            $pkgsWithCves = $this->getVulnerablePkgsWithCve($host);
            $cvesWithTag = array();
            foreach ($pkgsWithCves as $pkgWithCves) {
                foreach ($pkgWithCves["CVE"] as $cve) {
                    if (!empty($cve->getTag())) {
                        if ($cveName != "") {
                            if ($cveName == $cve->getName()) array_push($cvesWithTag, $cve);
                        } else {
                            array_push($cvesWithTag, $cve);
                        }

                    }
                }
            }
            if (!empty($cvesWithTag)) {
                $hostWithCvesThatContainsSomeTag["Host"] = $host;
                $hostWithCvesThatContainsSomeTag["CVE"] = $cvesWithTag;

                if ($hostGroupName != "") {
                    $hostGroups = $this->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
                    if (in_array($hostGroupName, array_map(function ($hostGroup) {
                        return $hostGroup->getName();
                    }, $hostGroups))) {
                        $hostWithCvesThatContainsSomeTag["HostGroups"] = array($this->getPakiti()->getManager("HostGroupsManager")->getHostGroupByName($hostGroupName));
                        array_push($hostsWithCvesThatContainsSomeTag, $hostWithCvesThatContainsSomeTag);
                    } else {
                        continue;
                    }

                } else {
                    $hostWithCvesThatContainsSomeTag["HostGroups"] = $this->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
                    array_push($hostsWithCvesThatContainsSomeTag, $hostWithCvesThatContainsSomeTag);

                }

            }


        }
        return $hostsWithCvesThatContainsSomeTag;

    }


    /**
     * Find vulnerable packages for a specific host
     * Save vulnerable pkgId and corresponding cveDefId and osGroupId to PkgCveDef table
     * @throws Exception
     * @param Host $host
     *
     */
    public function calculateVulnerablePkgsForSpecificHost(Host $host)
    {
        if ($host == null || $host->getId() == -1) {
            Utils::log(LOG_DEBUG, "Exception", __FILE__, __LINE__);
            throw new Exception("Host object is not valid or Host.id is not set");
        }

        Utils::log(LOG_DEBUG, "Searching for vulnerable packages for specific host ", __FILE__, __LINE__);

        // If not in Os Group
        $osGroup = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupByOsId($host->getOsId());
        if ($osGroup == null) {
            throw new Exception("Host's OS is not a member of any OsGroup");
        }

        //Get installed Pkgs on Host
        $installedPkgs = $this->getPakiti()->getManager("PkgsManager")->getInstalledPkgs($host);

        //For each vulnerable package get Cvedef
        foreach ($installedPkgs as $installedPkg) {
            $confirmedVulnerabilities = array();
            $potentialVulnerabilities = $this->getPakiti()->getDao("Vulnerability")->getVulnerabilitiesByPkgNameOsGroupIdArch($installedPkg->getName(), $osGroup->getId(), $installedPkg->getArch());
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
                if (!empty($confirmedVulnerabilities)) {
                    $cveDefs = array();
                    foreach ($confirmedVulnerabilities as $confirmedVulnerability) {
                        # Assign the Cvedef to the Package
                        $this->getPakiti()->getManager("CveDefsManager")->assignPkgToCveDef($installedPkg->getId(),
                            $this->getPakiti()->getDao("CveDef")->getCveDefForVulnerability($confirmedVulnerability)->getId(), $osGroup->getId());
                    }
                }
            }
        }
    }

    /**
     * Find vulnerable packages for all hosts
     * Save vulnerable pkgId and corresponding cveDefId and osGroupId to PkgCveDef table
     * @throws Exception
     */

    public function calculateVulnerablePkgsForEachHost()
    {
        Utils::log(LOG_DEBUG, "Searching for vulnerable packages for all hosts", __FILE__, __LINE__);

        $hosts = $this->getPakiti()->getManager("HostsManager")->getHosts("os");
        foreach ($hosts as $host) {
            $this->calculateVulnerablePkgsForSpecificHost($host);
        }
    }

    /**
     * Return array of Vulnerabilities by Cve name and Os name
     * Used by API
     * @param $cveName
     * @param $osName
     * @return array
     */
    public function getVulnerabilitiesByCveNameAndOsName($cveName, $osName)
    {
        Utils::log(LOG_DEBUG, "Searching for vulnerable packages for all hosts", __FILE__, __LINE__);
        $os = $this->getPakiti()->getDao("Os")->getByName($osName);
        if (!is_object($os)) {
            return array();
        }

        $cves = $this->getPakiti()->getDao("Cve")->getCvesByName($cveName);
        if (empty($cves)) {
            return array();
        }

        $osGroup = $this->getPakiti()->getManager("OsGroupsManager")->getOsGroupByOsId($os->getId());
        if (!is_object($osGroup)) {
            return array();
        }

        $cveDefsIds = array_map(function ($cve) {
            return $cve->getCveDefId();
        }, $cves);

        return $this->getPakiti()->getDao("Vulnerability")->getVulnerabilitiesByCveDefsIdsAndOsGroupId($cveDefsIds, $osGroup->getId());
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
    private function dpkgvercmp_in($a, $b)
    {
        $i = 0;
        $j = 0;
        $l = strlen($a) - 1;
        $k = strlen($b) - 1;
        while ($i < $l || $j < $k) {
            $first_diff = 0;
            while (($i < $l && !ctype_digit($a[$i])) || ($j < $k && !ctype_digit($b[$j]))) {
                $vc = $this->order($a[$i]);
                $rc = $this->order($b[$j]);
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
