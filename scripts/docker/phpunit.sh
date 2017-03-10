#!/usr/bin/env bash
docker exec -i mbh-php-fpm bash -c "cd /var/www/mbh && phpunit"
