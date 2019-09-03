<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class VulnerabilitiesManager extends DefaultManager
{
    /**
     * Find vulnerabilities for pkgs
     * Save vulnerable pkgId and corresponding cveDefId and osGroupId to PkgCveDef table
     * @throws Exception
     * @param Array of Pkgs
     *
     */
    public function calculateVulnerabilitiesForPkgs($pkgs)
    {
        Utils::log(LOG_DEBUG, "Calculate Vulnerabilities for Pkgs", __FILE__, __LINE__);

        $vulnerabilityDao = $this->getPakiti()->getDao("Vulnerability");
        $cveDefsManager = $this->getPakiti()->getManager("CveDefsManager");
        foreach ($pkgs as $pkg) {
            $potentialVulnerabilities = $vulnerabilityDao->getVulnerabilitiesByNameArchId($pkg->getName(), $pkg->getArchId());
            foreach ($potentialVulnerabilities as $vulnerability) {
                $confirmed = false;
                switch ($vulnerability->getOperator()) {
                    //TODO: Add more operator cases
                    case "<":
                        $value = $this->vercmp($pkg->getPkgTypeName(), $pkg->getVersion(), $pkg->getRelease(), $vulnerability->getVersion(), $vulnerability->getRelease());
                        if ($value < 0) {
                            $confirmed = true;
                        }
                        break;
                }
                if ($confirmed) {
                    $cveDefsManager->assignPkgToCveDef($pkg->getId(), $vulnerability->getCveDefId(), $vulnerability->getOsGroupId());
                }
            }
        }
    }


    /**
     * Find vulnerabilities for all pkgs
     * Save vulnerable pkgId and corresponding cveDefId and osGroupId to PkgCveDef table
     */
    public function calculateVulnerabilitiesForEachPkg()
    {
        Utils::log(LOG_DEBUG, "Calculate Vulnerabilities for each pkg", __FILE__, __LINE__);
        $pkgs = $this->getPakiti()->getManager("PkgsManager")->getPkgs();
        $this->calculateVulnerabilitiesForPkgs($pkgs);
    }

    public function getVulnerabilities($cveName, $osId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting Vulnerabilities by CVE name[".$cveName."], OS[$osId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Vulnerability");
        $ids = $dao->getIdsByCveNameAndOsId($cveName, $osId);

        $vulnerabilities = array();
        foreach ($ids as $id) {
            array_push($vulnerabilities, $dao->getById($id));
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
        if (($ver_a === $ver_b) && ($rel_a === $rel_b)) {
            return 0;
        }
        switch ($os) {
            case "dpkg":
                return $this->dpkgvercmp($ver_a, $rel_a, $ver_b, $rel_b);
                break;
            case "rpm":
                $cmp_ret = $this->rpmvercmp($this->addepoch($ver_a), $this->addepoch($ver_b));
                if ($cmp_ret == 0) {
                    return $this->rpmvercmp($rel_a, $rel_b);
                } else {
                    return $cmp_ret;
                }
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
            while ($i < $l && !ctype_alnum($a[$i])) {
                $i++;
            }
            if ($i == $l) {
                break;
            }

            $start = $i;
            if (ctype_digit($a[$i])) {
                while ($i < $l && ctype_digit($a[$i])) {
                    $i++;
                }
            } else {
                while ($i < $l && ctype_alpha($a[$i])) {
                    $i++;
                }
            }

            $arr[$j] = substr($a, $start, $i - $start);
            $j++;
        }
        return $arr;
    }

    # Used by dpkgvercmp
    private function dpkgvercmp_in($a, $b)
    {
        $i = 0;
        $j = 0;
        $l = strlen($a);
        $k = strlen($b);

        while ($i < $l && $j < $k) {
            $first_diff = 0;

            while (($i < $l && !ctype_digit($a[$i])) || ($j < $k && !ctype_digit($b[$j]))) {
                $vc = $this->order($a[$i]);
                $rc = $this->order($b[$j]);
                if ($vc != $rc) {
                    return $vc - $rc;
                }
                $i++;
                $j++;
            }

            # Cumulate digits into umber
            $a_num = 0;
            $a_has_num = 0;
            while ($i < $l && ctype_digit($a[$i])) {
                $a_num = $a_num * 10 + $a[$i];
                $a_has_num = 1;
                $i++;
            }
            $b_num = 0;
            $b_has_num = 0;
            while ($j < $k && ctype_digit($b[$j])) {
                $b_num = $b_num * 10 + $b[$j];
                $b_has_num = 1;
                $j++;
            }

            if (($a_has_num && $b_has_num) && $a_num != $b_num) {
                return $a_num == $b_num ? 0 : ($a_num > $b_num ? 1 : -1);
            }
            if ($a_has_num && !$b_has_num) {
                return 1;
            }
            if (!$a_has_num && $b_has_num) {
                return -1;
            }
            if ($a_has_num == $b_has_num && ($i == $l || $j == $k)) {
                return $l == $k ? 0 : ($l > $k ? 1 : -1);
            }
        }

        return $l == $k ? 0 : ($l > $k ? 1 : -1);
    }


    /**
     * Used by dpkgvercmp
     */
    private function order($val)
    {
        if ($val == '') {
            return 0;
        }
        if ($val == '~') {
            return -1;
        }
        if (ctype_digit($val)) {
            return 0;
        }
        if (!ord($val)) {
            return 0;
        }
        if (ctype_alpha($val)) {
            return ord($val);
        }
        return ord($val) + 256;
    }


    /*
     * Compare RPM versions
     * Returns 0 if $a and $b are equal
     * Returns 1 if $a is greater than $b
     * Returns -1 if $a is lower than $b
     */
    private function rpmvercmp($a, $b)
    {
        if (strcmp($a, $b) == 0) {
            return 0;
        }
        $a_arr = $this->rpm_split($a);
        $b_arr = $this->rpm_split($b);
        $arr_len = count($a_arr);
        $barr_len = count($b_arr) - 1;
        for ($i = 0; $i < $arr_len; $i++) {
            if ($i > $barr_len) {
                return 1;
            }
            if (ctype_digit($a_arr[$i]) && ctype_alpha($b_arr[$i])) {
                return 1;
            }
            if (ctype_alpha($a_arr[$i]) && ctype_digit($b_arr[$i])) {
                return -1;
            }
            if ($a_arr[$i] > $b_arr[$i]) {
                return 1;
            }
            if ($a_arr[$i] < $b_arr[$i]) {
                return -1;
            }
        }
        if ($i <= $barr_len) {
            return -1;
        }
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
        # Compare versions
        $r = $this->dpkgvercmp_in($this->addepoch($vera), $this->addepoch($verb));

        if ($r) {
            return $r;
        }

        # Compare release
        return $this->dpkgvercmp_in($rela, $relb);
    }

    /**
     * Add epoch
     */
    private function addepoch($ver)
    {
        if (strpos($ver, ':') === false) {
            return "0:" . $ver;
        }
        return $ver;
    }

    public function storeVulnerabilities($vulnerabilities)
    {
        return $this->getPakiti()->getDao("Vulnerability")->createMultiple($vulnerabilities);
    }

    public function updateVulnerabilities($vulnerabilities)
    {
        $dbManager = $this->getPakiti()->getManager("DbManager");

        $cveDefIds = array();
        foreach ($vulnerabilities as $vuln) {
            $cveDefId = $vuln->getCveDefId();
            if (!in_array($cveDefId, $cveDefIds))
                array_push($cveDefIds, $cveDefId);
        }

        $dbManager->begin();
        try {
            $this->getPakiti()->getDao("Vulnerability")->delete_with_cveDefId($cveDefIds);
            $this->getPakiti()->getDao("Vulnerability")->createMultiple($vulnerabilities);
        } catch (Exception $e) {
            $dbManager->rollback();
            throw $e;
        }
        $dbManager->commit();
    }
}
