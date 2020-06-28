# Server installation of Pakiti
Pakiti server runs as a standard PHP application in a web server (like Apache) and uses the MySQL/Maria
database engine to store data. Before proceeding with the installation, you need to deploy these services
and configure them properly.

For automated deployment you can use the Ansible role that is shipped with the server. After the server is deployed,
you need to provide initial configuration of the vulnerability sources and test its functions. See the bottom for
more detials.

## Ansible-based deployment
N.B. the provided Ansible recipe only addresses the deployment of a Pakiti server and enables it in Apache
configuration, which has to already be installed. Likewise, the machine is expected to have a MySQL/Maria
database installed and active. The Ansible installation was tested with Debian 9,10 and CentOS 8.

In order to install Pakiti using the provided role, the following steps can be performed:

###### Install Ansible to the machine that will initiate the deployment
###### Get the Ansible recipe, e.g. using:

    wget https://github.com/CESNET/pakiti-server/archive/master.zip
    unzip master.zip
    cd pakiti-server/install/ansible

###### Edit ansible-conf.yml to add database credentials etc.
###### Initite the deployment
    
    ansible-playbook playbook.yml
    
After the configuration has finished you are advised to adapt it to your needs and probably limit the access to the protected part.
See the bottom for more information on how to use the service.

## Manual installation
You can follow the steps from the Ansible recipe (pakiti-server/install/ansible/roles/pakiti-server/tasks/main.yml), they're
self-explaining. A more detailed description is below (based on Debian).

### Install dependencies
Pakiti requires PHP at least v.5.5

    apt-get install php php-mysql php-curl php-dom
    apt-get install curl

### Download repository from github and place it to /var/www
    cd /var/www
    git clone https://github.com/CESNET/pakiti-server

### Provide configuration settings
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

### Run php initDB.php for initalize database and create user which is defined in Config.php
###### use root password (option -h for help)
    php pakiti-server/install/initDB.php -p

### Extend the web server configuration to enable Pakiti
Pakiti is available via several entry points:
- Public entry page (src/modules/gui/www/public/) with no client authentication, meant as the entry point for users
- Pakiti GUI (/var/www/pakiti-server/src/modules/gui/www/) with controlled access
- The reporting endpoint (src/modules/api/) used by clients to sends reports
- The API endpoint (src/modules/api/) to enable other services get data from Pakiti

The provided template and following steps can be used for Apache web servers:

    cp (&edit) pakiti-server/install/ansible/roles/pakiti-server/templates/etc/apache2/pakiti.conf.j2 /etc/apache2/conf-available/pakiti.conf 
    a2enconf pakiti
    service apache2 reload

Please note that the template contains a very basic configuration, you need to adapt it to your needs and probably limit the access to the protected part.

### Regular update of information on vulnerabilities
You need to configure a cron job to update Pakiti with information about new vulnerabilities as published by vendors
Linux distibutions.
    
    30 4 * * * root php /var/www/pakiti-server/src/modules/cli/vds.php -c synchronize && php /var/www/pakiti-server/src/modules/cli/calculateVulnerabilities.php

## Getting started with Pakiti

In order to ease initial configuration of the Pakiti server, you can use the provided script:
    
    cd pakiti-server/src/modules/cli
    php server-boostrap.php
The script populates vulnerability information from main Linux distributions to get you started with the service. More details on
the configuration can be found in [Configuration](configuration.md).

In order to test the server you can use the pakiti client (https://github.com/CESNET/pakiti-client)
    
    pakiti-client --url https://example.org/pakiti/feed/

The reports sent by the client should be immediately visible in the Pakiti GUI at https://example.org/pakiti/protected/


