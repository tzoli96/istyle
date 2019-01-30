#!/bin/bash
set -u

export PATH=/root/.local/bin:$PATH

EFS="/mnt/istyle-storage/istyle"
EFS_LIVE="${EFS}/$(ls -1 ${EFS} | grep live_ | tail -1)"
WEBROOT="/var/www/istyle.eu/webroot"
INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)
INSTANCE_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)
SELENIUM_ID="i-00a99d503de806802"
SELENIUM_NAME="selenium.istyle.local"
SELENIUM_SCRIPT="/home/ubuntu/selitest.py"
SELENIUM_TEST_USER="janos.pinczes@oander.hu"
SELENIUM_TEST_PASS="QWEasd123"
SELENIUM_TEST_URL="/lead-trend-twist-cable-protector.html?color=470"
SLACK_WEBHOOK="https://hooks.slack.com/services/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"

# ENVIRONMENT VARIABLES
SG_GROUPS=$(curl -s http://169.254.169.254/latest/meta-data/security-groups)
if [[ ${SG_GROUPS} =~ "prod" ]];then
  DEPLOY_ENV="PRODUCTION"
  SELENIUM_TEST_DOMAIN="https://istyle.hu"
elif [[ ${SG_GROUPS} =~ "stg" ]];then
  DEPLOY_ENV="STAGING"
  SELENIUM_TEST_DOMAIN="https://staging.istyle.hu"
elif [[ ${SG_GROUPS} =~ "dev" ]];then
  DEPLOY_ENV="DEVELOPMENT"
else
  echo "DEPLOY_ENV VARIABLE NOT SET, CHECK THE EC2 SECURITY GROUPS!"
fi

if [ "${DEPLOY_ENV}" == "PRODUCTION" ]; then
  MASTER_ID="i-0a57263aca752890a"
  WAF_IDS=('12513a30-1fbf-44e3-ba52-5974a6db6f46' '2309ba7b-c63a-4565-a3e1-5af541eb8694' '6e81ab64-f9e6-4bbf-963f-eea8d912357c' 'a7896f03-ca34-4385-b48b-bf7dff193147' 'ba4a71a8-1364-45a3-ba0a-65e91c8c5927' 'bad20f7d-08da-435b-9c4b-47aadbfdad01' 'ce148ce2-a8d1-4389-8850-93156757fc6c' 'd638c88e-9bc5-45c9-81a4-4519c762686c' 'da1d4a90-4b05-405a-8fcc-d14f8a1131ed' 'fa50b7da-fcae-4b37-84b2-802d011b0d45')
elif [ "${DEPLOY_ENV}" == "STAGING" ]; then
  MASTER_ID="i-07102058010aa5695"
  WAF_IDS=('f3254e59-5c96-47dc-9255-9f8429932044' '339503d9-c260-4ff7-9459-1e2aebcfc1f0')
else
  # DEVELOPMENT #
  MASTER_ID=""
fi


send_to_slack() {
  local MESSAGE="${1}"
  echo "$MESSAGE"
  PAYLOAD="payload={
    \"channel\": \"#istyle-collab\",
    \"username\": \"${INSTANCE_ID} :: ${INSTANCE_IP}\",
    \"text\": \"$MESSAGE\"
    }"
  curl -X POST --data-urlencode "${PAYLOAD}" ${SLACK_WEBHOOK} &> /dev/null
}

maintenance_action() {
  local ACTION="${1^^}"
  if [ "${ACTION}" == "BLOCK" ]; then MODE_ACTION="ENABLE"; else MODE_ACTION="DISABLE"; fi

  send_to_slack " * ${MODE_ACTION} MAINTENANCE MODE:"
  for WAF_ID in "${WAF_IDS[@]}"; do
    WAF_DEFAULT_ACTION=$(aws waf get-web-acl --web-acl-id ${WAF_ID} --output text | grep DEFAULTACTION | cut -f2)
    if [[ "${WAF_DEFAULT_ACTION}" != "${ACTION}" ]]; then
      WAF_TOKEN=$(aws waf get-change-token --output text)
      if [[ -n ${WAF_TOKEN// } ]]; then
        if aws waf update-web-acl --web-acl-id ${WAF_ID} --change-token ${WAF_TOKEN} --default-action Type="$ACTION" &> /dev/null; then
          send_to_slack "   - ${WAF_ID} .. OK"
        else
          send_to_slack "   - ${WAF_ID} .. FAILED"
        fi
      else
        send_to_slack "SOMETHING IS WRONG WITH THE WAF TOKENS, PLEASE CHECK."
        return 123
      fi
    fi
  done
}


if [[ "${INSTANCE_ID}" != "${MASTER_ID}" ]]; then
  echo
  echo -n " * COPY THE PRELIVE NFS DIR NAME TO A FILE ... "
  echo ${EFS_LIVE} | awk -F "/" '{print $NF}' > ${EFS}/current_live
  [ $? -eq 0 ] && echo OK || echo FAIL

  echo
  echo -n "=== CHECK IF TESTING FLAG EXISTS => "
  sleep $[ ( $RANDOM % 10 ) + 1 ].$[ ( $RANDOM % 1000 ) + 1 ]
  if [ -f ${EFS}/testing.flag ]; then
    echo "YES ==="
    echo -n " * REMOVE TESTING FLAG ... "
    if rm ${EFS}/testing.flag; then echo OK; else echo FAIL; fi

    # RUN SELENIUM TESTS
    ssh ${SELENIUM_NAME} python ${SELENIUM_SCRIPT} ${SELENIUM_TEST_DOMAIN} ${SELENIUM_TEST_USER} ${SELENIUM_TEST_PASS} ${SELENIUM_TEST_URL}
    if [ $? -eq 0 ]; then
      echo
      send_to_slack " * SELENIUM TESTS :: OK"
      echo

      # TURN MAINTENANCE OFF
      maintenance_action allow

      INSTANCE_STATUS=$(aws ec2 describe-instances --instance-ids ${SELENIUM_ID} --output text | grep STATE | head -1 | cut -f3)
      if [[ "${INSTANCE_STATUS}" == "running" ]]; then
      echo -n " * STOP SELENIUM INSTANCE ... "
      if aws ec2 stop-instances --instance-ids ${SELENIUM_ID} &> /dev/null; then echo OK; else echo FAIL; fi
      fi

      echo -n " * MODIFY PHP-CLI CONFIG BACK .. "
      if cp ${EFS}/php-orig.conf /etc/php/7.0/cli/php.ini; then echo OK; else echo FAIL; fi

      echo " * MAGENTO CACHE FLUSH: "
      if sudo -u www-data php ${WEBROOT}/bin/magento cache:flush; then
      echo
      send_to_slack "### DEPLOY FINISHED SUCCESSFULLY ###"
      echo
      fi
    else
      echo
      send_to_slack " * SELENIUM TESTS :: FAILED"
      exit 200
    fi
  else
    echo "NO ==="
    echo
  fi
fi

