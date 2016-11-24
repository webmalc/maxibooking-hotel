#!/bin/bash
cd docker
docker-compose up -d
docker exec -it mbh-php-fpm /bin/bash