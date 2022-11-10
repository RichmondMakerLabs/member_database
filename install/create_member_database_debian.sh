#!/usr/bin/bash
# 
apt install mariadb-server
apt install php php-mysql
apt install apache2

systemctl enable mariadb
systemctl start mariadb

mysql_secure_installation

mysql
create user rml identified by '<password>';
grant all privileges on members.* to 'rml';
quit

loadsql members.sql
