#!/bin/sh
/etc/init.d/supervisor start
exec /usr/sbin/php-fpm7.1 -F
