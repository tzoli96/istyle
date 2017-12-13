#!/bin/bash
WEBROOT="/var/www/istyle.eu/webroot"
EFS="/mnt/efs/istyle"

#/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:enable 

cp ${EFS}/env/env.php ${WEBROOT}/app/etc/

[ -L ${WEBROOT}/media ] && rm ${WEBROOT}/media
ln -s ${EFS}/media ${WEBROOT}/

[ -L ${WEBROOT}/pub/static ] && rm ${WEBROOT}/pub/static
ln -s ${EFS}/pub/static ${WEBROOT}/pub/

[ -L ${WEBROOT}/var ] && rm ${WEBROOT}/var
ln -s ${EFS}/var ${WEBROOT}/

INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]
	then 
		#MASTER WORKFLOW
#		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/npm install"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/composer install"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento maintenance:enable
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:upgrade"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:di:compile"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy en_US"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy mk_MK"
#		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy"
#		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:upgrade --keep-generated"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento maintenance:disable
	else 
		#WORKER BRANCH
#		/usr/bin/php ${WEBROOT}/bin/magento maintenance:enable
#		sleep 10
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/composer install"
#		/usr/bin/php ${WEBROOT}/bin/magento maintenance:disable
fi

/bin/chown www-data:www-data -R ${WEBROOT}
/etc/init.d/php7.0-fpm restart
