<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class VulnerabilitiesManager extends DefaultManager
{
    const EPSILON = 0; /* end of string or character group (denotes an empty string) */

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
            $cveDefsManager->removePkg($pkg->getId());
            $potentialVulnerabilities = $vulnerabilityDao->getVulnerabilitiesByNameArchId($pkg->getName(), $pkg->getArchId());

            # Keep only vulnerability rows that belong to the host's own minor
            # stream (e.g. el9_7 rows for a 9.7-stream kernel), so a CVE fixed in
            # both el9_7 and el9_8 does not flag a patched 9.7 host just because
            # its NVR is below the 9.8 fix's NVR.
            $potentialVulnerabilities = $this->filterByMinorStream($pkg, $potentialVulnerabilities);
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
     * Keep only the Vulnerability rows that belong to the installed package's own
     * minor stream, for rpm-family packages.
     *
     * RHEL clones encode the minor release in the rpm release string: '.el9_7'
     * pins a row to the 9.7 stream, and even without the tag the leading release
     * number identifies the build line (611.x = 9.7, 687.x = 9.8, ...), because
     * RHEL assigns each minor its own base release number. Comparing a 9.7 host
     * against the 9.8 fix NVR is meaningless - the two streams' numbers are not
     * comparable - and produces false positives for patched hosts.
     *
     * Two tiers:
     *   1. Host release carries an explicit '.elX_Y' tag (e.g. 611.55.1.el9_7):
     *      drop rows pinned to a different '.elX_Y' tag.
     *   2. No tag on host (custom builds like 611.55.1.stef.el9): drop rows
     *      explicitly pinned to a minor stream whose leading release number
     *      differs from the host's. Assumes RHEL-family numbering, where
     *      '.elX_Y' tags always come with per-stream base release numbers
     *      (... 570.x = 9.6, 611.x = 9.7, 687.x = 9.8) - which holds for all
     *      real OVAL feeds of that family.
     *
     * Rows without a minor tag are stream-agnostic and always kept. If filtering
     * would drop everything (unknown/unmatched build line), fall back to the
     * original list, so the filter can only remove comparisons it can positively
     * identify as belonging to another stream - never silently reduce coverage.
     *
     * @param Pkg $pkg
     * @param array $vulnerabilities
     * @return array
     */
    private function filterByMinorStream($pkg, $vulnerabilities)
    {
        if ($pkg->getPkgTypeName() !== 'rpm') {
            return $vulnerabilities;
        }

        $hostRelease = $pkg->getRelease();

        # Tier 1 - explicit minor-stream tag on the host, e.g. .el9_7
        if (preg_match('/\.el(\d+)_(\d+)/', $hostRelease, $hostM)) {
            $hostTag = '.el' . $hostM[1] . '_' . $hostM[2];
            $matching = array();
            foreach ($vulnerabilities as $v) {
                if (preg_match('/\.el(\d+)_(\d+)/', $v->getRelease())) {
                    if (strpos($v->getRelease(), $hostTag) !== false) {
                        $matching[] = $v;        # same stream
                    }                            # else pinned to another minor: skip
                } else {
                    $matching[] = $v;            # stream-agnostic row: keep
                }
            }
            return empty($matching) ? $vulnerabilities : $matching;
        }

        # Tier 2 - no tag on host: leading release number is the stream discriminator
        if (preg_match('/^(\d+)/', $hostRelease, $hostN)) {
            $hostBase = intval($hostN[1]);
            $matching = array();
            foreach ($vulnerabilities as $v) {
                $vRel = $v->getRelease();
                # Only rows explicitly pinned to a minor stream can be dropped.
                if (preg_match('/\.el(\d+)_(\d+)/', $vRel) && preg_match('/^(\d+)/', $vRel, $vN)) {
                    if (intval($vN[1]) === $hostBase) {
                        $matching[] = $v;        # same build line (e.g. 611.x)
                    }                            # else different stream numbering: skip
                } else {
                    $matching[] = $v;
                }
            }
            return empty($matching) ? $vulnerabilities : $matching;
        }

        return $vulnerabilities;
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
    public function vercmp($os, $ver_a, $rel_a, $ver_b, $rel_b)
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

    # Used by dpkgvercmp, implements the algorithm described by deb-version(5)
    private function dpkgvercmp_in($a, $b)
    {
        $i = 0;
        $j = 0;

        $length_a = strlen($a);
        $length_b = strlen($b);

        while (True) {

            /* first compare the non-digit part */
            while (True) {
                $value_a = ($i < $length_a) ? $this->order($a[$i]) : self::EPSILON;
                $value_b = ($j < $length_b) ? $this->order($b[$j]) : self::EPSILON;
                if ($value_a > $value_b)
                    return 1;
                if ($value_a < $value_b)
                    return -1;
                /* both a and b are of equal values now */

                if ($value_a == self::EPSILON)
                    break;

                $i++;
                $j++;
            }

            /* now compare the numeric part */
            /* note that every part either starts with a digit or is eos now. */

            /* N.B. when compared against a number an empty string counts as zero */
            $a_num = 0;
            while ($i < $length_a && ctype_digit($a[$i])) {
                $a_num = $a_num * 10 + intval($a[$i]);
                $i++;
            }
            $b_num = 0;
            while ($j < $length_b && ctype_digit($b[$j])) {
                $b_num = $b_num * 10 + intval($b[$j]);
                $j++;
            }

            if ($a_num != $b_num)
                return ($a_num > $b_num) ? 1 : -1;

            if ($i == $length_a) {
                if ($j == $length_b)
                    return 0;
                else
                    return ($b[$j] == "~") ? 1 : -1;
            }
            if ($j == $length_b) {
                if ($i == $length_a)
                    return 0;
                else
                    return ($a[$i] == "~") ? -1 : 1;
            }
        }
    }


    /**
     * Used by dpkgvercmp,
     * ~ < EPSILON < alpha < +,.-
     */
    private function order($val)
    {
        if ($val == '~') {
            return ~PHP_INT_MAX;
        }
        if (ctype_digit($val)) {
            return self::EPSILON;
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
