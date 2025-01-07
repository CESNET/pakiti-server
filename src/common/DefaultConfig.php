<?php

class DefaultConfig
{
    public static $CONFIG_VERSION = "20171019";

    # Name of this Pakiti instance
    public static $PAKITI_NAME = "";

    # Pakiti operational mode
    #   1 - Synchronous mode - process clients reports immediately, useful for small deployments with < 1000 hosts
    #   2 - Asynchronous mode - process clients reports from the queue, needed in the deployments with > 1000 hosts
    public static $FEEDER_MODE = 1;

    # MySQL database configuration
    public static $DB_HOST = "localhost";
    public static $DB_USER = "pakiti";
    public static $DB_PASSWORD = "";
    public static $DB_NAME = "pakiti";

    # Authorization mode (none || auto-create || import || manual)
    public static $AUTHZ_MODE = "none";

    # Name of _SERVER variables for authorization
    public static $AUTHZ_UID = "REMOTE_USER";
    public static $AUTHZ_NAME = "cn";
    public static $AUTHZ_EMAIL = "mail";

    public static $DEBUG = false;

    # Directory, where to put the reports (only applied for asynchronous mode)
    public static $REPORTS_DIR = "/var/spool/pakiti/reports";
    public static $COMPRESS_REPORTS = 1;

    # Private key to use when the client sends encrypted reports
    public static $REPORT_DECRYPTION_KEY = "/etc/pakiti/pakiti-key.pem";

    # Do we want backup the reports?
    public static $BACKUP = false;
    public static $BACKUP_DIR = "/var/lib/pakiti";

    # Proxy authentication mode (hostname | ip | x509)
    public static $PROXY_AUTHENTICATION_MODE = "hostname";

    # Allowed proxies. Depends on the authentication mode, it should be list of hostnames|ips|X509 Subjects
    public static $PROXY_ALLOWED_PROXIES = array(
        "monitoring.server",
    );

    # Enable - 1/Disable - 0 outgoing proxy for accessing remote repositories and OVAL definitions
    public static $ENABLE_OUTGOING_PROXY = 0;
    public static $OUTGOING_PROXY = "tcp://proxy.example.com:3128";

