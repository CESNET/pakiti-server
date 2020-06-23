<?php

require_once(realpath(dirname(__FILE__)) . '/../../include/ISubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../lib/SubSource.php');

/**
 * @author Michal Prochazka
 */
class Debian extends SubSource implements ISubSource
{
    protected static $NAME = "Debian Advisories";
    protected static $TYPE = "Debian";
    protected static $DEBIAN_COMPONENTS = [ "non-free", "contrib", "main" ];

    /* lists of binary packages produced from individual source package */
    private $binary_packages = array();

    private function update_list($line, &$list)
    {
        $items = explode(",", $line);
        if (empty($items))
            return;

        $new = array();
        foreach ($items as $item) {
            $val = trim($item);
            if ($val == "")
                continue;
            $new[] = $val;
        }

        $list = array_unique(array_merge($list, $new));
    }

    /* The function(s) below gather mapping between the name of a source packages and resulting
       binary packages, which is used later to resolve names in the DSA descriptions (which refer
       to source package names). The mapping is retrieved from the Source indicies, following the
       description of formats at https://www.debian.org/doc/debian-policy/ (5.4) and
       https://wiki.debian.org/DebianRepository/Format#A.22Sources.22_Indices
     */

    public function update_packages($data, &$sources)
    {
        $num = 0;
        $source = "";
        $binaries = array();
        $in_paragraph = True;
        $in_Binary = False;

        /* can't use strtok here (would conflict with the outer loop) */
        $fp = fopen("php://memory", 'r+');
        fputs($fp, $data);
        rewind($fp);
        while($line = fgets($fp)) {
            $num++;

            /* continuation line */
            if ($line[0] == ' ' || $line[0] == '\t') {
                if (! $in_Binary)
                    goto loop_end;

                self::update_list($line, $binaries);
                goto loop_end;
            }
            $in_Binary = False;
            if ($source != "" && ! empty($binaries)) {
                if (!array_key_exists($source, $sources))
                    $sources[$source] = array();
                $sources[$source] = array_unique(array_merge($sources[$source] + $binaries));
                $source = "";
                $binaries = array();
            }

            $line = trim($line);
            if ($line == "") {
                if ($source != "" || ! empty($binaries)) {
                    fclose($fp);
                    throw new Exception(sprintf("Missing field %s before line $d", ($source) ? "Package" : "Binary", $num));
                }
                $in_paragraph = False;
                goto loop_end;
            }
            $in_paragraph = True;

            $parsed = explode(":", $line, 2);
            $name = $parsed[0];
            $value = $parsed[1];
            $name = trim($name);
            if (strcasecmp($name, "Package") == 0) {
                $source = trim($value);
                goto loop_end;
            }
            if (strcasecmp($name, "Binary") == 0) {
                self::update_list($value, $binaries);

                $in_Binary = True;
                goto loop_end;
            }

loop_end:
            if (!$in_Binary && $source != "" && ! empty($binaries)) {
                if (!array_key_exists($source, $sources))
                    $sources[$source] = array();
                $sources[$source] = array_unique(array_merge($sources[$source] + $binaries));

                $source = "";
                $binaries = array();
            }
        }
        fclose($fp);

        if ($source != "" || ! empty($binaries))
            throw new Exception(sprintf("Missing field %s before line %d", ($source) ? "Package" : "Binary", $num));
    }

    private function update_package_mapping($deb_release)
    {
        $mappings = array();
        foreach (self::$DEBIAN_COMPONENTS as $component) {
            try {
                $data = Utils::downloadContents(sprintf("%s/dists/%s/%s/source/Sources.gz",
                        Config::$DEBIAN_REPOSITORY, $deb_release, $component));
                $this->update_packages($data, $mappings);
            } catch (Exception $e) {
                throw new Exception(sprintf("Error reading Debian Source indices: %s", $e->getMessage()));
            }
        }

        $this->binary_packages[$deb_release] = $mappings;
    }

