#!/bin/bash

WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/efs/istyle"

#/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:enable 

cp ${EFS}/env/env.php ${WEBROOT}/app/etc/

[ -L ${WEBROOT}/media ] && rm ${WEBROOT}/media
ln -s ${EFS}/media ${WEBROOT}/

[ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static || rm -rf ${WEBROOT}/pub/static
ln -s ${EFS}/pub/static ${WEBROOT}/pub/

[ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var
ln -s ${EFS}/var ${WEBROOT}/

INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]
	then 
		#MASTER WORKFLOW
#		cd ${WEBROOT} && /usr/bin/npm install
		cd ${WEBROOT} && /usr/bin/composer install"
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento maintenance:enable
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:upgrade
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:di:compile
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:static-content:deploy en_US
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:static-content:deploy mk_MK
#		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:static-content:deploy
#		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento setup:upgrade --keep-generated
		cd ${WEBROOT} && /usr/bin/php ${WEBROOT}/bin/magento maintenance:disable
	else 
		#WORKER BRANCH
#		/usr/bin/php ${WEBROOT}/bin/magento maintenance:enable
#		sleep 10
		cd ${WEBROOT} && /usr/bin/composer install
#		/usr/bin/php ${WEBROOT}/bin/magento maintenance:disable
fi

/bin/chown www-data:www-data -R /var/www/istyle.eu/
/etc/init.d/php7.0-fpm restart
