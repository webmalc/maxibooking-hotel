#!/usr/bin/env bash
cd docker
docker exec -i mbh-php-fpm php /var/www/mbh/bin/console "$@"
