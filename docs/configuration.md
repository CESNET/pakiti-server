# Server configuration of Pakiti

Please check the [installation guide](installation.md) to see how to deploy Pakiti server. This
part describes some of configuration parameters in more details.

## Vulnerability definition system
Pakiti collects information on installed Linux packages from individual machines and correlates
this information with lists of known vulnerabilities that are published by various vendors of
Linux distributions. The server configuration must always reflect the distributions used in
the constituency. Proper definition of the vulnerability sources is a crucial part of the
configuration.

Pakiti uses the notion of a Vulnerability definition system to refer to various formats and
publishing endpoints that are different among Linux distributions. The VDS records are maintained
using the Pakiti GUI. The list below can server as a basis to locate the vulnerability sources
for particular distributions. The information is regularly updated usin a cron job (see the Installation guide)

* Debian DSA:
    * https://salsa.debian.org/security-tracker-team/security-tracker/raw/master/data/DSA/list
* RedHat OVAL:
    * OVAL definitions for releases (recommended)
        * https://www.redhat.com/security/data/oval/com.redhat.rhsa-RHEL7.xml
        * https://www.redhat.com/security/data/oval/com.redhat.rhsa-RHEL8.xml, etc.
    * OVAL definitions per year (might be handy sometimes) 
        * https://www.redhat.com/security/data/oval/com.redhat.rhsa-2018.xml
        * https://www.redhat.com/security/data/oval/com.redhat.rhsa-2019.xml, etc.
* SUSE OVAL:
    * http://ftp.suse.com/pub/projects/security/oval/suse.linux.enterprise.server.12-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.linux.enterprise.desktop.12-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.linux.enterprise.12-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.linux.enterprise.server.11-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.linux.enterprise.desktop.11-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.openstack.cloud.6-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/suse.openstack.cloud.7-patch.xml
* openSUSE OVAL:
    * http://ftp.suse.com/pub/projects/security/oval/opensuse.leap.42.3-patch.xml
    * http://ftp.suse.com/pub/projects/security/oval/opensuse.leap.42.2-patch.xml
* Ubuntu OVAL:
    * https://people.canonical.com/~ubuntu-security/oval/com.ubuntu.trusty.cve.oval.xml
    * https://people.canonical.com/~ubuntu-security/oval/com.ubuntu.xenial.cve.oval.xml
    * https://people.canonical.com/~ubuntu-security/oval/com.ubuntu.bionic.cve.oval.xml

After adding VDS definition you have to use cli for synchronize and calculate vulnerabilities.

    php /var/www/pakiti3/src/modules/cli/vds.php -c synchronize
    php /var/www/pakiti3/src/modules/cli/calculateVulnerabilities.php

##### Local OVAL format
```xml
<?xml version="1.0" encoding="utf-8"?>
<oval_definitions
    xmlns="http://oval.mitre.org/XMLSchema/oval-definitions-5"
    xmlns:oval="http://oval.mitre.org/XMLSchema/oval-common-5"
    xmlns:red-def="http://oval.mitre.org/XMLSchema/oval-definitions-5#linux"
    xmlns:unix-def="http://oval.mitre.org/XMLSchema/oval-definitions-5#unix"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://oval.mitre.org/XMLSchema/oval-common-5 oval-common-schema.xsd http://oval.mitre.org/XMLSchema/oval-definitions-5 oval-definitions-schema.xsd http://oval.mitre.org/XMLSchema/oval-definitions-5#unix unix-definitions-schema.xsd http://oval.mitre.org/XMLSchema/oval-definitions-5#linux linux-definitions-schema.xsd">
 <definitions>
  <definition id="myDefinitionId">
   <metadata>
    <title>myDefinitionTitle</title>
    <reference ref_url="myRefUrl"/>
    <advisory>
     <severity>Severity</severity>
     <cve>myCVE</cve>
     <cve>myCVE2</cve>
    </advisory>
   </metadata>
   <criteria operator="AND">
    <criteria operator="OR">
     <criterion comment="myOS is installed"/>
     <criterion comment="myOS2 is installed"/>
    </criteria>
    <criteria operator="OR">
     <criterion comment="myPackageName is earlier than version-release"/>
     <criterion comment="myPackageName2 is earlier than version2-release2"/>
    </criteria>
   </criteria>
  </definition>
 </definitions>
</oval_definitions>
```

