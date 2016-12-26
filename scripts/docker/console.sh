#!/usr/bin/env bash
docker exec -i mbh-php-fpm php /var/www/mbh/bin/console "$@"
