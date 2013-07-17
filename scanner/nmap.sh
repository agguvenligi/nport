#!/bin/bash
/usr/local/bin/nmap -n -Pn -sT -T3 -p 21,22,23,25,80,443,445,1433,1521,3306,3389,8080 -iL /root/ip_block -oX /var/nport/nmapoutput/ip_block_nmap.xml
php -e /var/www/nport/utility/nmapprocess.php