## Oses
Os is assigned to OS Group by regex which is set in Config.php. Correct assign OS to OS Group is necessary for getting vulnerable packages. After changing regex you have to recalculate OSgroup mapping.

    php /var/www/pakiti3/src/modules/cli/recalculateOsGroupsMapping.php

## Users
In Config.php have to be set one of these modes user management

    public static $AUTHZ_MODE = "none";

* none (default) - everyone have all permissions
* auto-create - create new user if doesn't exist, first user will be added as admin
* import - all users have to be added via cli
* manual - all users can be added via cli or gui

User is recognized by environment variable which name is defined in Config.php.

    public static $AUTHZ_UID = "REMOTE_USER";

Settings user permissions for listing Hosts and HostGroups are in Users page in gui.

## Tags
CVEs can be marked by tag. These CVEs are listed in the report back to the client. Option can be used by parameter --report in pakiti-client.
##### Export
    /api/cvesTags_export.php
##### Import via cli
    php /var/www/pakiti3/src/modules/cli/ImportCvesTags.php
        Usage: importCvesTags (-u <url> | --url=<url>) [-r | --remove]
            -u, --url=name  url address which contains xml file with cvesTags
            -r, --remove    remove param use if you want delete all cvesTags which isn't in this import
##### Format import/export
```xml
<cveTags>
    <cveTag>
        <cveName>...</cveName>
        <tagName>...</tagName>
        <reason>...</reason>
        <infoUrl>...</infoUrl>
        <enabled>...</enabled>
        <modifier>...</modifier>
    </cveTag>
</cveTags>
```

## Exceptions
If some local administrator compile its own package and leave the version of the package untouched (only add some additional text), the package will be marked as vulnerable. On Exception page in GUI exceptions can be defined. Select the CVE and the tick the package, which contains the fix, this package will be omitted from the listing of the CVEs.
##### Export
    /api/cvesExceptions_export.php
##### Import via cli
    php /var/www/pakiti3/src/modules/cli/importCvesExceptions.php
        Usage: importCvesExceptions (-u <url> | --url=<url>) [-r | --remove]
            -u, --url=name  url address which contains xml file with cvesExceptions
            -r, --remove    remove param use if you want delete all cvesExceptions which isn't in this import

If pkg->arch, pkg->type or osGroup->name is not set, iterate over all possible.

##### Format import/export
```xml
<cveExceptions>
    <cveException>
        <cveName>...</cveName>
        <reason>...</reason>
        <pkg>
            <name>...</name>
            <version>...</version>
            <release>...</release>
            <arch>...</arch>
            <type>...</type>
        </pkg>
        <osGroup>
            <name>...</name>
        </osGroup>
    </cveException>
</cveExceptions>
```

## One source code for multiple pakiti servers
If you need more pakiti servers on one machine, you can just set environment variable 'PAKITI_CONFIG_FILE' with path to appropriate Config.php in apache configuration.

    SetEnv PAKITI_CONFIG_FILE /etc/pakiti/Config2.php

When using cli module, you must set the --config option to the correct Config.php otherwise the default Config.php will be used. Use the --config option always as the first option.

## Logging
Pakiti logs under the LOCAL0 facility, which usualy ends up in a single log file. The server produces a large amount of records on data it processes, which may be desirable to log separately. The following snippet can help configure rsyslog to split the logging based on the priorities:
```
if $syslogfacility-text == 'local0' then {

    if $programname startswith 'Pakiti' then {
        # every Pakiti log goes to pakiti.log
        /var/log/pakiti.log
    
        # only  WARN's (and above) go to system logs, the rest is discarded
        if not prifilt("local0.warn") then {
            stop
        }

    }

}
```
