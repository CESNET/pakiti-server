# Client usage

## Pakiti client
Run pakiti-client -h for help

    perl /var/www/pakiti3/bin/pakiti-client -h

##### send report via https
    perl /var/www/pakiti3/bin/pakiti-client --url="https://yourdomain.com/feed/"

## GLPI
GLPI is either in your linux distribution or install it according to https://github.com/glpi-project/glpi-agent/releases

#### Create inventory in json format and save to file
    glpi-inventory --json -t Tag > /tmp/glpi
#### Send created inventory to pakiti
    glpi-injector -f /tmp/glpi -u https://yourdomain.com/feed/
