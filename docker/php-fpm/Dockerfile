FROM phpdockerio/php71-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install phpunit php7.1-mcrypt php7.1-apcu php7.1-bcmath php7.1-memcached php7.1-mongodb php7.1-sqlite3 php7.1-gd \
    php7.1-gmp php7.1-igbinary php7.1-imagick php7.1-imap php7.1-intl php7.1-mbstring php7.1-odbc\
    php7.1-phpdbg php7.1-soap php7.1-ssh2 php7.1-xdebug php7.1-xmlrpc php7.1-xsl\
    nodejs nodejs-legacy npm git supervisor && npm install -g bower && npm install -g uglifycss && npm install -g uglifyjs && npm install -g less ;\
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* ;\
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === 'c32408bcd017c577ce80605420e5987ce947a5609e8443dd72cd3867cc3a0cf442e5bf4edddbcbe72246a953a6c48e21') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/local/bin && mv /usr/local/bin/composer.phar /usr/local/bin/composer && \
    php -r "unlink('composer-setup.php');"

CMD ["/bin/bash", "/start.sh"]

WORKDIR "/var/www/mbh"