
####Export (available via http)####
    /api/cvesExceptions_export.php
    /api/cvesTags_export.php


####Import (command line interface)####
    /cli/ImportCvesExceptions.php
        Usage: importCvesExceptions (-u <url> | --url=<url>) [-r | --remove]
            url address which contains xml file with cvesExceptions
            remove param use if you want delete all cvesExceptions before import

    /cli/ImportCvesTags.php
        Usage: importCvesTags (-u <url> | --url=<url>) [-r | --remove]
            url address which contains xml file with cvesTags
            remove param use if you want delete all cvesTags before import


####Structure of exported/imported cvesExceptions####
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

####Structure of exported/imported cvesTags####
```xml
<cveTags>
    <cveTag>
        <cveName>...</cveName>
        <reason>...</reason>
        <infoUrl>...</infoUrl>
        <enabled>...</enabled>
        <tag>
            <name>...</name>
            <description>...</description>
        </tag>
    </cveTag>
</cveTags>
```