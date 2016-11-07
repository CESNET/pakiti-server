
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
<xml>
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
</xml>
```

####Structure of exported/imported cvesTags####
```xml
<xml>
    <cveTag>
        <cveName>...</cveName>
        <reason>...</reason>
        <enabled>...</enabled>
        <tag>
            <name>...</name>
            <description>...</description>
        </tag>
    </cveTag>
</xml>
```