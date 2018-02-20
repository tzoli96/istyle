#!/bin/bash
set -u

### DEPLOYMENT ENVIRONMENT ###
DEPLOY_ENV="PRODUCTION"

# SYSTEM VARIABLES
WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/istyle-storage/istyle"
EFS_BLUE="${EFS}/blue"
EFS_GREEN="${EFS}/green"
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)

# DEPLOYMENT VARIABLES #
LANGUAGES=('MK')

# DON'T CHANGE THESE UNLESS YOU KNOW WHAT YOU ARE DOING !!!
if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  MASTER_ID="i-0a57263aca752890a"
  DATABASES=('istyle' 'istyle_upg' 'istyle-warehousemanager' 'istylewh_upg' 'istyle-apigateway' 'istyleapi_upg')
elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
  MASTER_ID="i-07102058010aa5695"
  DATABASES=('istyle-stg' 'istyle-stg-upg' 'istyle-warehousemanager-stg' 'istyle-warehousemanager-stg-upg' 'istyle-apigateway-stg' 'istyle-apigateway-stg-upg')
else
  # DEVELOPMENT #
  MASTER_ID=""
  DATABASES=()
fi

# FUNCTIONS
magento() {
  local MAGENTO_COMMAND="${1}"
  php ${WEBROOT}/bin/magento ${MAGENTO_COMMAND}
#  su - www-data -c "php ${WEBROOT}/bin/magento ${MAGENTO_COMMAND}"
}

