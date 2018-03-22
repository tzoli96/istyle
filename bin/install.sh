#!/bin/bash
set -u

export PATH=/root/.local/bin:$PATH

# ENVIRONMENT VARIABLE
SG_GROUPS=$(curl -s http://169.254.169.254/latest/meta-data/security-groups)
if [[ ${SG_GROUPS} =~ "prod" ]];then
  DEPLOY_ENV="PRODUCTION"
elif [[ ${SG_GROUPS} =~ "stg" ]];then
  DEPLOY_ENV="STAGING"
elif [[ ${SG_GROUPS} =~ "dev" ]];then
  DEPLOY_ENV="DEVELOPMENT"
else
  echo "DEPLOY_ENV VARIABLE NOT SET, CHECK THE EC2 SECURITY GROUPS!"
fi

# SYSTEM VARIABLES
WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/istyle-storage/istyle"
EFS_BUILD="${EFS}/build"
EFS_PRELIVE="${EFS}/live_$(date +%Y%m%d%H%M)"
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)
CODEDEPLOY_BUILD_APP_NAME="istyle-eu-master"

# DEPLOYMENT VARIABLES
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
php_restart() {
   /etc/init.d/php7.0-fpm restart
}

magento() {
  local MAGENTO_COMMAND="${1}"
  php ${WEBROOT}/bin/magento ${MAGENTO_COMMAND}
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
  echo "===== BUILD STAGE ====="
  echo

  LATEST_CODEDEPLOY_BUILD_ID=$(aws deploy list-deployments --application-name ${CODEDEPLOY_BUILD_APP_NAME} --deployment-group-name ${DEPLOY_ENV,,} --query "deployments" --output text | awk '{print $2}' | head -1)
  LATEST_CODEDEPLOY_BUILD_STATUS=$(aws deploy get-deployment --deployment-id ${LATEST_CODEDEPLOY_BUILD_ID} --query "deploymentInfo.[status]" --output text)
  if [[ "${LATEST_CODEDEPLOY_BUILD_STATUS}" =~ ^(Succeeded|Failed|Stopped)$ ]]; then
    echo " * LATEST CODEDEPLOY BUILD ID: ${LATEST_CODEDEPLOY_BUILD_ID}"
    echo " * LATEST CODEDEPLOY BUILD STATUS: ${LATEST_CODEDEPLOY_BUILD_STATUS}"
    if [[ "${LATEST_CODEDEPLOY_BUILD_STATUS}" == "Succeeded" ]]; then
      echo -n " * CLEAN UP PREVIOUS LIVE FOLDER ... "
      EFS_PREVIOUS_LIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | head -1)"
      if rm -rf $EFS_PREVIOUS_LIVE/; then echo OK; else echo FAIL; fi
    fi
  else
    echo "SOMETHING IS WRONG WITH THE AWS COMMAND .. exiting"
    exit 2
  fi

  echo -n " * COPY THE ENV FILE WITH THE DATABASES FOR UPGRADE ... "
  if cp -a ${EFS}/env/upgrade_env.php ${WEBROOT}/app/etc/env.php; then echo OK; else echo FAIL; fi

  echo " * MANUALLY CLEANUP GENERATED MAGENTO FOLDERS: "
  echo -n " * /var/di ... "
  if rm -rf ${EFS_BUILD}/var/di/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/generation ... "
  if rm -rf ${EFS_BUILD}/var/generation/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/view_preprocessed ... "
  if rm -rf ${EFS_BUILD}/var/view_preprocessed/*; then echo OK; else echo FAIL; fi
  echo -n " * /var/report ... "
  if rm -rf ${EFS_BUILD}/var/report/*; then echo OK; else echo FAIL; fi
  echo -n " * /pub/static ... "
  if rm -rf ${EFS_BUILD}/pub/static/*; then echo OK; else echo FAIL; fi

  echo -n " * RSYNC LIVE FOLDER TO BUILD WITH EXCEPTIONS ... "
  EFS_LIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | head -1)"
  if rsync -au --delete --exclude={"/var/backups/*","/var/log","/var/report/*","/var/di/*","/var/generation/*","/var/view_preprocessed/*","/pub/static/*"} ${EFS_LIVE}/ ${EFS_BUILD}/; then echo OK; else echo FAIL; fi

  echo " * CREATE DIRECTORY SYMLINKS TO BUILD:"
  symlink_check "var" "${WEBROOT}/var" "${EFS_BUILD}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_BUILD}/pub/static" "${WEBROOT}/pub/"
  php_restart

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  cd ${WEBROOT} && composer install
  chown www-data:www-data -R ${WEBROOT}

  echo " * DISABLE MAGENTO CACHE:"
  magento "cache:disable"

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
    # EZEN LEHET GYORSITANI HA CSAK A MINIMALIS MUKODESHEZ SZUKSEGES ADATOK KERULNEK CSAK AT..
#    echo -n " Main DB: ${DATABASES[0]} ... "
#    mysql -e "DROP DATABASE \`${DATABASES[1]}\`; CREATE DATABASE \`${DATABASES[1]}\`;"
#    mysqldump --skip-add-drop-table --no-data ${DATABASES[0]} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}
#    if mysqldump --single-transaction ${DATABASES[0]} weee_tax theme product_alert_price eav_entity_type core_config_data setup_module store store_group store_website | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}; then echo OK; else echo FAIL; fi
    dbdump ${DATABASES[0]} ${DATABASES[1]}
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
  magento "setup:static-content:deploy ru_RU --theme=Oander/istyle"
  magento "setup:static-content:deploy sr_Cyrl_RS --theme=Oander/istyle"
  magento "setup:static-content:deploy sl_SI --theme=Oander/istyle"
  magento "setup:static-content:deploy cs_CZ --theme=Oander/istyle"
  magento "setup:static-content:deploy sk_SK --theme=Oander/istyle"
  echo

  symlink_check "CREATE DIRECTORY SYMLINK TO MEDIA FOLDER" "${WEBROOT}/pub/media" "${EFS}/media" "${WEBROOT}/pub/"
  symlink_check "CREATE DIRECTORY SYMLINK TO UPLOAD FOLDER" "${WEBROOT}/upload" "${EFS}/upload" "${WEBROOT}/"

  echo -n " * CHOWN EFS BUILD DIR ... "
  if chown www-data:www-data -R ${EFS_BUILD}; then echo OK; else echo FAIL; fi
  echo -n " * CHOWN WEBROOT DIR ... "
  if chown www-data:www-data -R ${WEBROOT}; then echo OK; else echo FAIL; fi
  echo -n " * CHOWN VAR/LOG DIR ... "
  if chown www-data:www-data -R /var/log/magento; then echo OK; else echo FAIL; fi

  # VALIDATION
  echo
  if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
    echo -n " * COPY CUSTOM NGINX CONFIG FOR MAGENTO ... "
    if cp -a ${WEBROOT}/nginx.magento.conf ${WEBROOT}/nginx.conf.sample; then echo OK; else echo FAIL; fi
    for LANG_SYMLINK in "${LANGUAGES[@]}"; do
      symlink_check "CREATE DIRECTORY SYMLINK TO ${LANG_SYMLINK} FOLDER" "${WEBROOT}/pub/${LANG_SYMLINK,,}" "${WEBROOT}/pub" "${WEBROOT}/pub/${LANG_SYMLINK,,}"
    done
    php_restart
    /etc/init.d/nginx reload
    echo -n " * SERVICE VALIDATION ... "
    if curl -I -H 'Host: istyle.eu' -H 'X-Forwarded-Proto: https' http://localhost/mk/ 2>&1 /dev/null | grep -q "HTTP/1.1 200 OK"; then
      echo OK
    else
      echo FAIL
      exit 3
    fi
  elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
    php_restart
    /etc/init.d/nginx reload
    echo -n " * SERVICE VALIDATION ... "
    if curl -I -H 'Host: staging.istyle.mk' -H 'X-Forwarded-Proto: https' http://localhost/ 2>&1 /dev/null | grep -q "HTTP/1.1 200 OK"; then
      echo OK
    else
      echo FAIL
      exit 3
    fi
  fi

  echo -n " * RSYNC EFS BUILD FOLDER TO PRELIVE: ${EFS_PRELIVE} ... "
  if rsync -au --delete --exclude={"/var/backups/*","/var/log/*","/var/session"} ${EFS_BUILD}/ ${EFS_PRELIVE}/; then echo OK; else echo FAIL; fi

  # FOR CRONJOBS
  echo -n " * COPY THE ENV FILE WITH THE DATABASES BACK FOR CRONJOBS ... "
  if cp -a ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo " * SET SYMLINKS TO PRELIVE FOR CRONJOBS ... "
  symlink_check "var" "${WEBROOT}/var" "${EFS_PRELIVE}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_PRELIVE}/pub/static" "${WEBROOT}/pub/"

  echo -n " * CREATE FLAG FOR BLUE/GREEN DEPLOYMENT FIRST INSTANCE CHECK ... "
  if [ -f ${EFS}/deployed.flag ]; then
    echo OK
  else
    touch ${EFS}/deployed.flag
    echo OK
  fi
else
  echo
  echo "===== BLUE / GREEN DEPLOYMENT ====="
  echo
  echo -n " * CONFIGURE THE ENV FILE WITH THE ${DEPLOY_ENV} DATABASES ... "
  if cp -a ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo -n " * DELETE VENDOR DIRECTORY ... "
  if rm -rf ${WEBROOT}/vendor/*; then echo OK; else echo FAIL; fi

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  cd ${WEBROOT} && composer install
  chown www-data:www-data -R ${WEBROOT}

  EFS_PRELIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | tail -1)"
  echo " * CREATE DIRECTORY SYMLINKS TO PRELIVE: ${EFS_PRELIVE} ... "
  symlink_check "var" "${WEBROOT}/var" "${EFS_PRELIVE}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_PRELIVE}/pub/static" "${WEBROOT}/pub/"
  php_restart

  echo
  echo -n "=== CHECK IF DEPLOYED FLAG EXISTS => "
  sleep $[ ( $RANDOM % 10 ) + 1 ].$[ ( $RANDOM % 1000 ) + 1 ]
  if [ -f ${EFS}/deployed.flag ]; then
    echo "YES ==="
    echo -n " * REMOVING DEPLOYED FLAG ... "
    if rm ${EFS}/deployed.flag; then echo OK; else echo FAIL; fi

    echo
    echo "==== MAGENTO UPGRADE :: KEEP-GENERATED ===="
    echo

    echo -n " * INIT MAINTENANCE MODE MANUALLY ... "
    EFS_LIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | head -1)"
    if touch ${EFS_LIVE}/var/.maintenance.flag && sleep 5; then
      echo OK
      magento "setup:upgrade --keep-generated"
      magento "cache:enable"
    else
      echo FAIL
    fi
  else
    echo "NO ==="
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

# CUSTOM MAINTENANCE PAGE
echo -n " * SETTING UP CUSTOM MAINTENANCE PAGE ... "
if cp -a ${WEBROOT}/pub/errors/default/maintenance.phtml ${WEBROOT}/pub/errors/default/503.phtml; then echo OK; else echo FAIL; fi

# EZ MAJD NEM FOG KELLENI HA MAR NEM LESZ SIGMANET PROXY..
if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  echo -n " * COPY CUSTOM NGINX CONFIG FOR MAGENTO ... "
  if cp -a ${WEBROOT}/nginx.magento.conf ${WEBROOT}/nginx.conf.sample; then echo OK; else echo FAIL; fi
fi

# OWNERSHIP FIXES
chown www-data:www-data -R /var/log/magento
chown www-data:www-data -R ${WEBROOT}

php_restart
/etc/init.d/nginx restart

echo
echo "================================================="
echo "              END OF DEPLOY SCRIPT               "
echo "================================================="
echo

exit 0

