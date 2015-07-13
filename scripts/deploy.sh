#!/bin/bash
SERVER='root@128.199.55.176'
GREEN='\e[0;32m'
RED='\e[0;31m'
NC='\e[0m'

if [[ -z "$1" ]]; then
	echo -e "${RED}Error: path=false! Usage: scripts/deploy.sh last.mbf [update] ${NC}"
	exit
fi

if [[ $2 == 'new' ]]; then
    IGNORE='ignore_new.txt'
else
    IGNORE='ignore_update.txt'
fi

if [[ -z "$3" && $2 == 'new' ]]; then
    echo -e "${RED}Error: path=false! Usage: scripts/deploy.sh last.mbf new password ${NC}"
	exit
fi

if [[ $4 == 'demo' ]]; then
    SERVER='root@95.85.3.188'
fi


DB_USER="${1////_}"
FOLDER='/var/www/'$1'/'
APC=$FOLDER'bin/console mbh:demo:apc --name='$1
CACHE='rm -rf '$FOLDER'var/cache/*'
PROXIES=$FOLDER'bin/console doctrine:mongodb:generate:proxies'
HYDRATORS=$FOLDER'bin/console doctrine:mongodb:generate:hydrators'
FOS=$FOLDER'bin/console fos:js-routing:dump'
ASSEST=$FOLDER'bin/console assets:install '$FOLDER'web --symlink'
ASSESTIC=$FOLDER'bin/console assetic:dump'
DB=$FOLDER'bin/console doctrine:mongodb:schema:create'
PHP_FPM='service php5-fpm restart'
MOVE_PARAMS='mv -f '$FOLDER'parameters.yml '$FOLDER'/app/config/parameters.yml'

echo -e "${GREEN}Start rsync${NC}"

rsync -avz --delete --exclude-from=scripts/$IGNORE * -e ssh $SERVER:$FOLDER

if [[ $2 == 'new' ]]; then

    echo -e "${GREEN}New database${NC}"
    ssh $SERVER '/root/scripts/mongo_user.sh '$3' '$DB_USER

    echo -e "${GREEN}New nginx server${NC}"
    ssh $SERVER '/root/scripts/nginx.sh '$DB_USER

    echo -e "${GREEN}Move parameters.yml${NC}"
    ssh $SERVER $MOVE_PARAMS
fi

echo -e "${GREEN}Start mbh:demo:apc${NC}"
ssh $SERVER $APC

echo -e "${GREEN}Make cache, logs and upload directories${NC}"
ssh $SERVER 'setfacl -R -m u:"www-data":rwX -m u:"root":rwX '$FOLDER'var/cache '$FOLDER'var/logs '$FOLDER'protectedUpload '$FOLDER'web/media'
ssh $SERVER 'setfacl -dR -m u:"www-data":rwX -m u:"root":rwX '$FOLDER'var/cache '$FOLDER'var/logs '$FOLDER'protectedUpload '$FOLDER'web/media'

echo -e "${GREEN}Start clear:cache${NC}"
ssh $SERVER $CACHE

echo -e "${GREEN}Start doctrine:mongodb:generate:hydrators${NC}"
ssh $SERVER $HYDRATORS

echo -e "${GREEN}Start octrine:mongodb:generate:proxies${NC}"
ssh $SERVER $PROXIES

echo -e "${GREEN}Start fos:dump${NC}"
ssh $SERVER $FOS

echo -e "${GREEN}Start assets:install${NC}"
ssh $SERVER $ASSEST

echo -e "${GREEN}Start assetic:dump${NC}"
ssh $SERVER $ASSESTIC

echo -e "${GREEN}Start doctrine:mongodb:schema:create${NC}"
ssh $SERVER $DB

if [[ $2 == 'new' ]]; then
    echo -e "${GREEN}Upload fixtures${NC}"
    ssh $SERVER $FOLDER'bin/console mbh:base:fixtures --cities'
    ssh $SERVER $FOLDER'bin/console mbh:vega:import'
    ssh $SERVER $FOLDER'bin/console mbh:currency:load'
fi

echo -e "${GREEN}Start clear:cache${NC}"
ssh $SERVER $CACHE

ssh $SERVER $PHP_FPM
ssh $SERVER 'service nginx restart'

