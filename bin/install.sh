#!/bin/bash
WEBROOT="/var/www/istyle.eu/webroot"

#/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:enable 

cp /mnt/efs/istyle/env/env.php ${WEBROOT}/app/etc/
ln -s /mnt/efs/istyle/media/ ${WEBROOT}/media
if [ -d ${WEBROOT}/pub/static];then rmdir ${WEBROOT}/pub/static;else rm ${WEBROOT}/pub/static;fi
ln -s /mnt/efs/istyle/pub/static/ ${WEBROOT}/pub/
if [ -d ${WEBROOT}/var ];then rmdir ${WEBROOT}/var;else rm ${WEBROOT}/var;fi
ln -s /mnt/efs/istyle/var ${WEBROOT}/var

INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]
	then 
		#MASTER WORKFLOW
#		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/npm install"
		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/composer install"
		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:upgrade"
#		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:di:compile"
		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy"
		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:upgrade --keep-generated "
	else 
		#WORKER BRANCH
		/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:enable
		sleep 10
		/bin/su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/composer install"
		/usr/bin/php /var/www/istyle.eu/webroot/bin/magento maintenance:disable
fi

/bin/chown www-data:www-data -R ${WEBROOT}
/etc/init.d/php7.0-fpm restart
