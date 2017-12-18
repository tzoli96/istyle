#!/bin/bash

WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/efs/istyle"
EFS_BLUE="/mnt/efs/istyle/blue"
EFS_GREEN="/mnt/efs/istyle/green"
INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

[ -L ${WEBROOT}/pub/media ] && rm ${WEBROOT}/pub/media &> /dev/null || rm -rf ${WEBROOT}/pub/media
ln -s ${EFS}/media ${WEBROOT}/pub/

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]; then
   # MASTER WORKFLOW #
   # UPDATE THE ENVIRONMENT FILE WITH NEW DATABASES
   cp ${EFS}/env/upgrade_env.php ${WEBROOT}/app/etc/env.php
   
   # RSYNC BLUE FOLDER TO GREEN WITH EXCEPTIONS
   time rsync -aur --exclude={"/var/backups/*","/var/generation/*","/var/di/*","/pub/static/*"} ${EFS_BLUE}/* ${EFS_GREEN}/

   # CREATE SYMLINKS TO GREEN
   [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var
   ln -s ${EFS_GREEN}/var ${WEBROOT}/
   [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static &> /dev/null || rm -rf ${WEBROOT}/pub/static
   ln -s ${EFS_GREEN}/pub/static ${WEBROOT}/pub/

   # INSTALL CODE
   # cd ${WEBROOT} && npm install
   cd ${WEBROOT} && composer install
#   cd ${WEBROOT} && php bin/magento maintenance:enable

   # CHECK IF DB UPGRADE NEEDED
   if php ${WEBROOT}bin/magento setup:db:status | grep -q "up to date"; then
      # MAGENTO UPGRADE PROCESS WITHOUT DB CHANGE
      cd ${WEBROOT} && php bin/magento setup:upgrade
   else
      # COPY DB
      mysql -e 'DROP DATABASE istyle_upg; CREATE DATABASE istyle_upg;'
      mysqldump istyle | mysql istyle_upg
      mysql -e 'DROP DATABASE istylewh_upg; CREATE DATABASE istylewh_upg;'
      mysqldump istyle-warehousemanager | mysql istylewh_upg
      mysql -e 'DROP DATABASE istyleapi_upg; CREATE DATABASE istyleapi_upg;'
      mysqldump istyle-apigateway | mysql istyleapi_upg

      # UPGRADE MAGENTO WITH DB CHANGE
      cd ${WEBROOT} && php bin/magento setup:upgrade
   fi

   # CONTINUE MAGENTO UPGRADE PROCESS
   cd ${WEBROOT} && php bin/magento setup:di:compile
   cd ${WEBROOT} && php bin/magento setup:static-content:deploy en_US
   cd ${WEBROOT} && php bin/magento setup:static-content:deploy mk_MK
   touch ${EFS}/deployed.flag
   cd ${WEBROOT} && php bin/magento maintenance:disable
   chown www-data:www-data -R ${EFS_GREEN}
else
   # WORKER INSTANCES #
   cp ${EFS}/env/env.php ${WEBROOT}/app/etc/

   # CREATE SYMLINKS TO BLUE
   [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var
   ln -s ${EFS_BLUE}/var ${WEBROOT}/
   [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static || rm -rf ${WEBROOT}/pub/static
   ln -s ${EFS_BLUE}/pub/static ${WEBROOT}/pub/

   cd ${WEBROOT} && composer install

   if [ -f ${EFS}/deployed.flag ]; then
      cd ${WEBROOT} && php bin/magento maintenance:enable
      time rsync -aur --exclude={"/var/backups/*"} ${EFS_GREEN}/* ${EFS_BLUE}/
      cd ${WEBROOT} && php bin/magento setup:upgrade --keep-generated
      cd ${WEBROOT} && php bin/magento maintenance:disable
      rm ${EFS}/deployed.flag
   fi
fi

chown www-data:www-data -R /var/www/istyle.eu/
/etc/init.d/php7.0-fpm restart

