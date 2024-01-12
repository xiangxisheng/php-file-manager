#!/bin/bash
#cd `dirname $0`

#common
apt update

#php-ext-zip
apt install -y libzip-dev
docker-php-ext-install zip

cd /etc/apache2/mods-enabled
ln -s ../mods-available/rewrite.load .

sh /root/script/reload.sh
