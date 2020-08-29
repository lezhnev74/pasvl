FROM php:7.1

WORKDIR /var/www

# Install dependencies
RUN apt-get update

RUN apt-get install -y zip unzip libzip-dev libonig-dev

# Install extensions
RUN docker-php-ext-install mbstring bcmath zip

# XDEBUG
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
# This needs in order to run xdebug from PhpStorm
ENV PHP_IDE_CONFIG 'serverName=DockerApp'

ADD php.ini /usr/local/etc/php

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e5325b19b381bfd88ce90a5ddb7823406b2a38cff6bb704b0acc289a09c8128d4a8ce2bbafcd1fcbdc38666422fe2806') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer