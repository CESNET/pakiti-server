<?php
# Copyright (c) 2018, CESNET. All rights reserved.
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
require_once(realpath(dirname(__FILE__)) . '/OvalRedHat.php');

/*
 * When re-building packages, CentOS retains in most cases the versioning of
 * original packages. In some cases, however, packages are changed to contain
 * specific adaptations and the package versions are changed, too.
 * CentOS-specific versioning breaks the vulnerability detection based on
 * information issued by RH since it doesn't contain only the original (RH)
 * version numbers.
 * The differences are in the release part (its dist field), i.e.:
 * 67.el7.centos.6 vs. 67.el7_4.6
 * In order to mitigate the problem we provide this class, which populate the
 * vulnerability database with artifical records reflecting the
 * changed versioning. It constructs two records for any original definition
 * in the RH OVAL, one is its plain copy, the other one is constructed to
 * provide the CentOS versions based on the schema above. It's quite
 * inefficient and still doesn't pick up any security fixes possibly updated
 * by CentOS.
 */

class OvalCentOS extends OvalRedHat implements ISubSource
{
    protected static $NAME = "CentOS OVAL";
    protected static $TYPE = "CentOS";

    public function retrieveDefinitions()
    {
        Utils::log(LOG_DEBUG, "Retrieving definitions from " . $this->getName() . ".", __FILE__, __LINE__);

        $rh_defs = parent::retrieveDefinitions();

        $patterns = array();
        $replacements = array();
        $centos_defs = array();
        foreach ($rh_defs as $rh_def) {
            $centos_def = array();
            $centos_def['subSourceDefId'] = $rh_def['subSourceDefId'];
            $centos_def['definition_id'] = $rh_def['definition_id'];
            $centos_def['severity'] = $rh_def['severity'];
            $centos_def['title'] = $rh_def['title'];
            $centos_def['ref_url'] = $rh_def['ref_url'];
            $centos_def['cves'] = $rh_def['cves'];
            $centos_def['os'] = $rh_def['os'];
            $centos_def['osGroup'] = array();
            foreach ($rh_def['osGroup'] as $rh_osg_name => $defsPkg) {
                if (!preg_match('/Red Hat Enterprise Linux (\d+)/', $rh_osg_name, $redhat_release)) {
                    Utils::log(LOG_ERR, "Unexpected osGroup format reached ($rh_osg_name)");
                    continue;
                }
                $rhrel = $redhat_release[1];

                $patterns[0] = "/\.el${rhrel}(_\d+|)/";
                $replacements[0] = ".el${rhrel}.centos";

                $osGroup_defs = array();
                foreach ($defsPkg as $defPkg) {
                    array_push($osGroup_defs, $defPkg);

                    $osGroup_def = $defPkg;
                    $osGroup_def['release'] = preg_replace($patterns, $replacements, $defPkg['release']);
                    array_push($osGroup_defs, $osGroup_def);
                }

                $centos_osg_name = "CentOS $rhrel";
                $centos_def['osGroup'][$centos_osg_name] = $osGroup_defs;
            }
            array_push($centos_defs, $centos_def);
        }

#        print_r ($rh_defs);
#        echo "============================\n";
#        print_r ($centos_defs);
        return (array_merge($rh_defs, $centos_defs));
    }
}
