#!/bin/bash
WEBROOT="/var/www/istyle.eu/webroot"

#php bin/magento maintenance:enable 

cp /mnt/efs/istyle/env/env.php ${WEBROOT}/app/etc
ln -s /mnt/efs/istyle/media/ ${WEBROOT}/media
if [ -d ${WEBROOT}/pub/static];then rmdir ${WEBROOT}/pub/static;else rm ${WEBROOT}/pub/static;fi
ln -s /mnt/efs/istyle/pub/static ${WEBROOT}/pub/static
if [ -d ${WEBROOT}/var ];then rmdir ${WEBROOT}/var;else rm ${WEBROOT}/var;fi
ln -s /mnt/efs/istyle/var ${WEBROOT}/var

INSTANCE_ID=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
MASTER_ID="i-0a57263aca752890a"

if [ "${INSTANCE_ID}" == "${MASTER_ID}" ]
	then 
		#MASTER WORKFLOW
#		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && npm install"
		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && composer install"
		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:upgrade"
#		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:di:compile"
		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:static-content:deploy"
		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && /usr/bin/php bin/magento setup:upgrade --keep-generated "

	else 
		#WORKER BRANCH
		su - www-data -s /bin/bash -c "cd /var/www/${WEBROOT} && composer install"
fi

chown www-data:www-data -R ${WEBROOT}
/etc/init.d/php7.0-fpm restart