    # Package names that represent the linux kernel
    public static $KERNEL_PACKAGES_NAMES = array(
        "kernel",
#        "kernel-devel",
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
        "linux-image-amd64",
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

    # Favorite shortcuts to be made available in the GUI
    public static $GUI_HOSTS_FAVORITE_FILTERS = array(
        "With tagged CVEs in the last 24 hours" => "tag=true&listTaggedCves=true&activity=24h",
        "Inactive longer than 7 days" => "activity=-7d",
        "Report in the last 48 hours sorted by hostname" => "listTaggedCves=true&activity=48h&sortBy=hostname",
    );

    # A footer for the GUI pages
    public static $GUI_FOOTER = "";

    # Names of json variables to import users
    public static $USERS_UID = "login";
    public static $USERS_NAME = "displayName";
    public static $USERS_EMAIL = "mail";
    public static $USERS_ADMIN = "admin";
    public static $USERS_HOSTS_IDS = "hostsIds";
    public static $USERS_HOSTGROUPS_IDS = "hostGroupsIds";
    public static $USERS_HOSTGROUPS_NAMES = "hostGroupsNames";

    # Import users default admin value (if the admin variable isn't defined in import)
    public static $USERS_ADMIN_DEFAULT_VALUE = true;

    # Debian publishes vulnerabilities at a single URL, referring to all distributions. Setting this
    # directive makes the processing a bit more efficient and helps avoid errors when indices for the
    # older distributions are not available.
    public static $DEBIAN_IGNORED_VERSIONS = [ "stretch", "jessie", "wheezy", "squeeze", "lenny", "etch", "sarge", "woody" ];
    # Endpoint to get the Debian Source indices
    public static $DEBIAN_REPOSITORY = "http://ftp.debian.org/debian";

    # Mapping OS groups to OSes by regular expression
    # Don't forget to re-process existing records in the DB on any change, e.g. using recalculateOsGroupsMapping.php
    public static $OS_GROUPS_MAPPING = array(
        # Debian
        "bookworm" => "Debian.* 12.*",
        "bullseye" => "Debian.* 11.*",
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
        "Red Hat Enterprise Linux 9" => "(Red\s*Hat.* 9.*)|(CentOS.* 9.*)|(Scientific.* 9.*)|(AlmaLinux.* 9.*)|(Rocky.* 9.*)",
        "Red Hat Enterprise Linux 8" => "(Red\s*Hat.* 8.*)|(CentOS.* 8.*)|(Scientific.* 8.*)|(AlmaLinux.* 8.*)|(Rocky.* 8.*)",
        "Red Hat Enterprise Linux 7" => "(Red\s*Hat.* 7.*)|(CentOS.* 7.*)|(Scientific.* 7.*)",
        "Red Hat Enterprise Linux 6" => "(Red\s*Hat.* 6.*)|(CentOS.* 6.*)|(Scientific.* 6.*)",
        "Red Hat Enterprise Linux 5" => "(Red\s*Hat.* 5.*)|(CentOS.* 5.*)|(Scientific.* 5.*)",
        "Red Hat Enterprise Linux 4" => "(Red\s*Hat.* 4.*)|(CentOS.* 4.*)|(Scientific.* 4.*)",
        "Red Hat Enterprise Linux 3" => "(Red\s*Hat.* 3.*)|(CentOS.* 3.*)|(Scientific.* 3.*)",
        # SUSE
        "SUSE Linux Enterprise Server 15 SP5" => "SUSE.* 15\.5.*",
        "SUSE Linux Enterprise Server 15 SP4" => "SUSE.* 15\.4.*",
        "SUSE Linux Enterprise Server 15 SP3" => "SUSE.* 15\.3.*",
        "SUSE Linux Enterprise Server 15 SP2" => "SUSE.* 15\.2.*",
        "SUSE Linux Enterprise Server 15 SP1" => "SUSE.* 15\.1.*",
        "SUSE Linux Enterprise Server 15" => "(SUSE.* 15\.0.*)|(SUSE.* 15)",
        "SUSE Linux Enterprise Server 12 SP5" => "SUSE.* 12\.5.*",
        "SUSE Linux Enterprise Server 12 SP4" => "SUSE.* 12\.4.*",
        "SUSE Linux Enterprise Server 12 SP3" => "SUSE.* 12\.3.*",
        "SUSE Linux Enterprise Server 12 SP2" => "SUSE.* 12\.2.*",
        "SUSE Linux Enterprise Server 12 SP1" => "SUSE.* 12\.1.*",
        "SUSE Linux Enterprise Server 12" => "(SUSE.* 12\.0.*)|(SUSE.* 12)",
        "SUSE Linux Enterprise Server 11 SP4" => "SUSE.* 11\.4.*",
        "SUSE Linux Enterprise Server 11 SP3" => "SUSE.* 11\.3.*",
        "SUSE Linux Enterprise Server 11 SP2" => "SUSE.* 11\.2.*",
        "SUSE Linux Enterprise Server 11 SP1" => "SUSE.* 11\.1.*",
        "SUSE Linux Enterprise Server 11" => "(SUSE.* 11\.0.*)|(SUSE.* 11)",
        # openSUSE
        "openSUSE 13.2" => "openSUSE.* 13\.2.*",
        "openSUSE Leap 42.1" => "openSUSE.* 42\.1.*",
        "openSUSE Leap 42.2" => "openSUSE.* 42\.2.*",
        "openSUSE Leap 42.3" => "openSUSE.* 42\.3.*",
        "openSUSE Leap 15.0" => "openSUSE.* 15\.0.*",
        "openSUSE Leap 15.1" => "openSUSE.* 15\.1.*",
        "openSUSE Leap 15.2" => "openSUSE.* 15\.2.*",
        "openSUSE Leap 15.3" => "openSUSE.* 15\.3.*",
        # Ubuntu
        "Ubuntu 4.10" => "Ubuntu.* 4\.10.*",
        "Ubuntu 5.04" => "Ubuntu.* 5\.04.*",
        "Ubuntu 5.10" => "Ubuntu.* 5\.10.*",
        "Ubuntu 6.04 LTS" => "Ubuntu.* 6\.04.*",
        "Ubuntu 6.10" => "Ubuntu.* 6\.10.*",
        "Ubuntu 7.04" => "Ubuntu.* 7\.04.*",
        "Ubuntu 7.10" => "Ubuntu.* 7\.10.*",
        "Ubuntu 8.04 LTS" => "Ubuntu.* 8\.04.*",
        "Ubuntu 8.10" => "Ubuntu.* 8\.10.*",
        "Ubuntu 9.04" => "Ubuntu.* 9\.04.*",
        "Ubuntu 9.10" => "Ubuntu.* 9\.10.*",
        "Ubuntu 10.04 LTS" => "Ubuntu.* 10\.04.*",
        "Ubuntu 10.10" => "Ubuntu.* 10\.10.*",
        "Ubuntu 11.04" => "Ubuntu.* 11\.04.*",
        "Ubuntu 11.10" => "Ubuntu.* 11\.10.*",
        "Ubuntu 12.04 LTS" => "Ubuntu.* 12\.04.*",
        "Ubuntu 12.10" => "Ubuntu.* 12\.10.*",
        "Ubuntu 13.04" => "Ubuntu.* 13\.04.*",
        "Ubuntu 13.10" => "Ubuntu.* 13\.10.*",
        "Ubuntu 14.04 LTS" => "Ubuntu.* 14\.04.*",
        "Ubuntu 14.10" => "Ubuntu.* 14\.10.*",
        "Ubuntu 15.04" => "Ubuntu.* 15\.04.*",
        "Ubuntu 15.10" => "Ubuntu.* 15\.10.*",
        "Ubuntu 16.04 LTS" => "Ubuntu.* 16\.04.*",
        "Ubuntu 16.10" => "Ubuntu.* 16\.10.*",
        "Ubuntu 17.04" => "Ubuntu.* 17\.04.*",
        "Ubuntu 17.10" => "Ubuntu.* 17\.10.*",
        "Ubuntu 18.04 LTS" => "Ubuntu.* 18\.04.*",
        "Ubuntu 18.10" => "Ubuntu.* 18\.10.*",
        "Ubuntu 19.04" => "Ubuntu.* 19\.04.*",
        "Ubuntu 19.10" => "Ubuntu.* 19\.10.*",
        "Ubuntu 20.04 LTS" => "Ubuntu.* 20\.04.*",
        "Ubuntu 20.10" => "Ubuntu.* 20\.10.*",
        "Ubuntu 21.04" => "Ubuntu.* 21\.04.*",
        "Ubuntu 21.10" => "Ubuntu.* 21\.10.*",
        "Ubuntu 22.04 LTS" => "Ubuntu.* 22\.04.*",
        "Ubuntu 22.10" => "Ubuntu.* 22\.10.*",
        "Ubuntu 23.04" => "Ubuntu.* 23\.04.*",
        "Ubuntu 23.10" => "Ubuntu.* 23\.10.*",
        "Ubuntu 24.04 LTS" => "Ubuntu.* 24\.04.*",
        "Ubuntu 24.10" => "Ubuntu.* 24\.10.*",
        # CentOS
        "CentOS 5" => "CentOS.* 5.*",
        "CentOS 6" => "CentOS.* 6.*",
        "CentOS 7" => "CentOS.* 7.*",
        "CentOS 8" => "CentOS.* 8.*",
        "CentOS 9" => "CentOS.* 9.*",
        # AlmaLinux
        "AlmaLinux 8" => "AlmaLinux.* 8.*",
        "AlmaLinux 9" => "AlmaLinux.* 9.*",
    );
}
