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

require_once(realpath(dirname(__FILE__)) . '/../../include/ISubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../lib/SubSource.php');

/**
 * @author Michal Prochazka
 */
class Debian extends SubSource implements ISubSource
{
    protected static $NAME = "Debian";
    protected static $TYPE = "DSA";

    public function processDSA($dsa)
    {
        $currentSubSourceHash = $this->computeHash($dsa);
        $num = 0;
        $defs = array();

        if (! $this->isSubSourceDefContainsNewData($this->getSubSourceDefs()[0], $currentSubSourceHash)) {
            $this->updateSubSourceLastChecked($this->getSubSourceDefs()[0]);
            return $defs;
        }

        $line = strtok($dsa, "\r\n");
        while ($line !== false) {
            $num++;

            /* Record header */
            $ret = preg_match('/^\[.+\] (DSA-\S+) (.*)$/', $line, $matches);
            if ($ret === 1) {
                if (!empty($rec)) {
                    if (array_key_exists('cves', $rec)) {
                        array_push($defs, $rec);
                    }
                }

                $rec = array();
                $rec['subSourceDefId'] = $this->getSubSourceDefs()[0]->getId(); // Only one
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
                $name = $matches[2];
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
                $package = array();
                $package['name'] = $name;
                $package['version'] = $upstream_version;
                $package['release'] = $debian_revision;
                $package['operator'] = '<';
                if (!array_key_exists('osGroup', $rec)) {
                    $rec['osGroup'] = array();
                }
                if (!array_key_exists($deb_release, $rec['osGroup'])) {
                    $rec['osGroup'][$deb_release] = array();
                }
                array_push($rec['osGroup'][$deb_release], $package);
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

        $this->updateLastSubSourceDefHash($this->getSubSourceDefs()[0], $currentSubSourceHash);
        $this->updateSubSourceLastChecked($this->getSubSourceDefs()[0]);

        return $defs;
    }

    public function retrieveDefinitions()
    {
        Utils::log(LOG_DEBUG, "Retreiving definitions from the " . Debian::getName(), __FILE__, __LINE__);
        if (empty($this->getSubSourceDefs()))
            return array();

        $dsa = file_get_contents($this->getSubSourceDefs()[0]->getUri());
        if ($dsa === False) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("An error occurred while trying to retrieve Debian DSA (" .
                file_get_contents($this->getSubSourceDefs()[0]->getUri()) . ")");
        }

        return ($this->processDSA($dsa));
    }
}
