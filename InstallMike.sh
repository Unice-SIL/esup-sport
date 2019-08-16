#!/bin/bash

# une fois l'install faite, se connecter avec PuTTY

# connaitre sa version 32 ou 64 bit
uname -m

# MAJ du system
yum update

yum install nano

# Installer Apache - php - mysql
yum install php httpd 

### Install MySQL
yum install wget
wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
rpm -ivh mysql-community-release-el7-5.noarch.rpm
yum update
yum install mysql-server
yum install mysql

### Instal php 7.2
#Aide : https://www.tecmint.com/install-php-7-in-centos-7/
wget -q http://rpms.remirepo.net/enterprise/remi-release-7.rpm
wget -q https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
yum install yum-utilsy
yum-config-manager --enable remi-php72

yum install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo 
yum install php-xml php-mbstring
yum install php-pecl-zip
yum install php-imap



# Activer les services
####chkconfig --level 35 mysqld on
systemctl start mysqld
chkconfig --level 35 httpd on

# Installer phpMyAdmin
rpm -Uvh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum install phpMyAdmin
### => error a l'install

Configurer Apache
# chercher ServerName et y mettre ceci : ServerName 192.168.51.57 + Allow la réécriture d'URL
nano -K /etc/httpd/conf/httpd.conf



# Configurer l'acces à phpmyadmin - pour Directory /usr/share/phpMyAdmin/ : Allow from All
###nano -K /etc/httpd/conf.d/phpMyAdmin.conf

# definir un mot de passe pour l'utilisateur root dans mysql
mysql -u root
UPDATE mysql.user SET Password = PASSWOmysqkRD('password') WHERE User = 'root';
FLUSH PRIVILEGES;

# configurer le firewall puis rendre la nouvelle règle persistante
iptables -I INPUT 2 -p tcp -m tcp --dport 80 -j ACCEPT
/sbin/service iptables save 
### => error

# redémarrer Apache
service mysqld restart
service httpd restart

# aller dans le repertoire www
cd /var/www/html

# Copiez les fichiers du site dans ce répertoire
# Importez les scripts SQL en prenant soin au préalable de remplacer l'ancienne ip par le nom de domaine (ou ip) su site dans tout le script.

#Autoriser l'envoi d'email
setsebool httpd_can_sendmail 1

setsebool httpd_can_network_connect 1
setsebool httpd_can_network_connect_db 1

semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/*"
setenforce 0

#### 777 pour files


service httpd restart
