#!/bin/bash
set -eu

WEBROOT="/var/www/istyle.eu/"

if [ -d ${WEBROOT} ]; then
    rm -rf ${WEBROOT}
fi

mkdir -p ${WEBROOT}
chown www-data:www-data ${WEBROOT}
