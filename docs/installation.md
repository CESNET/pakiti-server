## Server installation for Debian-based Linux (tested on Debian 9)

#### Install dependencies
    apt-get update
    apt-get install apache2 php php-mysql php-curl php-dom
    apt-get install mysql-server
    apt-get install curl subversion

#### Download repository from github and place it to /var/www
    cd /var/www
    git clone https://github.com/CESNET/pakiti-server

#### Provide configuration settings
    edit /etc/pakiti/Config.php

Override any default settings, as stated in pakiti-server/src/common/DefaultConfig.php. The file has the following structure:

    <?php
     
    final class Config extends DefaultConfig
    {
        public static $DB_HOST = "localhost";
        public static $DB_NAME = "pakiti";
        public static $DB_USER = "pakiti";
        public static $DB_PASSWORD = "password";
        ...
    }

See [Configuration](configuration.md) for more information on server configuration.

#### Run php initDB.php for initalize database and create user which is defined in Config.php
###### use root password (option -h for help)
    php pakiti-server/install/initDB.php -p

#### Extend the web server configuration to enable Pakiti
Pakiti is available via several entry points:
- Public entry page (src/modules/gui/www/public/) with no client authentication, meant as the entry point for users
- Pakiti GUI (/var/www/pakiti-server/src/modules/gui/www/) with controlled access
- The reporting endpoint (src/modules/api/) used by clients to sends reports
- The API endpoint (src/modules/api/) to enable other services get data from Pakiti

The provided template and following steps can be used for Apache web servers:

    cp pakiti-server/install/pakiti3.apache2_template.conf /etc/apache2/sites-available/pakiti.conf

##### Enable sites in pakiti3.apache2.conf
    a2ensite pakiti.conf

##### Enable apache2 modules
    a2enmod ssl
    a2enmod rewrite

##### Reload apache2
    service apache2 restart
