#!/bin/bash

WEBROOT="/var/www/istyle.eu/"
LOGDIR="/var/log/magento"

nfs_check() {
  if [ ! -f /mnt/istyle-storage/istyle/.nfs.check ]; then
    echo -n "NFS file not found, trying to remount .. "
    if mount -a; then echo OK; else echo FAIL; fi
  fi
}

cron_check() {
  echo -n "Checking CRON: "
  while pgrep -x php7.0 > /dev/null; do
    echo -n "."
    sleep 5
  done
  echo " DONE!"
}

cron_check

echo -n "Stop PHP .. "
if /usr/bin/pkill -9 php; then echo OK; else echo FAIL; fi
echo -n "Stop Nginx .. "
if /usr/bin/pkill -9 nginx; then echo OK; else echo FAIL; fi

echo -n "Delete and recreate webroot directories .. "
if [ -d ${WEBROOT} ]; then
    rm -rf ${WEBROOT}
    rm -rf ${LOGDIR}
fi

mkdir -p ${WEBROOT}
mkdir -p ${LOGDIR}/log/oander
chown www-data:www-data -R /var/www/.composer
chown www-data:www-data -R ${LOGDIR}
chown www-data:www-data -R ${WEBROOT}
echo "OK"

