#!/usr/bin/env bash
cd docker
docker exec -i mbh-php-fpm bash -c "cd /var/www/mbh && phpunit"
