#!/bin/bash
WEBROOT="/var/www/istyle.eu/webroot"

#/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:enable 

cp /mnt/efs/istyle/env/env.php ${WEBROOT}/app/etc/
ln -s /mnt/efs/istyle/media/ ${WEBROOT}/
[ -d ${WEBROOT}/pub/static ] && rm -rf ${WEBROOT}/pub/static || rm ${WEBROOT}/pub/static
ln -s /mnt/efs/istyle/pub/static/ ${WEBROOT}/pub/
[ -d ${WEBROOT}/var ] && rmdir ${WEBROOT}/var || rm ${WEBROOT}/var
ln -s /mnt/efs/istyle/var/ ${WEBROOT}/

INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]
	then 
		#MASTER WORKFLOW
#		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/npm install"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/composer install"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:upgrade"
#		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:di:compile"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy"
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/php bin/magento setup:upgrade --keep-generated "
	else 
		#WORKER BRANCH
		/usr/bin/php ${WEBROOT}/bin/magento maintenance:enable
		sleep 10
		/bin/su - www-data -s /bin/bash -c "cd ${WEBROOT} && /usr/bin/composer install"
		/usr/bin/php ${WEBROOT}/bin/magento maintenance:disable
fi

/bin/chown www-data:www-data -R ${WEBROOT}
/etc/init.d/php7.0-fpm restart
