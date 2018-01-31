#!/bin/bash
set -eu

WEBROOT="/var/www/istyle.eu/"

if [ -d ${WEBROOT} ]; then
    rm -rf ${WEBROOT}
fi

mkdir -p ${WEBROOT}
rm -rf /var/log/magento/log
mkdir -p /var/log/magento/log
chown www-data:www-data -R /var/log/magento
chown www-data:www-data -R /var/www/.composer
chown www-data:www-data ${WEBROOT}
