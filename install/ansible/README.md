# Pakiti Server Ansible Provisioning

## Description

This is [Ansible](https://www.ansible.com/) script for automatic deployment of Pakiti server.

## Prerequisites

This script does not install Pakiti prerequisites, therefore you need to install following prerequisities manually:

* MySQL Database Server
* Apache2 Web Server
* PHP (at least 5) + PHP MySQL module
* curl + PHP curl module
* Subversion

Furthermore you need to have Ansible installed. It is recommended to have latest Ansible version installed which may not be available from your distro's repositories. To find out how to install latest Ansible version on your system please review [official documentation page](https://docs.ansible.com/ansible/latest/intro_installation.html).

Also for Ansible installation you need to have following extra packages present on system:

* MySQL-python module
** Can be installed for example by `apt-get install python-dev libmysqlclient-dev python-pip` and then `pip install MySQL-python`.

## Installation using Ansible

You first need to create an ansible inventory file, like `inventory.cfg`,

Next you have to change the configuration of the installation. To do this, please modify values in `ansible-conf.yml` file present in this folder. The variables you will want to change and their meaning is following:

* `pakiti_mysql_user` - This is username (login name) of pakiti user on MySQL server (which will be created during the installation).
* `pakiti_mysql_pass` - This is password of pakiti user on MySQL server to be created.
* `pakiti_mysql_db` - This is name of database to be created on MySQL server for pakiti needs.
* `mysql_root_name` - This is username (login name) of user which is priviledged to create new users and databases (usually this will be root so no need to change this).
* `mysql_root_password` - This is password of user specified in mysql_root_name variable.
* `pakiti_domain_name` - This is FQDN of host where you are installing pakiti. This will be used for apache server configuration.
* `pakiti_admin_mail` - This is email of pakiti server administrator which will be pasted into apache configuration file.

Finally when you have changed the defaults in config file, you may launch the installation by executing command

```shell
ansible-playbook -i inventory.cfg playbook.yml
```

## Post installation steps

Please continue with configuring Pakiti server according to our [Server Configuration](https://github.com/CESNET/pakiti-server/blob/master/docs/configuration.md) documentation page.
