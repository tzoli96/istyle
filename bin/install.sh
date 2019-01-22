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
LOGDIR="/var/log/magento"
EFS="/mnt/istyle-storage/istyle"
EFS_BUILD="${EFS}/build"
EFS_PRELIVE="${EFS}/live_$(date +%Y%m%d%H%M)"
CURRENT_LIVE=$(<${EFS}/current_live)
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)
SLACK_WEBHOOK="https://hooks.slack.com/services/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"

# DEPLOYMENT VARIABLES
LANGUAGES=('MK')

# DON'T CHANGE THESE UNLESS YOU KNOW WHAT YOU ARE DOING !!!
if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  MASTER_ID="i-0a57263aca752890a"
  DATABASES=('istyle' 'istyle_upg' 'istyle-warehousemanager' 'istylewh_upg' 'istyle-apigateway' 'istyleapi_upg')
  WAF_IDS=('12513a30-1fbf-44e3-ba52-5974a6db6f46' '2309ba7b-c63a-4565-a3e1-5af541eb8694' '6e81ab64-f9e6-4bbf-963f-eea8d912357c' 'a7896f03-ca34-4385-b48b-bf7dff193147' 'ba4a71a8-1364-45a3-ba0a-65e91c8c5927' 'bad20f7d-08da-435b-9c4b-47aadbfdad01' 'ce148ce2-a8d1-4389-8850-93156757fc6c' 'd638c88e-9bc5-45c9-81a4-4519c762686c' 'da1d4a90-4b05-405a-8fcc-d14f8a1131ed' 'fa50b7da-fcae-4b37-84b2-802d011b0d45')
elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
  MASTER_ID="i-07102058010aa5695"
  DATABASES=('istyle-stg' 'istyle-stg-upg' 'istyle-warehousemanager-stg' 'istyle-warehousemanager-stg-upg' 'istyle-apigateway-stg' 'istyle-apigateway-stg-upg')
  WAF_IDS=('f3254e59-5c96-47dc-9255-9f8429932044' '339503d9-c260-4ff7-9459-1e2aebcfc1f0')
else
  # DEVELOPMENT #
  MASTER_ID=""
  DATABASES=()
fi

# FUNCTIONS
send_to_slack() {
  local MESSAGE="${1}"
  echo "$MESSAGE"
  PAYLOAD="payload={
    \"channel\": \"#istyle-collab\",
    \"username\": \"$(hostname)\",
    \"text\": \"$MESSAGE\"
    }"
  curl -X POST --data-urlencode "${PAYLOAD}" ${SLACK_WEBHOOK} &> /dev/null
}

