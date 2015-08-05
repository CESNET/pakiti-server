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

require_once(realpath(dirname(__FILE__)) . '/../../include/ISubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../lib/SubSource.php');
require_once(realpath(dirname(__FILE__)) . '/../../../../common/Pakiti.php');
require_once(realpath(dirname(__FILE__)) . '/../../../../common/Utils.php');
require_once(realpath(dirname(__FILE__)) . '/../../../../managers/DbManager.php');
require_once(realpath(dirname(__FILE__)) . '/../../../../../etc/Config.php');


class Debian extends SubSource implements ISubSource
{
    private static $NAME = "Debian";
    private static $TYPE = "DSA";
    private $_dsaPath = "/tmp/pakiti-debian/DSA/list";

    public function loadDSA()
    {
        $path = realpath('/tmp/pakiti-debian');
        if ($path === false OR !is_dir($path)) {
            mkdir('/tmp/pakiti-debian', $mode = 0755, $recursive = true);
        }

        chdir('/tmp/pakiti-debian/');
        $path = realpath('/tmp/pakiti-debian/DSA');
        if ($path === false OR !is_dir($path)) {
            exec('svn checkout '.$this->getSubSourceDefs()[0]->getUri().' 2>&1', $output, $return_code);
            if ($return_code) {
                die("An error occurred while trying to checkout svn: " . join("\n", $output));
            }

        } else {
            exec('svn up DSA 2>&1', $output, $return_code);
            if ($return_code) {
                die("An error occurred while trying to update svn: " . join("\n", $output));
            }
        }

    }

    public function processDSA()
    {
        $dsaFile = fopen($this->_dsaPath, "r") or die("Unable to open DSA file!");
        $num = 0;
        $defs = array();
        while (($line = fgets($dsaFile)) !== false) {
            $num++;

            /* Record header */
            $ret = preg_match('/^\[.+\] (DSA-\S+) (.*)$/', $line, $matches);
            if ($ret === 1) {
                if (!empty($rec)) {
                    if (array_key_exists('cves', $rec))
                        array_push($defs, $rec);
                }

                $rec = array();
                $rec['subSourceDefId'] = $this->getSubSourceDefs()[0]->getId(); // Only one
                $rec['definition_id'] = $matches[1];
                $rec['severity'] = "n/a";
                $rec['title'] = $matches[1] . ": " . $matches[2];
                $rec['ref_url'] = "https://security-tracker.debian.org/tracker/" . $matches[1];
                continue;
            }

            /* CVEs */
            $ret = preg_match('/^\s+{(.+)}\s*$/', $line, $matches);
            if ($ret === 1) {
                $cves = preg_split("/\s/", $matches[1]);
                if (array_key_exists('cves', $rec))
                    die ("Format error (multiple CVE lines) at " . $num);
                $rec['cves'] = array();
                foreach ($cves as $cve)
                    if (!empty($cve)) {
                        array_push($rec['cves'], $cve);
                    }
                continue;
            }

            /* Debian versions affected */
            $ret = preg_match('/^\s+\[(\S+)\]\s+-\s+(\S+)\s+(.*)$/', $line, $matches);
            if ($ret === 1) {
                $deb_release = $matches[1];
                $name = $matches[2];
                $list = preg_split("/\s+/", $matches[3]);
                $package_version = $list[0];
                if ($package_version == "<not-affected>" ||
                    $package_version == "<unfixed>" ||
                    $package_version == "<end-of-life>"
                ) {
                    continue;
                }
                /* see deb-version(5) for version number format */
                $ret = preg_match('/^[\.+-:~A-Za-z0-9]+$/', $package_version);
                if ($ret !== 1)
                    die ("Format error at line " . $num);
                /* rsplit('-', $package_version): */
                $ver = explode('-', $package_version);
                $debian_revision = array_pop($ver);
                $upstream_version = implode('-', $ver);
                $package = array();
                $package['name'] = $name;
                $package['version'] = $upstream_version;
                $package['release'] = $debian_revision;
                $package['operator'] = "<";
                if (!array_key_exists('osGroup', $rec))
                    $rec['osGroup'] = array();
                if (!array_key_exists($deb_release, $rec['osGroup']))
                    $rec['osGroup'][$deb_release] = array();
                array_push($rec['osGroup'][$deb_release], $package);
                continue;
            }

            /*Ignore NOTE */
            $ret = preg_match('/^\s+NOTE:/', $line);
            if ($ret === 1) {
                continue;
            }


            die ("Format error at line " . $num);
        }

        //Add last one
        if (!empty($rec)) {
            if (array_key_exists('cves', $rec))
                array_push($defs, $rec);
        }

        fclose($dsaFile);
        $this->updateSubSourceLastChecked($this->getSubSourceDefs()[0]);
        return $defs;
    }

    public function retrieveDefinitions()
    {
        Utils::log(LOG_DEBUG, "Retreiving definitions from the " . Debian::getName(), __FILE__, __LINE__);
        $this->loadDSA();
        return $this->processDSA();

    }

    public function getName()
    {
        return Debian::$NAME;
    }

    public function getType()
    {
        return Debian::$TYPE;
    }
}