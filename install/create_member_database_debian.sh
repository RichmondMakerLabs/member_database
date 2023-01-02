#!/bin/bash
# sudo setup-lamp-server.sh

sudo mysql_secure_installation << EOF

n
n
y
y
y
y
EOF


sudo mysql << EOF

create user rml identified by 'abc123';
create database members;
grant all privileges on members.* to 'rml';
quit
EOF

cd      #(takes you to your home directory)
git clone https://github.com/RichmondMakerLabs/member_database
cd ~/member_database/install/
## make certain that bin/loadsql has execute permissions
chmod +x ../bin/loadsql
## edit bin/loadsql to contain the mysql password instead of the placeholder \<password\>
## if the mysql user (line 20 above) is not rml, then edit bin/loadsql after the option -u
../bin/loadsql members.sql      #(creates the tables and loads initial data)

sudo mkdir /var/www/auth                #(prepare a secure location for password)
cd ~/member_database/php/
sudo cp *.* /var/www/html       #(copies the php files to DocumentRoot)
sudo mv /var/www/html/rml.inc /var/www/auth/ #(move the password file)
sudo ln -s /var/www/html/rml.php /var/www/html/index.php
sudo rm /var/www/html/index.html