restart_services() {
  echo -n " * STOP PHP .. "
  if pgrep php &> /dev/null; then
    if pkill -9 php; then echo OK; else echo FAIL; fi
  else
    echo "DONE"
  fi

  echo -n " * STOP NGINX .. "
  if pgrep nginx &> /dev/null; then
    if pkill -9 nginx; then echo OK; else echo FAIL; fi
  else
    echo "DONE"
  fi

  if ! /etc/init.d/php7.0-fpm restart &> /dev/null; then
     send_to_slack "SOMETHING IS WRONG WITH THE PHP PROCESS, PLEASE CHECK!"
     exit 102
  fi

  if ! /etc/init.d/nginx restart &> /dev/null; then
     send_to_slack "SOMETHING IS WRONG WITH THE NGINX PROCESS, PLEASE CHECK!"
     exit 103
  fi
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
  if mysqldump --single-transaction ${ORIGINAL_DB} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${UPGRADE_DB}; then
    echo OK
  else
    echo "FAILED, dumping again after 60sec.."
    sleep 60
    echo -n " ${ORIGINAL_DB} ... "
    mysql -e "DROP DATABASE \`${UPGRADE_DB}\`; CREATE DATABASE \`${UPGRADE_DB}\`;"
    if mysqldump --single-transaction ${ORIGINAL_DB} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${UPGRADE_DB}; then
      echo OK
    else
      echo "FAILED, dumping again after 120sec.."
      sleep 120
      echo -n " ${ORIGINAL_DB} ... "
      mysql -e "DROP DATABASE \`${UPGRADE_DB}\`; CREATE DATABASE \`${UPGRADE_DB}\`;"
      if mysqldump --single-transaction ${ORIGINAL_DB} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${UPGRADE_DB}; then
        echo OK
      else
        echo "FAILED .. exiting"
        exit 3
      fi
    fi
  fi
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

maintenance_action() {
  local ACTION="${1^^}"
  if [ "${ACTION}" == "BLOCK" ]; then MODE_ACTION="ENABLE"; else MODE_ACTION="DISABLE"; fi

  send_to_slack " * ${MODE_ACTION} MAINTENANCE MODE IN CDN WAF"
  for WAF_ID in "${WAF_IDS[@]}"; do
    WAF_TOKEN=$(aws waf get-change-token --output text)
    if [[ -n ${WAF_TOKEN// } ]]; then
      if aws waf update-web-acl --web-acl-id ${WAF_ID} --change-token ${WAF_TOKEN} --default-action Type="$ACTION" &> /dev/null; then
        echo "   - ${WAF_ID} .. OK"
      else
        send_to_slack "   - ${WAF_ID} .. FAILED"
      fi
    sleep 2
    else
      send_to_slack "SOMETHING IS WRONG WITH THE WAF TOKENS, PLEASE CHECK."
      return 123
    fi
  done
}


echo
echo "================================================="
echo "   DEPLOY SCRIPT STARTING ON ENV: ${DEPLOY_ENV}  "
echo "================================================="
echo

send_to_slack " * DEPLOY STARTED :: https://eu-central-1.console.aws.amazon.com/cloudwatch/home?region=eu-central-1#logEventViewer:group=CodeDeploy_LogGroup;stream=${INSTANCE_ID}"

restart_services

symlink_check "CREATE DIRECTORY SYMLINK TO UPLOAD FOLDER" "${WEBROOT}/upload" "${EFS}/upload" "${WEBROOT}/"

# MAIN INSTANCE CHECK AND WORKFLOW
if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]; then
  echo "===== BUILD STAGE ====="
  echo

  echo -n " * REMOVE PREVIOUS LIVE NFS FOLDERS .. "
  find ${EFS} -maxdepth 1 -iname "live_*" -not -iname ${CURRENT_LIVE} -exec rm -rf {} \;
  if [ $? -eq 0 ]; then
    echo OK
  else
    send_to_slack "SOMETHING IS WRONG WITH DELETING THE PREVIOUS NFS FOLDERS, PLEASE CHECK!"
    exit 101
  fi

  LIVE_DIR_COUNT=$(ls -1 ${EFS}/ | grep -c live_)
  if [[ "${LIVE_DIR_COUNT}" != "1" ]]; then
    send_to_slack "SOMETHING IS WRONG WITH THE NFS LIVE FOLDERS, PLEASE CHECK!"
    exit 102
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
  if rsync -au --delete --exclude={"/var/backups/*","/var/log","/var/report/*","/var/di/*","/var/generation/*","/var/view_preprocessed/*","/pub/static/*","/var/.maintenance.flag"} ${EFS_LIVE}/ ${EFS_BUILD}/; then echo OK; else echo FAIL; fi

  echo " * CREATE DIRECTORY SYMLINKS TO BUILD:"
  symlink_check "var" "${WEBROOT}/var" "${EFS_BUILD}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_BUILD}/pub/static" "${WEBROOT}/pub/"

  echo
  echo "==== COMPOSER INSTALL ===="
  echo
  cd ${WEBROOT} && composer install
  chown www-data:www-data -R ${WEBROOT}

  echo " * DISABLE MAGENTO CACHE:"
  magento "cache:disable"

#  echo
#  echo -n "=== CHECK IF DB UPGRADE NEEDED => "
#  if magento "setup:db:status" | grep -q "up to date"; then
#    echo "MAGENTO UPGRADE WITHOUT DB CHANGE ==="
#    echo
#    magento "setup:upgrade"
#  else
#    echo "UPGRADE WITH DB CHANGE ==="
#    echo
#    echo "=== COPY DATABASES ==="
#    echo
#    # EZEN LEHET GYORSITANI HA CSAK A MINIMALIS MUKODESHEZ SZUKSEGES ADATOK KERULNEK CSAK AT..
#    echo -n " Main DB: ${DATABASES[0]} ... "
#    mysql -e "DROP DATABASE \`${DATABASES[1]}\`; CREATE DATABASE \`${DATABASES[1]}\`;"
#    mysqldump --skip-add-drop-table --no-data ${DATABASES[0]} | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}
#    if mysqldump --single-transaction ${DATABASES[0]} weee_tax theme product_alert_price eav_entity_type core_config_data setup_module store store_group store_website | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql ${DATABASES[1]}; then echo OK; else echo FAIL; fi
#    dbdump ${DATABASES[0]} ${DATABASES[1]}
#    dbdump ${DATABASES[2]} ${DATABASES[3]}
#    dbdump ${DATABASES[4]} ${DATABASES[5]}
    echo
    echo "==== MAGENTO UPGRADE ===="
    echo
    magento "setup:upgrade"
#  fi

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
    /etc/init.d/nginx reload
    if curl -I -H 'Host: istyle.eu' -H 'X-Forwarded-Proto: https' http://localhost/mk/ 2>&1 /dev/null | grep -q "HTTP/1.1 200 OK"; then
      send_to_slack " * SERVICE VALIDATION ... OK"
    else
      send_to_slack " * SERVICE VALIDATION ... FAIL"
      exit 3
    fi
  elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
    /etc/init.d/nginx reload
    if curl -I -H 'Host: staging.istyle.mk' -H 'X-Forwarded-Proto: https' http://localhost/ 2>&1 /dev/null | grep -q "HTTP/1.1 200 OK"; then
      send_to_slack " * SERVICE VALIDATION ... OK"
    else
      send_to_slack " * SERVICE VALIDATION ... FAIL"
      exit 3
    fi
  fi

  echo -n " * RSYNC EFS BUILD FOLDER TO PRELIVE: ${EFS_PRELIVE} ... "
  if rsync -au --delete --exclude={"/var/backups/*","/var/log/*","/var/session"} ${EFS_BUILD}/ ${EFS_PRELIVE}/; then echo OK; else echo FAIL; fi

  # FOR CRONJOBS
  echo " * SET SYMLINKS TO PRELIVE FOR CRONJOBS ... "
  symlink_check "var" "${WEBROOT}/var" "${EFS_PRELIVE}/var" "${WEBROOT}/"
  symlink_check "pub/static" "${WEBROOT}/pub/static" "${EFS_PRELIVE}/pub/static" "${WEBROOT}/pub/"

  echo -n " * COPY THE ENV FILE WITH THE DATABASES BACK FOR CRONJOBS ... "
  if cp -a ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

  echo -n " * ENABLE CRON .. "
  if cp /mnt/istyle-storage/istyle/crontab_on /etc/crontab; then echo OK; else echo FAIL; fi

  echo -n " * COPY THE CONFIG.PHP TO NFS ... "
  if cp -a ${WEBROOT}/app/etc/config.php ${EFS}/env/; then echo OK; else echo FAIL; fi

  echo -n " * MODIFY PHP-CLI CONFIG BACK .. "
  if cp ${EFS}/php-orig.conf /etc/php/7.0/cli/php.ini; then echo OK; else echo FAIL; fi

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

    if maintenance_action block; then
      magento "setup:upgrade --keep-generated"
      magento "cache:enable"
    fi

  else
    echo "NO ==="
    echo
    echo -n " * COPY THE CONFIG.PHP FROM NFS ... "
    if cp -a ${EFS}/env/config.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

    magento "cache:enable"
  fi
fi

## GENERAL STUFF ##

# COPY UPLOADED FILES FROM NFS TO THE WEBROOT FOR REWRITES
echo -n " * COPY UPLOADED FILES FOR REWRITES FROM NFS TO THE WEBROOT ... "
if rsync -a ${EFS}/rewrite/ ${WEBROOT}/; then echo OK; else echo FAIL; fi

# MAIN SYMLINK SETUP
symlink_check "CREATE DIRECTORY SYMLINK TO MEDIA FOLDER" "${WEBROOT}/pub/media" "${EFS}/media" "${WEBROOT}/pub/"

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
chown www-data:www-data -R ${LOGDIR}
chown www-data:www-data -R ${WEBROOT}

restart_services

echo
echo "================================================="
echo "              END OF DEPLOY SCRIPT               "
echo "================================================="
echo

exit 0

