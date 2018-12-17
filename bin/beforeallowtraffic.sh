#!/bin/bash
set -u

EFS="/mnt/istyle-storage/istyle"
EFS_LIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | head -1)"
WEBROOT="/var/www/istyle.eu/webroot"
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)

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

if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  MASTER_ID="i-0a57263aca752890a"
elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
  MASTER_ID="i-07102058010aa5695"
else
  # DEVELOPMENT #
  MASTER_ID=""
fi


if [[ "${INSTANCE_ID}" != "${MASTER_ID}" ]]; then
  sleep $[ ( $RANDOM % 10 ) + 1 ].$[ ( $RANDOM % 1000 ) + 1 ]
  if [ -f ${EFS_LIVE}/var/.maintenance.flag ]; then
    echo -n " * DISABLE MAINTENANCE MODE .. "
    if rm ${EFS_LIVE}/var/.maintenance.flag; then echo OK; else echo FAIL; fi
  fi

  echo " * MAGENTO CACHE FLUSH: "
  #sudo -u www-data php ${WEBROOT}/bin/magento cache:flush
fi

