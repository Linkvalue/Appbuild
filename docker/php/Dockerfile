ARG PHP_VERSION=7.1
FROM php:${PHP_VERSION}-fpm-alpine

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
