#!/bin/bash
SERVER='root@95.85.3.188'
FOLDER='/var/www/last.mbf/'
CACHE=$FOLDER'bin/console cache:clear --env=prod'
FOS=$FOLDER'bin/console fos:js-routing:dump'
ASSEST=$FOLDER'bin/console assets:install '$FOLDER'web --symlink'
ASSESTIC=$FOLDER'bin/console assetic:dump'
DB=$FOLDER'bin/console doctrine:mongodb:schema:create'
GREEN='\e[0;32m'
NC='\e[0m' 

if [[ $1 = 'force' ]]; then
	DRY_RUN=''
else
	DRY_RUN='--dry-run'
fi

echo -e "${GREEN}Start rsync $DRY_RUN${NC}"

rsync -avz $DRY_RUN --delete --exclude-from=deploy/ingnore.txt * -e ssh $SERVER:$FOLDER

if [[ $1 = 'force' ]]; then

    echo -e "${GREEN}Start clear:cache${NC}"
    ssh $SERVER $CACHE

    echo -e "${GREEN}Start fos:dump${NC}"
    ssh $SERVER $FOS

    echo -e "${GREEN}Start assets:install${NC}"
    ssh $SERVER $ASSEST

    echo -e "${GREEN}Start assetic:dump${NC}"
    ssh $SERVER $ASSESTIC

    echo -e "${GREEN}Start doctrine:mongodb:schema:create${NC}"
    ssh $SERVER $DB
fi
