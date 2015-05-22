#!/bin/bash
GREEN='\e[0;32m'
NC='\e[0m'
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
FOLDER=${DIR}'/../var/'
HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`

sudo rm -rf ${FOLDER}'cache'
sudo rm -rf ${FOLDER}'logs'
mkdir ${FOLDER}'cache'
mkdir ${FOLDER}'logs'
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${DIR}'/../var/cache' ${DIR}'/../var/logs' ${DIR}'/../protectedUpload'
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${DIR}'/../var/cache' ${DIR}'/../var/logs' ${DIR}'/../protectedUpload'

echo -e "${GREEN}Complete!${NC}"