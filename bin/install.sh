#!/bin/bash

# SYSTEM VARIABLES
DEPLOY_ENV="DEVELOPMENT"
WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/istyle-storage/istyle"
EFS_PRELIVE="${EFS}/live"
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)
DEV_MASTER_ID="i-0810073885f247b7c"
DUMP_FILE="/mnt/istyle-storage/istyle/dbdumps/istyle_full_$(date +%F-%H%M%S).sql"

# FUNCTIONS
magento() {
  local MAGENTO_COMMAND="${1}"
  php ${WEBROOT}/bin/magento ${MAGENTO_COMMAND}
}

symlink_check() {
  local NOTES="${1}"
  local PATH_TO="${2}"
  local WHAT_TO_LINK="${3}"
  local WHERE_TO_LINK="${4}"

  echo -n " * ${NOTES} ... "
  if [ -L "${PATH_TO}" ]; then
    rm "${PATH_TO}" &> /dev/null
  else
    rm -rf "${PATH_TO}"
  fi
  if ln -s "${WHAT_TO_LINK}" "${WHERE_TO_LINK}"; then echo OK; else echo FAIL; fi
}


echo
echo "================================================="
echo "   DEPLOY SCRIPT STARTING ON ENV: ${DEPLOY_ENV}  "
echo "================================================="
echo

/etc/init.d/php7.0-fpm restart
/etc/init.d/nginx restart
/etc/init.d/varnish stop

# MAIN INSTANCE CHECK AND WORKFLOW
if [ "${INSTANCE_ID}" == "${DEV_MASTER_ID}" ]; then
  echo
  echo "===== BUILD STAGE ====="
  echo

  symlink_check "CREATE DIRECTORY SYMLINK TO UPLOAD FOLDER" "${WEBROOT}/upload" "${EFS}/upload" "${WEBROOT}/"

  echo -n " * COPY THE ENV FILE ... "
  if cp -a ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo " * MANUALLY CLEANUP GENERATED MAGENTO FOLDERS: "
  echo -n " * /var/di ... "
  if rm -rf ${EFS_PRELIVE}/var/di/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/generation ... "
  if rm -rf ${EFS_PRELIVE}/var/generation/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/view_preprocessed ... "
  if rm -rf ${EFS_PRELIVE}/var/view_preprocessed/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/report ... "
  if rm -rf ${EFS_PRELIVE}/var/report/*; then echo OK; else echo FAIL; fi
  echo -n " * /pub/static ... "
  if rm -rf ${EFS_PRELIVE}/pub/static/*; then echo OK; else echo FAIL; fi

  echo " * CREATE DIRECTORY SYMLINKS TO PRELIVE:"
  symlink_check "var" "${WEBROOT}/var" "${EFS_PRELIVE}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_PRELIVE}/pub/static" "${WEBROOT}/pub/"
  /etc/init.d/php7.0-fpm restart

  echo -n " * REMOVE OLD DATABASE BACKUPS ... "
  find /mnt/istyle-storage/istyle/dbdumps/ -type f -mtime +7 -print0 | xargs -0 rm -f
  [ $? -eq 0 ] && echo OK || echo FAIL

  #echo -n " * BACKUP ALL DEV DATABASES ... "
  #mysqldump --routines --triggers --databases istyle-dev istyle-warehousemanager-dev istyle-apigateway-dev > ${DUMP_FILE}
  #[ $? -eq 0 ] && echo OK || echo FAIL
  #sed -i 's/`istylem2`@`%`/`root`@`%`/g' ${DUMP_FILE}

  echo -n " * DELETE VENDOR DIRECTORY ... "
  if rm -rf ${WEBROOT}/vendor/*; then echo OK; else echo FAIL; fi

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  cd ${WEBROOT} && composer install
  chown www-data:www-data -R ${WEBROOT}

  echo
  echo -n "==== MAGENTO UPGRADE ===="
  magento "setup:upgrade"

  echo
  echo "==== COMPILE STATIC CONTENTS ===="
  echo
  magento "setup:di:compile"
  magento "setup:static-content:deploy hu_HU --theme=Magento/backend --theme=Oander/istyle"
  magento "setup:static-content:deploy en_US --theme=Magento/backend"
  magento "setup:static-content:deploy mk_MK --theme=Oander/istyle"
  magento "setup:static-content:deploy bg_BG --theme=Oander/istyle"
  magento "setup:static-content:deploy hr_HR --theme=Oander/istyle"
  magento "setup:static-content:deploy lv_LV --theme=Oander/istyle"
  magento "setup:static-content:deploy ro_RO --theme=Oander/istyle"
#  magento "setup:static-content:deploy ru_RU --theme=Oander/istyle"
  magento "setup:static-content:deploy sr_Cyrl_RS --theme=Oander/istyle"
  magento "setup:static-content:deploy sl_SI --theme=Oander/istyle"
  magento "setup:static-content:deploy cs_CZ --theme=Oander/istyle"
  magento "setup:static-content:deploy sk_SK --theme=Oander/istyle"
  echo

  symlink_check "CREATE DIRECTORY SYMLINK TO MEDIA FOLDER" "${WEBROOT}/pub/media" "${EFS}/media" "${WEBROOT}/pub/"

  # OWNERSHIP FIXES
  echo -n " * CHOWN EFS PRELIVE DIR ... "
  if chown www-data:www-data -R ${EFS_PRELIVE}; then echo OK; else echo FAIL; fi
  echo -n " * CHOWN WEBROOT DIR ... "
  if chown www-data:www-data -R ${WEBROOT}; then echo OK; else echo FAIL; fi
  echo -n " * CHOWN VAR/LOG DIR ... "
  if chown www-data:www-data -R /var/log/magento; then echo OK; else echo FAIL; fi

  echo -n " * COPY CUSTOM NGINX CONFIG FOR MAGENTO ... "
  if cp -a ${WEBROOT}/nginx.magento.conf ${WEBROOT}/nginx.conf.sample; then echo OK; else echo FAIL; fi

  echo
  echo " * MAGENTO CACHE ENABLE: "
  magento "cache:enable"
  echo
  echo " * MAGENTO CACHE FLUSH: "
  sudo -u www-data php ${WEBROOT}/bin/magento cache:flush
  echo
  echo " * REDIS CACHE FLUSH: "
  redis-cli -h istyle-eu-dev-redis.dzzabg.0001.euc1.cache.amazonaws.com flushall
  echo

fi

/etc/init.d/php7.0-fpm restart
/etc/init.d/nginx restart
/etc/init.d/varnish start

echo
echo "================================================="
echo "              END OF DEPLOY SCRIPT               "
echo "================================================="
echo

exit 0
