#!/bin/bash
rsync -avz --delete --exclude-from=scripts/ignore_new.txt * -e ssh root@mbhs.maxibooking.ru:/var/www/mbhs/deploy/
ssh root@mbhs.maxibooking.ru setfacl -R -m u:"www-data":rwX /var/www/mbhs/deploy
ssh root@mbhs.maxibooking.ru setfacl -dR -m u:"www-data":rwX /var/www/mbhs/deploy