dbdump() {
  local ORIGINAL_DB="${1}"
  local UPGRADE_DB="${2}"
  echo -n " ${ORIGINAL_DB} ... "
  mysql -e "DROP DATABASE \`${UPGRADE_DB}\`; CREATE DATABASE \`${UPGRADE_DB}\`;"
  if mysqldump --single-transaction ${ORIGINAL_DB} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${UPGRADE_DB}; then echo OK; else echo FAIL; fi
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

# MAIN INSTANCE CHECK AND WORKFLOW
if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]; then
  echo
  echo "===== MASTER WORKFLOW ====="
  echo
  echo -n " * COPY THE ENV FILE WITH THE DATABASES FOR UPGRADE ... "
  if cp ${EFS}/env/upgrade_env.php ${WEBROOT}/app/etc/env.php; then echo OK; else echo FAIL; fi

  echo -n " * RSYNC BLUE FOLDER TO GREEN WITH EXCEPTIONS ... "
  if rsync -au --exclude={"/var/backups/*","/var/log/*","/var/generation/*","/var/di/*","/pub/static/*"} ${EFS_BLUE}/* ${EFS_GREEN}/; then echo OK; else echo FAIL; fi

  echo " * CREATE DIRECTORY SYMLINKS TO GREEN:"
  symlink_check "var" "${WEBROOT}/var" "${EFS_GREEN}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_GREEN}/pub/static" "${WEBROOT}/pub/"
  # MEG KELL NEZNI ERRE SZUKSEG VAN-E
  #symlink_check "var/log" "${WEBROOT}/var/log" "/var/log/magento/log" "${WEBROOT}/var/"

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  # MEG KELL NEZNI HOGY WWW-DATA USER NEVEBEN EZ MEGY-E...
  cd ${WEBROOT} && composer install
#  su - www-data -c "cd ${WEBROOT} && composer install"

  echo
  echo -n "=== CHECK IF DB UPGRADE NEEDED => "
  if magento "setup:db:status" | grep -q "up to date"; then
    echo "MAGENTO UPGRADE WITHOUT DB CHANGE ==="
    echo
    magento "setup:upgrade"
  else
    echo "UPGRADE WITH DB CHANGE ==="
    echo
    echo "=== COPY DATABASES ==="
    echo
    echo -n " Main DB: ${DATABASES[0]} ... "
    mysql -e "DROP DATABASE \`${DATABASES[1]}\`; CREATE DATABASE \`${DATABASES[1]}\`;"
    mysqldump --skip-add-drop-table --no-data ${DATABASES[0]} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}
    if mysqldump --single-transaction ${DATABASES[0]} weee_tax theme product_alert_price eav_entity_type core_config_data setup_module store store_group store_website | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}; then echo OK; else echo FAIL; fi
    dbdump ${DATABASES[2]} ${DATABASES[3]}
    dbdump ${DATABASES[4]} ${DATABASES[5]}
    echo
    echo "==== MAGENTO UPGRADE ===="
    echo
    magento "setup:upgrade"
  fi

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
  magento "setup:static-content:deploy sr_RS --theme=Oander/istyle"
  magento "setup:static-content:deploy sl_SI --theme=Oander/istyle"
  magento "setup:static-content:deploy cs_CZ --theme=Oander/istyle"
  magento "setup:static-content:deploy sk_SK --theme=Oander/istyle"
  magento "cache:enable"
  echo
  echo -n " * CREATE FLAG FOR BLUE/GREEN DEPLOYMENT ... "
  if touch ${EFS}/deployed.flag; then echo OK; else echo FAIL; fi

  echo -n " * CHOWN EFS_GREEN DIR ... "
  if chown www-data:www-data -R ${EFS_GREEN}; then echo OK; else echo FAIL; fi

  echo -n " * COPY THE ENV FILE WITH THE DATABASES TO PRODUCTION FOR CRONJOBS ... "
  if cp ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo " * SET BACK SYMLINKS TO BLUE ... "
  symlink_check "var" "${WEBROOT}/var" "${EFS_BLUE}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_BLUE}/pub/static" "${WEBROOT}/pub/"
else
  echo
  echo "===== WORKER INSTANCES ====="
  echo
  echo -n " * CONFIGURE THE ENV FILE WITH THE PRODUCTION DATABASES FOR UPGRADE ... "
  if cp ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo -n " * DELETE VENDOR DIRECTORY ... "
  if rm -rf ${WEBROOT}/vendor/*; then echo OK; else echo FAIL; fi

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  # MEG KELL NEZNI HOGY WWW-DATA USER NEVEBEN EZ MEGY-E...
  cd ${WEBROOT} && composer install
#  su - www-data -c "cd ${WEBROOT} && composer install"

  echo
  echo -n "=== CHECK IF DEPLOYED FLAG EXISTS => "
  if [ -f ${EFS}/deployed.flag ]; then
    echo "YES ==="
    echo
    echo -n " * RSYNC EFS GREEN TO BLUE ... "
    if rsync -au --exclude={"/var/backups/*","/var/log/*"} ${EFS_GREEN}/* ${EFS_BLUE}/; then echo OK; else echo FAIL; fi

    echo " * CREATE DIRECTORY SYMLINKS TO BLUE ... "
    symlink_check "var" "${WEBROOT}/var" "${EFS_BLUE}/var" "${WEBROOT}/"
    symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_BLUE}/pub/static" "${WEBROOT}/pub/"

    echo
    echo "==== MAGENTO UPGRADE :: KEEP-GENERATED ===="
    echo
    cp ${WEBROOT}/pub/errors/default/maintenance.phtml ${WEBROOT}/pub/errors/default/503.phtml
    if magento "maintenance:enable" && sleep 10; then
      magento "setup:upgrade --keep-generated"
      magento "maintenance:disable" && sleep 15
      magento "cache:enable"
      magento "cache:flush"
    fi

    echo -n " * REMOVING DEPLOYED FLAG ... "
    if rm ${EFS}/deployed.flag; then echo OK; else echo FAIL; fi
  else
    echo "NO ==="
    echo
    echo " * CREATE DIRECTORY SYMLINKS TO BLUE ... "
    symlink_check "var" "${WEBROOT}/var" "${EFS_BLUE}/var" "${WEBROOT}/"
    symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_BLUE}/pub/static" "${WEBROOT}/pub/"
    echo
    magento "cache:enable"
  fi
fi

# MAIN SYMLINK SETUP
symlink_check "CREATE DIRECTORY SYMLINK TO MEDIA FOLDER" "${WEBROOT}/pub/media" "${EFS}/media" "${WEBROOT}/pub/"
symlink_check "CREATE DIRECTORY SYMLINK TO UPLOAD FOLDER" "${WEBROOT}/upload" "${EFS}/upload" "${WEBROOT}/"
# ONLY NEEDED FOR PRODUCTION ENV IF URL IS USING istyle.eu/xx
if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  for LANG_SYMLINK in "${LANGUAGES[@]}"; do
    symlink_check "CREATE DIRECTORY SYMLINK TO ${LANG_SYMLINK} FOLDER" "${WEBROOT}/pub/${LANG_SYMLINK,,}" "${WEBROOT}/pub" "${WEBROOT}/pub/${LANG_SYMLINK,,}"
  done
fi

chown www-data:www-data -R /var/log/magento
chown www-data:www-data -R /var/www/istyle.eu/

# EZ MAJD NEM FOG KELLENI HA MAR NEM LESZ SIGMANET PROXY..
if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  cp ${WEBROOT}/nginx.magento.conf ${WEBROOT}/nginx.conf.sample
fi

/etc/init.d/php7.0-fpm restart
/etc/init.d/nginx restart

echo
echo "================================================="
echo "              END OF DEPLOY SCRIPT               "
echo "================================================="
echo

exit 0

