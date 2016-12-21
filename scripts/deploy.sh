#!/bin/bash
SERVER='root@176.112.204.203'
GREEN='\e[0;32m'
RED='\e[0;31m'
NC='\e[0m'

NAME='az-test'
IGNORE='ignore_update.txt'
DB_USER="${1////_}"
FOLDER='/var/www/'$NAME'/'
APC=$FOLDER'bin/console mbh:demo:apc --name='$NAME
CACHE='rm -rf '$FOLDER'var/cache/*'
PROXIES=$FOLDER'bin/console doctrine:mongodb:generate:proxies'
HYDRATORS=$FOLDER'bin/console doctrine:mongodb:generate:hydrators'
FOS=$FOLDER'bin/console fos:js-routing:dump'
ASSEST=$FOLDER'bin/console assets:install '$FOLDER'web --symlink'
ASSESTIC=$FOLDER'bin/console assetic:dump'
DB=$FOLDER'bin/console doctrine:mongodb:schema:create'
PHP_FPM='service php7.0-fpm restart'
MOVE_PARAMS='mv -f '$FOLDER'parameters.yml '$FOLDER'/app/config/parameters.yml'

echo -e "${GREEN}Start rsync${NC}"

rsync -avz --delete --exclude-from=scripts/$IGNORE * -e ssh $SERVER:$FOLDER

#echo -e "${GREEN}Start mbh:demo:apc${NC}"
#ssh $SERVER $APC
#
#echo -e "${GREEN}Make cache, logs and upload directories${NC}"
#ssh $SERVER 'setfacl -R -m u:"www-data":rwX -m u:"root":rwX '$FOLDER'var/cache '$FOLDER'var/logs '$FOLDER'protectedUpload '$FOLDER'web/media'
#ssh $SERVER 'setfacl -dR -m u:"www-data":rwX -m u:"root":rwX '$FOLDER'var/cache '$FOLDER'var/logs '$FOLDER'protectedUpload '$FOLDER'web/media'

#echo -e "${GREEN}Start clear:cache${NC}"
#ssh $SERVER $CACHE

#echo -e "${GREEN}Start doctrine:mongodb:generate:hydrators${NC}"
#ssh $SERVER $HYDRATORS
#
#echo -e "${GREEN}Start doctrine:mongodb:generate:proxies${NC}"
#ssh $SERVER $PROXIES
#
#echo -e "${GREEN}Start fos:dump${NC}"
#ssh $SERVER $FOS
#
#echo -e "${GREEN}Start assets:install${NC}"
#ssh $SERVER $ASSEST
#
#echo -e "${GREEN}Start assetic:dump${NC}"
#ssh $SERVER $ASSESTIC
#
#echo -e "${GREEN}Start doctrine:mongodb:schema:create${NC}"
#ssh $SERVER $DB
#
#echo -e "${GREEN}Start clear:cache${NC}"
#ssh $SERVER $CACHE
#
#ssh $SERVER $PHP_FPM
#ssh $SERVER 'service nginx restart'
#
