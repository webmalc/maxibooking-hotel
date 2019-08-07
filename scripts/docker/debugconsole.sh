#!/usr/bin/env bash
docker exec -i mbh-php-fpm env XDEBUG_CONFIG="ideKey=PHPSTORM" PHP_IDE_CONFIG="serverName=cli" php /var/www/mbh/bin/console "$@"