    private function add_packages($package_template, $deb_release, $source_name, &$list)
    {
        if (!array_key_exists($deb_release, $this->binary_packages))
            $this->update_package_mapping($deb_release);

        $mappings = $this->binary_packages[$deb_release];

        if (! array_key_exists($source_name, $mappings)) {
            /* Sometimes a source package name gets changed/remove, but previously published DSA records
               keep refering to the original name. We proceed with the source name in these cases. */
            $package_template['name'] = $source_name;
            array_push($list, $package_template);
            Utils::log(LOG_NOTICE, sprintf("Failed to resolve source package %s from %s to its binaries",
                    $source_name, $deb_release));
            return;
        }

        foreach($mappings[$source_name] as $bin_name) {
            $package_template['name'] = $bin_name;
            array_push($list, $package_template);
        }
    }

    public function processAdvisories($advisories, $subSourceDef_id)
    {
        $num = 0;
        $defs = array();

        $line = strtok($advisories, "\r\n");
        while ($line !== false) {
            $num++;

            /* Record header */
            $ret = preg_match('/^\[.+\] (D[SL]A-\S+) (.*)$/', $line, $matches);
            if ($ret === 1) {
                if (!empty($rec)) {
                    if (array_key_exists('cves', $rec)) {
                        array_push($defs, $rec);
                    }
                }

                $rec = array();
                $rec['subSourceDefId'] = $subSourceDef_id;
                $rec['definition_id'] = $matches[1];
                $rec['severity'] = "n/a";
                $rec['title'] = $matches[1] . ": " . $matches[2];
                $rec['ref_url'] = "https://security-tracker.debian.org/tracker/" . $matches[1];
                $line = strtok("\r\n");
                continue;
            }

            /* CVEs */
            $ret = preg_match('/^\s+{(.+)}\s*$/', $line, $matches);
            if ($ret === 1) {
                $cves = preg_split("/\s/", $matches[1]);
                if (array_key_exists('cves', $rec)) {
                    Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
                    throw new Exception("Format error (multiple CVE lines) at " . $num);
                }
                $rec['cves'] = array();
                foreach ($cves as $cve) {
                    if (!empty($cve)) {
                        array_push($rec['cves'], $cve);
                    }
                }
                $line = strtok("\r\n");
                continue;
            }

            /* Debian versions affected */
            $ret = preg_match('/^\s+\[(\S+)\]\s+-\s+(\S+)\s+(.*)$/', $line, $matches);
            if ($ret === 1) {
                $deb_release = $matches[1];
                if (in_array($deb_release, Config::$DEBIAN_IGNORED_VERSIONS)) {
                    $line = strtok("\r\n");
                    continue;
                }

                $source_name = $matches[2];
                $list = preg_split("/\s+/", $matches[3]);
                $package_version = $list[0];
                if ($package_version == "<not-affected>" || $package_version == "<unfixed>" || $package_version == "<end-of-life>") {
                    $line = strtok("\r\n");
                    continue;
                }
                /* see deb-version(5) for version number format */
                $ret = preg_match('/^[\.+-:~A-Za-z0-9]+$/', $package_version);
                if ($ret !== 1) {
                    Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
                    throw new Exception("Format error at line " . $num);
                }
                $pos = strrpos($package_version, '-');
                if($pos !== false){
                    $upstream_version = substr($package_version, 0, $pos);
                    $debian_revision = substr($package_version, ($pos + 1) - strlen($package_version), strlen($package_version) - ($pos + 1));
                } else {
                    $upstream_version = $package_version;
                    $debian_revision = "";
                }

                $package_template = array();
                $package_template['version'] = $upstream_version;
                $package_template['release'] = $debian_revision;
                $package_template['operator'] = '<';

                if (!array_key_exists('osGroup', $rec)) {
                    $rec['osGroup'] = array();
                }
                if (!array_key_exists($deb_release, $rec['osGroup'])) {
                    $rec['osGroup'][$deb_release] = array();
                }

                $this->add_packages($package_template, $deb_release, $source_name, $rec['osGroup'][$deb_release]);

                $line = strtok("\r\n");
                continue;
            }

            /* Ignore NOTE */
            $ret = preg_match('/^\s+NOTE:/', $line);
            if ($ret === 1) {
                $line = strtok("\r\n");
                continue;
            }

            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Format error at line " . $num);
        }

        # Add last one
        if (!empty($rec)) {
            if (array_key_exists('cves', $rec)) {
                array_push($defs, $rec);
            }
        }

        return $defs;
    }
}
