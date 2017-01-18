#!/usr/bin/env bash
docker exec -i mbh-php-fpm2 php /var/www/mbh/bin/console "$@"
