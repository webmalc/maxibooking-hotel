FROM phpdockerio/php73-fpm:latest
WORKDIR "/var/www/mbh"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  \
    php-memcached php-memcache php-redis php-xdebug acl \
    php7.3-bcmath php7.3-bz2 php7.3-gd php-imagick php7.3-intl php-mongodb php-ssh2 php-yaml php-zmq \
    fish supervisor \
    curl -sL https://deb.nodesource.com/setup_9.x -o /tmp/nodesource_setup.sh && \
    bash /tmp/nodesource_setup.sh && \
    apt-get -y install gcc g++ make && \
    apt-get -y --no-install-recommends install nodejs build-essential && npm install -g bower && npm install -g uglifycss && npm install -g uglify-js && npm install -g less && \
    curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && sudo apt-get install yarn && \
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*