## Server installation for Debian-based Linux

#### Install apache2, mysql, php5, curl and subversion
    apt-get update
    apt-get install apache2
    apt-get install mysql-server
    apt-get install curl
    apt-get install php5 libapache2-mod-php5 php5-mysql php5-curl
    apt-get install subversion

#### Download repository from github and place it to /var/www
    cd /var/www
    git clone https://github.com/CESNET/pakiti-server

#### Copy file Config.php to /etc/pakiti
###### you can change the default username and password for the Pakiti database user in Config.php
    mkdir -p /etc/pakiti
    cp /var/www/pakiti-server/install/Config_template.php /etc/pakiti/Config.php

#### Run php initDB.php for initalize database and create user which is defined in Config.php
###### use root password (option -h for help)
    php /var/www/pakiti-server/install/initDB.php -p

#### Copy file pakiti3.apache2.conf to apache2/sites-available
    cp /var/www/pakiti-server/install/pakiti3.apache2_template.conf /etc/apache2/sites-available/pakiti.conf

#### Enable sites in pakiti3.apache2.conf
    a2ensite pakiti.conf

#### Enable apache2 modules
    a2enmod ssl
    a2enmod rewrite

#### Reload apache2
    service apache2 restart
