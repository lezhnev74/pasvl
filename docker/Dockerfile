FROM php:7.4

WORKDIR /var/www

# Install dependencies
RUN apt-get update

RUN apt-get install -y zip unzip libzip-dev libonig-dev

# Install extensions
RUN docker-php-ext-install mbstring bcmath zip

# AST extension
RUN pecl install ast
RUN docker-php-ext-enable ast

# XDEBUG
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
# This needs in order to run xdebug from PhpStorm
ENV PHP_IDE_CONFIG 'serverName=DockerApp'

ADD php.ini /usr/local/etc/php

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer