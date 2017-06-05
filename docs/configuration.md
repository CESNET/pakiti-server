# Configuration

* [Installation](installation.md)

## Pakiti client
Run pakiti-client -h for help

    perl /var/www/pakiti3/bin/pakiti-client -h

##### send report via https (recommended)
    perl /var/www/pakiti3/bin/pakiti-client --url="https://yourdomain.com/feed/"

##### send report via http
    perl /var/www/pakiti3/bin/pakiti-client --url="http://yourdomain.com/feed/" --encrypt="/etc/ssl/localcerts/pakiti3.pem"

If you haven't certificate for encrypt/decrypt report, you can generate it

    mkdir -p /etc/ssl/localcerts
    openssl req -new -x509 -days 365 -nodes -out /etc/ssl/localcerts/pakiti3.pem -keyout /etc/ssl/localcerts/pakiti3.key

Path to the private key must be defined, in order to decrypt incomming report in Config.php

    public static $REPORT_DECRYPTION_KEY = "/etc/ssl/localcerts/pakiti3.key";

## Vulnerability definition system
Add vds definition is necessary in order to calculating vulnerable pkgs. VDS can be added in VDS page via gui.

For example:
* Debian: svn://anonscm.debian.org/svn/secure-testing/data/DSA/
* RedHat OVAL: https://www.redhat.com/security/data/oval/com.redhat.rhsa-2016.xml, https://www.redhat.com/security/data/oval/com.redhat.rhsa-2017.xml, ...

After adding VDS definition you have to use cli for synchronize and recalculate vulnerabilities.

    php /var/www/pakiti3/lib/modules/cli/vds.php -c synchronize
    php /var/www/pakiti3/lib/modules/cli/calculateVulnerabilities.php

It's recommended to synchronize new vulnerabilities every day by cron.

    30 4 * * * root php /var/www/pakiti3/lib/modules/cli/vds.php -c synchronize && php /var/www/pakiti3/lib/modules/cli/calculateVulnerabilities.php

## Oses
Os is assigned to OS Group by regex. Assign OS to OS Group is necessary for getting vulnerable packages.

## Users
In Config.php have to be set one of these modes user management

    public static $AUTHZ_MODE = "none";

* none (default) - everyone have all permissions
* auto-create - create new user if doesn't exist, first user will be added as admin
* import - all users have to be added via cli
* manual - all users can be added via cli or gui

User is recognized by environment variable which name is defined in Config.php.

    public static $AUTHZ_UID = "epuid";

Settings user permissions for listing Hosts and HostGroups are in Users page in gui.

## Tags
CVEs can be marked by tag. These CVEs are listed in the report back to the client. Option can be used by parameter --report in pakiti-client.
##### Export
    /api/cvesTags_export.php
##### Import via cli
    php /var/www/pakiti3/lib/modules/cli/ImportCvesTags.php
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
    php /var/www/pakiti3/lib/modules/cli/importCvesExceptions.php
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

When using cli module, you must set the --config option to the correct Config.php otherwise the default Config.php will be used. Use the --config as the first option.
