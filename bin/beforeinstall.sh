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
    \"username\": \"${INSTANCE_ID}\",
    \"text\": \"$MESSAGE\"
    }"
  curl -X POST --data-urlencode "${PAYLOAD}" ${SLACK_WEBHOOK} &> /dev/null
}

nfs_check() {
  sleep 30
  if [ ! -f ${EFS}/.nfs.check ]; then
    if ! mount -a; then
      send_to_slack "SOMETHING IS WRONG WITH THE NFS MOUNT, PLEASE CHECK!"
      exit 100
    fi
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
  echo " DONE!"

  echo -n " * STOP PHP .. "
  if pgrep php &> /dev/null; then
    if pkill -9 php; then echo OK; else echo FAIL; fi
  else
    echo "DONE"
  fi
}

recreate_dirs() {
  echo -n " * STOP NGINX .. "
  if pgrep nginx &> /dev/null; then
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
cron_check
if recreate_dirs; then echo "OK"; fi

if dpkg -l|grep monit &> /dev/null; then
  echo -n " * STOP MONIT .. "
  if /etc/init.d/monit stop &> /dev/null; then echo OK; else echo FAIL; fi
fi

echo -n " * MODIFY PHP-CLI CONFIG FOR DEPLOY .. "
if cp ${EFS}/php-deploy.conf /etc/php/7.0/cli/php.ini; then echo OK; else echo FAIL; fi

