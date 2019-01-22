#!/bin/bash
set -u

export PATH=/root/.local/bin:$PATH

SLACK_WEBHOOK="https://hooks.slack.com/services/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"
EFS="/mnt/istyle-storage/istyle"
WEBROOT="/var/www/istyle.eu/"
LOGDIR="/var/log/magento"
CURRENT_LIVE=$(<${EFS}/current_live)

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
}

cron_check() {
  echo -n " * DISABLE CRON .. "
  if cp ${EFS}/crontab_off /etc/crontab; then echo OK; else echo FAIL; fi

  echo -n " * CHECKING CRON: "
  while pgrep -x php7.0 > /dev/null; do
    echo -n "."
    sleep 5
  done

  echo -n " * STOP PHP .. "
  if pgrep php; then
    if pkill -9 php; then echo OK; else echo FAIL; fi
  else
    echo "DONE"
  fi
}

recreate_dirs() {
  echo -n " * STOP NGINX .. "
  if pgrep nginx; then
    if pkill -9 nginx; then echo OK; else echo FAIL; fi
  else
    echo "DONE"
  fi

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


nfs_check
if cron_check; then echo " DONE!"; fi
if recreate_dirs; then echo "OK"; fi

echo -n " * MODIFY PHP-CLI CONFIG FOR DEPLOY .. "
if cp ${EFS}/php-deploy.conf /etc/php/7.0/cli/php.ini; then echo OK; else echo FAIL; fi

