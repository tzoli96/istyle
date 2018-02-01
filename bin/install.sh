#!/bin/bash

WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/istyle-storage/istyle"
EFS_BLUE="/mnt/istyle-storage/istyle/blue"
EFS_GREEN="/mnt/istyle-storage/istyle/green"
INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

echo -n " * CREATE DIRECTORY SYMLINK TO MEDIA FOLDER ... "
[ -L ${WEBROOT}/pub/media ] && rm ${WEBROOT}/pub/media &> /dev/null || rm -rf ${WEBROOT}/pub/media
if ln -s ${EFS}/media ${WEBROOT}/pub/; then echo OK; else echo FAIL; fi
if ln -s ${EFS}/upload ${WEBROOT}/; then echo OK; else echo FAIL; fi

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]; then
   echo "### MASTER WORKFLOW ###"
   echo -n " * COPY THE ENV FILE WITH THE DATABASES FOR UPGRADE ... "
   if cp ${EFS}/env/upgrade_env.php ${WEBROOT}/app/etc/env.php; then echo OK; else echo FAIL; fi
   
   echo -n " * RSYNC BLUE FOLDER TO GREEN WITH EXCEPTIONS ... "
   if time rsync -au --exclude={"/var/backups/*","/var/log/*","/var/generation/*","/var/di/*","/pub/static/*"} ${EFS_BLUE}/* ${EFS_GREEN}/; then echo OK; else echo FAIL; fi

   echo " * CREATE DIRECTORY SYMLINKS TO GREEN:"
   [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var || rm -rf ${WEBROOT}/var
   echo -n "var ... "
   if ln -s ${EFS_GREEN}/var ${WEBROOT}/; then echo OK; else echo FAIL; fi
   echo -n "check var/log ... "
   [ -L ${WEBROOT}/var/log ] && echo OK || echo FAIL
   echo -n "pub/static ... "
   [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static &> /dev/null || rm -rf ${WEBROOT}/pub/static
   if ln -s ${EFS_GREEN}/pub/static ${WEBROOT}/pub/; then echo OK; else echo FAIL; fi

   echo "### COMPOSER INSTALL & MAGENTO UPGRADE ###"
   # cd ${WEBROOT} && npm install
   cd ${WEBROOT} && time composer install
#   cd ${WEBROOT} && php bin/magento maintenance:enable

   echo -n "### CHECK IF DB UPGRADE NEEDED ==> "
   if php ${WEBROOT}bin/magento setup:db:status | grep -q "up to date"; then
      echo "UPGRADE WITHOUT DB CHANGE ###"
      echo "### SETUP UPGRADE ###"
      cd ${WEBROOT} && time php bin/magento setup:upgrade
   else
      echo "UPGRADE WITH DB CHANGE ###"
      echo " * COPY DATABASES:"
      echo -n "istyle ... "
      mysql -e 'DROP DATABASE istyle_upg; CREATE DATABASE istyle_upg;'
      mysqldump --skip-add-drop-table --no-data istyle | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql istyle_upg
      if mysqldump --single-transaction istyle weee_tax theme product_alert_price eav_entity_type core_config_data setup_module store store_group store_website | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql istyle_upg; then echo OK; else echo FAIL; fi
      echo -n "istyle-warehousemanager ... "
      mysql -e 'DROP DATABASE istylewh_upg; CREATE DATABASE istylewh_upg;'
      if mysqldump --single-transaction istyle-warehousemanager | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql istylewh_upg; then echo OK; else echo FAIL; fi
      echo -n "istyle-apigateway ... "
      mysql -e 'DROP DATABASE istyleapi_upg; CREATE DATABASE istyleapi_upg;'
      if mysqldump --single-transaction istyle-apigateway | sed 's/`istylem2`@`%`/`root`@`%`/g' | mysql istyleapi_upg; then echo OK; else echo FAIL; fi

      echo "### SETUP UPGRADE ###"
      cd ${WEBROOT} && time php bin/magento setup:upgrade
   fi

   echo "### COMPILE STATIC CONTENTS ###"
   cd ${WEBROOT} && time php bin/magento setup:di:compile
   cd ${WEBROOT} && time php bin/magento setup:static-content:deploy en_US
   cd ${WEBROOT} && time php bin/magento setup:static-content:deploy mk_MK
   cd ${WEBROOT} && time php bin/magento setup:static-content:deploy hu_HU
   cd ${WEBROOT} && php bin/magento cache:enable
   echo -n " * CREATE FLAG FOR BLUE/GREEN DEPLOYMENT ... "
   if touch ${EFS}/deployed.flag; then echo OK; else echo FAIL; fi
   echo -n " * CHOWN EFS_GREEN DIR ... "
   if time chown www-data:www-data -R ${EFS_GREEN}; then echo OK; else echo FAIL; fi

   echo -n " * COPY THE ENV FILE WITH THE DATABASES TO PRODUCTION FOR CRONJOBS ... "
   if cp ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

   echo " * SET BACK SYMLINKS TO BLUE:"
   [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var || rm -rf ${WEBROOT}/var
   echo -n "var ... "
   if ln -s ${EFS_BLUE}/var ${WEBROOT}/; then echo OK; else echo FAIL; fi
   [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static || rm -rf ${WEBROOT}/pub/static
   echo -n "pub/static ... "
   if ln -s ${EFS_BLUE}/pub/static ${WEBROOT}/pub/; then echo OK; else echo FAIL; fi
#   cd ${WEBROOT} && php bin/magento maintenance:disable
else
   echo "### WORKER INSTANCES ###"
   echo -n " * CONFIGURE THE ENV FILE WITH THE PRODUCTION DATABASES FOR UPGRADE ... "
   if cp ${EFS}/env/env.php ${WEBROOT}/app/etc/; then echo OK; else echo FAIL; fi

   echo " * DELETE VENDOR DIRECTORY ... "
   if time rm -rf ${WEBROOT}/vendor/*; then echo OK; else echo FAIL; fi
   echo "### COMPOSER INSTALL ###"
   cd ${WEBROOT} && time composer install

   echo -n "### CHECK IF DEPLOYED FLAG EXISTS ==> "
   if [ -f ${EFS}/deployed.flag ]; then
      echo "YES"
      echo -n " * RSYNC EFS GREEN TO BLUE ... "
      if time rsync -au --exclude={"/var/backups/*","/var/log/*"} ${EFS_GREEN}/* ${EFS_BLUE}/; then echo OK; else echo FAIL; fi
      echo " * CREATE DIRECTORY SYMLINKS TO BLUE:"
      echo -n "var ... "
      [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var || rm -rf ${WEBROOT}/var
      if ln -s ${EFS_BLUE}/var ${WEBROOT}/; then echo OK; else echo FAIL; fi
      echo -n "pub/static ... "
      [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static || rm -rf ${WEBROOT}/pub/static
      echo "### SETUP UPGRADE :: KEEP-GENERATED ###"
      cp ${WEBROOT}/pub/errors/default/maintenance.phtml ${WEBROOT}/pub/errors/default/503.phtml
      cd ${WEBROOT} && php bin/magento maintenance:enable
      sleep 10
      cd ${WEBROOT} && php bin/magento setup:upgrade --keep-generated
      cd ${WEBROOT} && php bin/magento maintenance:disable
      sleep 20
      cd ${WEBROOT} && php bin/magento cache:enable
      echo -n " * REMOVING DEPLOYED FLAG ... "
      if rm ${EFS}/deployed.flag; then echo OK; else echo FAIL; fi
   else
      echo "NO"
      echo " * CREATE DIRECTORY SYMLINKS TO BLUE:"
      echo -n "var ... "
      [ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var || rm -rf ${WEBROOT}/var
      if ln -s ${EFS_BLUE}/var ${WEBROOT}/; then echo OK; else echo FAIL; fi
      echo -n "pub/static ... "
      [ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static || rm -rf ${WEBROOT}/pub/static
      if ln -s ${EFS_BLUE}/pub/static ${WEBROOT}/pub/; then echo OK; else echo FAIL; fi
      cd ${WEBROOT} && php bin/magento cache:enable
   fi
fi

chown www-data:www-data -R /var/log/magento
chown www-data:www-data -R /var/www/istyle.eu/
/etc/init.d/php7.0-fpm restart

exit 0
