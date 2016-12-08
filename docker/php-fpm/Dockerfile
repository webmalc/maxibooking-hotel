FROM phpdockerio/php71-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install phpunit php7.1-apcu php7.1-memcached php7.1-mongodb php7.1-sqlite3 php7.1-gd \
    php7.1-gmp php7.1-igbinary php7.1-imagick php7.1-imap php7.1-intl php7.1-mbstring php7.1-odbc\
    php7.1-phpdbg php7.1-soap php7.1-ssh2 php7.1-xdebug php7.1-xmlrpc php7.1-xsl\
    nodejs nodejs-legacy npm git && npm install -g bower && npm install -g uglifycss && npm install -g uglifyjs && npm install -g less\
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \

WORKDIR "/var/www/mbh"