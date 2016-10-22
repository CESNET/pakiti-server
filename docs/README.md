##Installation manual for Debian based Linux##

####1, Install apache2, mysql and php5####
    apt-get update
    apt-get install apache2
    apt-get install mysql-server
    apt-get install php5 libapache2-mod-php5 php5-mysql

####2, Download repository from github and place it to /var/www####
    cd /var/www
    git clone https://github.com/CESNET/pakiti3

####3, Copy file pakiti3.apache2.conf to apache2/sites-available####
    cp /var/www/pakiti3/install/pakiti3.apache2.conf /etc/apache2/sites-available

####4, Enable sites in pakiti3.apache2.conf####
    a2ensite pakiti3.apache2.conf

####5, Enable SSL####
    a2enmod ssl
    
####6, Reload apache2####
    service apache2 reload
    
####7, If you haven't certificate for encrypt/decrypt report, you can generate it####
    mkdir -p /etc/ssl/localcerts
    openssl req -new -x509 -days 365 -nodes -out /etc/ssl/localcerts/pakiti3.pem -keyout /etc/ssl/localcerts/pakiti3.key

####8, Edit Config.php####
    edit var/www/pakiti3/etc/Config.php

set username and password to database for pakiti3

    public static $DB_USER = "set pakiti3 mysql username";
    public static $DB_PASSWORD = "set pakiti3 mysql password";

path to private key in order to decrypt incomming reports

    public static $CERN_REPORT_DECRYPTION_KEY = "path to your private key";

####9, Run php initDB.php for initalize database and create user which is defined in Config.php####
login as user who can create databases and users (root)

    php /var/www/pakiti3/install/initDB.php

####10, Install curl and subversion####
    apt-get install curl
    apt-get install subversion

####11, Config and run pakiti-client for sending to pakiti-server####
    perl /var/www/pakiti3/bin/pakiti-client --url="url of pakiti3 server" --encrypt="path to your public key"

####12, Open your browser and go to https://pakiti.com/pakiti3/####
