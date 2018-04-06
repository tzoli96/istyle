#!/bin/bash
set -eu

/etc/init.d/php7.0-fpm stop
/etc/init.d/nginx stop

WEBROOT="/var/www/istyle.eu/"
LOGDIR="/var/log/magento"

if [ -d ${WEBROOT} ]; then
    rm -rf ${WEBROOT}
    rm -rf ${LOGDIR}
fi

mkdir -p ${WEBROOT}
mkdir -p ${LOGDIR}/log/oander
chown www-data:www-data -R /var/www/.composer
chown www-data:www-data -R ${LOGDIR}
chown www-data:www-data -R ${WEBROOT}

