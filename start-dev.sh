#!/bin/bash
export ALIAS_DOMAIN=reports.site.org
docker run -d \
	-v $(pwd)/dev/site:/app \
	-v $(pwd)/dev/nginx/vhost.common.d:/opt/docker/etc/nginx/vhost.common.d \
	-v $(pwd)/dev/basic:/home/application/.config/libreoffice/4/user/basic \
	-v $(pwd)/dev/basic:/root/.config/libreoffice/4/user/basic \
	-p 8080:80 \
	-p 8443:443 \
	--name reports-dev \
	-e "ALIAS_DOMAIN=$ALIAS_DOMAIN" \
	nginx-php-lo
# run -ti --rm \
#	-v $(pwd)/dev/site:/app \
#	-v $(pwd)/dev/tmp:/tmp \
#	nginx-php-lo /bin/bash