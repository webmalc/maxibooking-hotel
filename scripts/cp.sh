#!/bin/sh
FOLDER='/var/www/'$1
HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`

if [ -z "$1" ]; then
    echo "folder is null "
    exit
fi

rm -rf $FOLDER
mkdir $FOLDER
rsync -avz --exclude-from=/var/www/mbh/scripts/cp_ingnore.txt /var/www/mbh/ $FOLDER
cd $FOLDER'/'
setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs
bin/console mbh:demo:apc --name='demo_'$1_$RANDOM
bin/console cache:clear
bin/console cache:clear --env=prod
bin/console mbh:demo:params --db='demo_'$1
bin/console mbh:demo:insert_scripts
bin/console fos:js-routing:dump
bin/console assets:install --symlink
bin/console assetic:dump
bin/console doctrine:mongodb:schema:drop
bin/console doctrine:mongodb:schema:create
bin/console doctrine:mongodb:generate:hydrators
bin/console doctrine:mongodb:generate:proxies
bin/console fos:user:create demo user@example.com demo
bin/console fos:user:promote demo ROLE_ADMIN
bin/console cache:clear
bin/console cache:clear --env=prod
bin/console mbh:demo:load --name=$1
bin/console mbh:cache:generate --no-debug
bin/console mbh:demo:online_form




