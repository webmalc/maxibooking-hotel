#!/usr/bin/env bash
GREEN='\e[0;32m'
RED='\e[0;31m'
NC='\e[0m'

if [[ -z "$1" ]]; then
	echo -e "${RED}Error: Usage: mongoDbCopy.sh remote_dbName local_dbName server_ip ${NC}"
	exit
fi
if [[ -z "$2" ]]; then
	echo -e "${RED}Error: Usage: mongoDbCopy.sh remote_dbName local_dbName server_ip ${NC}"
	exit
fi
if [[ -z "$3" ]]; then
	echo -e "${RED}Error: Usage: mongoDbCopy.sh remote_dbName local_dbName server_ip ${NC}"
	exit
fi

if [[ -z "$4" ]]; then
	echo -e "${RED}Error: Usage: mongoDbCopy.sh remote_dbName local_dbName server_ip mongohost:port ${NC}"
	exit
fi

REMOTE_SERVER='root@'${3}
REMOTE_DB=$1
LOCAL_DB=$2
MONGO_LOCAL_HOST=$4
REMOTE_ARCHIVE_FOLDER="/root/mongoarchives"
REMOTE_ARCHIVE="$REMOTE_ARCHIVE_FOLDER/$REMOTE_DB.tar.gz"
LOCAL_ARCHIVE_FOLDER="~/mongoarchives"
LOCAL_ARCHIVE="$LOCAL_ARCHIVE_FOLDER/$LOCAL_DB.tar.gz"

REMOTE_MONGO_DUMP="mongodump --db $REMOTE_DB --gzip --archive=$REMOTE_ARCHIVE"
GET_REMOTE_MONGO_DUMP="rsync -avz -e ssh $REMOTE_SERVER:$REMOTE_ARCHIVE $LOCAL_ARCHIVE"
LOCAL_MONGO_RESTORE="mongorestore --db=$LOCAL_DB --gzip --archive=$LOCAL_ARCHIVE --host $MONGO_LOCAL_HOST"

echo "mkdir -p $REMOTE_ARCHIVE_FOLDER"|ssh $REMOTE_SERVER
echo $REMOTE_MONGO_DUMP | ssh $REMOTE_SERVER
mkdir -p $LOCAL_ARCHIVE_FOLDER
$GET_REMOTE_MONGO_DUMP
$LOCAL_MONGO_RESTORE

