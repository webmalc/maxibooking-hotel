FROM phpdockerio/php7-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install phpunit php7.0-apcu php7.0-memcached php7.0-mongodb php7.0-sqlite3 php7.0-gd \
    php7.0-gmp php7.0-igbinary php7.0-imagick php7.0-imap php7.0-intl php7.0-mbstring php7.0-odbc\
    php7.0-phpdbg php7.0-soap php7.0-ssh2 php7.0-xdebug php7.0-xmlrpc php7.0-xsl\
    nodejs nodejs-legacy npm && npm install -g uglifycss && npm install -g uglifyjs && npm install -g less\
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \



WORKDIR "/var/www/mbh"