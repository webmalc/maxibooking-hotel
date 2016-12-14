#!/bin/sh
/var/www/mbh/bin/console rabbitmq-supervisor:rebuild
exec /usr/sbin/php-fpm7.1 -F
