# Client usage

## Pakiti client
Run pakiti-client -h for help

    perl /var/www/pakiti3/bin/pakiti-client -h

##### send report via https (recommended)
    perl /var/www/pakiti3/bin/pakiti-client --url="https://yourdomain.com/feed/"

It may be necessary to specify the location with CA root certificate using the HTTPS_CA_DIR environment, variable, e.g. HTTPS_CA_DIR=/etc/grid-security/certificates.

##### send report via http
    perl /var/www/pakiti3/bin/pakiti-client --url="http://yourdomain.com/feed/" --encrypt="/etc/ssl/localcerts/pakiti3.pem"

If you haven't certificate for encrypt/decrypt report, you can generate it

    mkdir -p /etc/ssl/localcerts
    openssl req -new -x509 -days 365 -nodes -out /etc/ssl/localcerts/pakiti3.pem -keyout /etc/ssl/localcerts/pakiti3.key

Path to the private key must be defined, in order to decrypt incomming report in Config.php

    public static $REPORT_DECRYPTION_KEY = "/etc/ssl/localcerts/pakiti3.key";
