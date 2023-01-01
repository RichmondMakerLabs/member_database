#!/usr/bin/bash
# run this script as root, or prepend 'sudo' to all commands
apt update
# apt upgrade -y
apt install apache2 -y
apt install mariadb-server -y
apt install php8.1 php8.1-mysql
apt install phpmyadmin -y


a2enmod php8.1		## (may be already enabled)
a2enmod rewrite
systemctl restart apache2

systemctl enable mariadb
systemctl start mariadb

ln -s /usr/share/phpmyadmin /var/www/html/
systemctl restart apache2

