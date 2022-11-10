#!/bin/bash

WHEN=`date -I`
FILE=membersdbdump${WHEN}.sql
SERVER=localhost
DB=members
NAME=rml
PASS=<password>
/usr/bin/mariadb-dump -h $SERVER -u $NAME --password=$PASS $DB > $FILE

