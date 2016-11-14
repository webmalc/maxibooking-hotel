#!/bin/bash
rsync -avz --delete --exclude-from=scripts/ignore_new.txt * -e ssh root@mbhs.maxibooking.ru:/var/www/mbhs/deploy/