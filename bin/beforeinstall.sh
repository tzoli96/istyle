#!/bin/bash

/etc/init.d/php7.0-fpm stop
/etc/init.d/nginx stop

SLACK_WEBHOOK="https://hooks.slack.com/services/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"
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

send_to_slack "iStyle Deploy started on DEV"

if [ -d ${WEBROOT} ]; then
    rm -rf ${WEBROOT}
    rm -rf ${LOGDIR}
fi

mkdir -p ${WEBROOT}
mkdir -p ${LOGDIR}/log/oander
chown www-data:www-data -R /var/www/.composer
chown www-data:www-data -R ${LOGDIR}
chown www-data:www-data -R ${WEBROOT}

