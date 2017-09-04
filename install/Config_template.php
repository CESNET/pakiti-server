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

# Global configuration file

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
final class Config
{

    # Name of this Pakiti instance
    public static $PAKITI_NAME = "Pakiti";

    # Pakiti operational mode
    #   1 - Synchronous mode - process clients reports immediatelly, useful for small deployments with < 1000 hosts
    #   2 - Asynchronous mode - process clients reports from the queue, needed in the deployments with > 1000 hosts
    public static $FEEDER_MODE = 1;

    # MySQL database configuration
    public static $DB_HOST = "localhost";
    public static $DB_USER = "pakiti";
    public static $DB_PASSWORD = "pakiti_password";
    public static $DB_NAME = "pakiti3";

    # Authorization mode (none || auto-create || import || manual)
    public static $AUTHZ_MODE = "none";

    # Name of _SERVER variables for authorization
    public static $AUTHZ_UID = "REMOTE_USER";
    public static $AUTHZ_NAME = "cn";
    public static $AUTHZ_EMAIL = "mail";

    # Debug
    public static $DEBUG = FALSE;

    # Directory, where to put the reports (only applied for asynchronous mode)
    public static $REPORTS_DIR = "/var/tmp/pakiti-reports/";
    # Should be report compressed?
    public static $COMPRESS_REPORTS = 1;

    # If pakiti-client v3 are used, path to the private key must be defined, in order to decrypt incomming report
    public static $REPORT_DECRYPTION_KEY = "/etc/ssl/localcerts/pakiti3.key";

    # Do we want backup the reports
    public static $BACKUP = FALSE;
    public static $BACKUP_DIR = "/var/log/pakitiv3-reports/";

    # Proxy authentication mode (hostname | ip | x509)
    public static $PROXY_AUTHENTICATION_MODE = "hostname";

    # Allowed proxies. Depends on the authentication mode, it should be list of hostnames|ips|X509 Subjects
    public static $PROXY_ALLOWED_PROXIES = array(
        "yourserver.yourdomain.com",
    );

    # Enable - 1/Disable - 0 outgoing proxy for accessing remote repositories and OVAL definitions
    public static $ENABLE_OUTGOING_PROXY = 0;
    public static $OUTGOING_PROXY = "tcp://proxy.example.com:3128";

    # List of packages which will be ignored by Pakiti
    public static $IGNORE_PACKAGES = array(
        "kernel-headers",
        "kernel-debug",
        "kernel-source",
    );
    # Also the ignore list, but using REGEXP
    public static $IGNORE_PACKAGES_PATTERNS = array(
        ".*-devel$",
        ".*-doc$",
    );

    # Packages names which represents kernels
    public static $KERNEL_PACKAGES_NAMES = array(
        "kernel",
        "kernel-devel",
        "kernel-smp",
        "kernel-smp-devel",
        "kernel-xenU",
        "kernel-xenU-devel",
        "kernel-largesmp",
        "kernel-largesmp-devel",
        "kernel-xen",
        "kernel-PAE",
        "kernel-hugemem",
        "linux-image-generic",
        "linux-image-2.4",
        "linux-image-2.6",
    );

    # Tags to mark CVEs
    public static $TAGS = array(
        "Critical",
        "High"
    );

    # Hosts gui Your favorite settings
    public static $GUI_HOSTS_FAVORITE_FILTERS = array(
        "With tagged CVEs in the last 24 hours" => "tag=true&listTaggedCves=true&activity=24h",
        "Inactive longer than 7 days" => "activity=-7d",
        "Report in the last 48 hours sorted by hostname" => "listTaggedCves=true&activity=48h&sortBy=hostname"
    );

    # Names of json variables for import users
    public static $USERS_UID = "login";
    public static $USERS_NAME = "displayName";
    public static $USERS_EMAIL = "mail";
    public static $USERS_ADMIN = "admin";

    # Import users default admin value (if admin variable not defined in import)
    public static $USERS_ADMIN_DEFAULT_VALUE = true;

    # Mapping OS groups to OSes by regular expression
    public static $OS_GROUPS_MAPPING = array(
        "stretch" => "Debian(.*) 9(.*)",
        "jessie" => "Debian(.*) 8(.*)",
        "wheezy" => "Debian(.*) 7(.*)",
        "squeeze" => "Debian(.*) 6(.*)",
        "lenny" => "Debian(.*) 5(.*)",
        "etch" => "Debian(.*) 4(.*)",
        "sarge" => "Debian(.*) 3.1(.*)",
        "woody" => "Debian(.*) 3.0(.*)",
        "Red Hat Enterprise Linux 7" => "(CentOS Linux(.*) 7(.*))|(Scientific Linux(.*) 7(.*))",
        "Red Hat Enterprise Linux 6" => "(CentOS Linux(.*) 6(.*))|(Scientific Linux(.*) 6(.*))",
        "Red Hat Enterprise Linux 5" => "(CentOS Linux(.*) 5(.*))|(Scientific Linux(.*) 5(.*))",
        "Red Hat Enterprise Linux 4" => "(CentOS Linux(.*) 4(.*))|(Scientific Linux(.*) 4(.*))",
        "Red Hat Enterprise Linux 3" => "(CentOS Linux(.*) 3(.*))|(Scientific Linux(.*) 3(.*))",
    );

    # OS names definiton, used for guess OS from installed packages
    public static $OS_NAMES_DEFINITIONS = array(
        "sl-release"     => "Scientific Linux",
        "redhat-release" => "Red Hat Linux",
        "sles-release"   => "SUSE Linux",
        "hpc-release"    => "HPC Linux",
        "centos-release" => "CentOS Linux",
        "fedora-release" => "Fedora Linux",
        "redhat-release-server" => "Red Hat Linux Server",
        "redhat-release-client" => "Red Hat Linux Client",
        "redhat-release-workstation" => "Red Hat Linux Workstation",
        "redhat-release-computenode" => "Red Hat Linux ComputeNode",
    );

    # OS names mapping, used for cannonization of the OS name sent by the client. ? will be replaced by the version/release.
    public static $OS_NAMES_MAPPING = array(
        '^\s*ScientificSL ([\w.-]+)' => 'Scientific Linux ${1}',
        '^\s*Scientific ([\w.-]+)' => 'Scientific Linux ${1}',
        '^\s*ScientificCERNSLC ([\w.-]+)' => 'Scientific Linux ${1}.cern',
        '^\s*RedHatEnterpriseServer ([\w.-]+)' => 'Red Hat Linux Server ${1}',
        '^\s*Scientific Linux SL release ([\w.-]+) .*' => 'Scientific Linux ${1}',
        '^\s*Scientific Linux CERN SLC release ([\w.-]+) .*' => 'Scientific Linux ${1}.cern',
        '^\s*Ubuntu ([\w.-]+)' => 'Ubuntu ${1}',
        '^\s*CentOS ([\w.-]+)' => 'CentOS Linux ${1}',
        '^\s*CentOS release ([\w.-]+) .*' => 'CentOS Linux ${1}',
        '^\s*Fedora ([\w.-]+)' => 'Fedora Linux ${1}',
        '^\s*SUSE LINUX ([\w.-]+)' => 'SUSE Linux ${1}',
        '^\s*Debian ([\w.-\/]+)' => 'Debian ${1}',
    );

}

?>
