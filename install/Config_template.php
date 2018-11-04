<?php

# Global configuration file

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
final class Config
{
    # CONFIG_VERSION
    public static $CONFIG_VERSION = "20171019";

    # Name of this Pakiti instance
    public static $PAKITI_NAME = "";

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
    public static $DEBUG = false;

    # Directory, where to put the reports (only applied for asynchronous mode)
    public static $REPORTS_DIR = "/var/tmp/pakiti-reports/";
    # Should be report compressed?
    public static $COMPRESS_REPORTS = 1;

    # If pakiti-client v3 are used, path to the private key must be defined, in order to decrypt incomming report
    public static $REPORT_DECRYPTION_KEY = "/etc/ssl/localcerts/pakiti3.key";

    # Do we want backup the reports
    public static $BACKUP = false;
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
        # Debian
        "linux-image",
        "linux-image-generic",
        # SUSE
        "kernel-default",
        "kernel-default-devel",
    );

    # List of packages which will be ignored by Pakiti
    public static $IGNORE_PACKAGES = array(
        "kernel-headers",
        "kernel-debug",
        "kernel-source",
        "kernel-firmware",
        "kernel-debug-devel",
        "kernel-kdump",
        # SUSE
        "kernel-syms",
        "kernel-macros",
    );
    # Also the ignore list, but using REGEXP, note that kernel-related packages
    # (recognized by $KERNEL_PACKAGES_NAMES) are never ignored
    public static $IGNORE_PACKAGES_PATTERNS = array(
        ".*-devel$",
        ".*-doc$",
    );

    # Tags to mark CVEs
    public static $TAGS = array(
        "Critical",
        "High",
    );

    # Hosts gui Your favorite settings
    public static $GUI_HOSTS_FAVORITE_FILTERS = array(
        "With tagged CVEs in the last 24 hours" => "tag=true&listTaggedCves=true&activity=24h",
        "Inactive longer than 7 days" => "activity=-7d",
        "Report in the last 48 hours sorted by hostname" => "listTaggedCves=true&activity=48h&sortBy=hostname",
    );

    # Configurable footer
    public static $GUI_FOOTER = "";

    # Names of json variables for import users
    public static $USERS_UID = "login";
    public static $USERS_NAME = "displayName";
    public static $USERS_EMAIL = "mail";
    public static $USERS_ADMIN = "admin";
    public static $USERS_HOSTS_IDS = "hostsIds";
    public static $USERS_HOSTGROUPS_IDS = "hostGroupsIds";
    public static $USERS_HOSTGROUPS_NAMES = "hostGroupsNames";

    # Import users default admin value (if admin variable not defined in import)
    public static $USERS_ADMIN_DEFAULT_VALUE = true;

    # Mapping OS groups to OSes by regular expression
    # You might want to call the recalculateOsGroupsMapping.php tool to adapt the DB on changes
    public static $OS_GROUPS_MAPPING = array(
        # Debian
        "buster" => "Debian.* 10.*",
        "stretch" => "Debian.* 9.*",
        "jessie" => "Debian.* 8.*",
        "wheezy" => "Debian.* 7.*",
        "squeeze" => "Debian.* 6.*",
        "lenny" => "Debian.* 5.*",
        "etch" => "Debian.* 4.*",
        "sarge" => "Debian.* 3\.1.*",
        "woody" => "Debian.* 3\.0.*",
        # Red Hat
        "Red Hat Enterprise Linux 7" => "(Red\s*Hat.* 7.*)|(CentOS.* 7.*)|(Scientific.* 7.*)",
        "Red Hat Enterprise Linux 6" => "(Red\s*Hat.* 6.*)|(CentOS.* 6.*)|(Scientific.* 6.*)",
        "Red Hat Enterprise Linux 5" => "(Red\s*Hat.* 5.*)|(CentOS.* 5.*)|(Scientific.* 5.*)",
        "Red Hat Enterprise Linux 4" => "(Red\s*Hat.* 4.*)|(CentOS.* 4.*)|(Scientific.* 4.*)",
        "Red Hat Enterprise Linux 3" => "(Red\s*Hat.* 3.*)|(CentOS.* 3.*)|(Scientific.* 3.*)",
        # SUSE
        "SUSE Linux Enterprise Server 12 SP3" => "SUSE.* 12\.3.*",
        "SUSE Linux Enterprise Server 12 SP2" => "SUSE.* 12\.2.*",
        "SUSE Linux Enterprise Server 12 SP1" => "SUSE.* 12\.1.*",
        "SUSE Linux Enterprise Server 11 SP4" => "SUSE.* 11\.4.*",
        "SUSE Linux Enterprise Server 11 SP3" => "SUSE.* 11\.3.*",
        "SUSE Linux Enterprise Server 11 SP2" => "SUSE.* 11\.2.*",
        "SUSE Linux Enterprise Server 11 SP1" => "SUSE.* 11\.1.*",
        # openSUSE
        "openSUSE 13.2" => "openSUSE.* 13\.2.*",
        "openSUSE Leap 42.2" => "openSUSE.* 42\.2.*",
        "openSUSE Leap 42.3" => "openSUSE.* 42\.3.*",
        # Ubuntu
        "Ubuntu 12.04 LTS" => "Ubuntu.* 12\.04.*",
        "Ubuntu 14.04 LTS" => "Ubuntu.* 14\.04.*",
        "Ubuntu 16.04 LTS" => "Ubuntu.* 16\.04.*",
        "Ubuntu 18.04 LTS" => "Ubuntu.* 18\.04.*",
        # CentOS
        "CentOS 5" => "CentOS.* 5.*",
        "CentOS 6" => "CentOS.* 6.*",
        "CentOS 7" => "CentOS.* 7.*",
    );
}
