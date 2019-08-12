#!/usr/bin/env bash
docker exec -i mbh-php-fpm php /usr/local/bin/composer "$@"
