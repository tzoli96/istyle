#!/bin/bash
set -u

export PATH=/root/.local/bin:$PATH

SLACK_WEBHOOK="https://hooks.slack.com/services/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"
EFS="/mnt/istyle-storage/istyle"
WEBROOT="/var/www/istyle.eu/"
LOGDIR="/var/log/magento"

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

nfs_check() {
  if [ ! -f ${EFS}/.nfs.check ]; then
    if ! mount -a; then 
      send_to_slack "SOMETHING IS WRONG WITH THE NFS MOUNT, PLEASE CHECK!"
      exit 100
    fi
  fi

  LIVE_DIR_COUNT=$(ls -1 ${EFS}/ | grep -c live_)
  if [[ "${LIVE_DIR_COUNT}" != "2" ]]; then
    send_to_slack "SOMETHING IS WRONG WITH THE NFS LIVE FOLDERS, PLEASE CHECK!"
    exit 101
  fi
}

cron_check() {
  echo -n " * DISABLE CRON .. "
  if cp ${EFS}/crontab_off /etc/crontab; then echo OK; else echo FAIL; fi
  
  echo -n " * CHECKING CRON: "
  while pgrep -x php7.0 > /dev/null; do
    echo -n "."
    sleep 5
  done
}

recreate_dirs() {
echo -n " * DELETE AND RECREATE WEBROOT DIRECTORIES .. "
if [ -d ${WEBROOT} ]; then
  rm -rf ${WEBROOT}
  mkdir -p ${WEBROOT}
fi

if [ ! -d ${LOGDIR}/log/oander ]; then
  mkdir -p ${LOGDIR}/log/oander
fi

chown www-data:www-data -R ${LOGDIR}
chown www-data:www-data -R ${WEBROOT}
}

restart_services() {
  echo -n " * STOP PHP .. "
  if pkill -9 php; then echo OK; else echo FAIL; fi
  echo -n " * STOP NGINX .. "
  if pkill -9 nginx; then echo OK; else echo FAIL; fi

  if ! /etc/init.d/php7.0-fpm restart &> /dev/null; then
     send_to_slack "SOMETHING IS WRONG WITH THE PHP PROCESS, PLEASE CHECK!"
     exit 102
  fi
  
  if ! /etc/init.d/nginx restart &> /dev/null; then
     send_to_slack "SOMETHING IS WRONG WITH THE NGINX PROCESS, PLEASE CHECK!"
     exit 103
  fi
}


nfs_check
if cron_check; then echo " DONE!"; fi
if recreate_dirs; then echo "OK"; fi

echo -n " * MODIFY PHP-CLI CONFIG FOR DEPLOY .. "
if cp ${EFS}/php-deploy.conf /etc/php/7.0/cli/php.ini; then echo OK; else echo FAIL; fi

restart_services


