ARG PHP_EXTENSIONS="pdo_mysql pdo_sqlite"
FROM thecodingmachine/php:8.4-v5-slim-apache AS php
ENV TEMPLATE_PHP_INI=production \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

ENV COMPOSER_ALLOW_SUPERUSER 1

ADD --chown=docker:docker . /var/www/html

WORKDIR /var/www/html

### PHP Development
FROM php AS php-dev

RUN composer install

### PHP Production
FROM php AS php-prod

RUN composer install --quiet --optimize-autoloader
ENV OPCACHE_ENABLE=1 \
    OPCACHE_MEMORY_CONSUMPTION=128 \
    OPCACHE_STRINGS_BUFFER=8 \
    OPCACHE_MAX_FILES=10000 \
    OPCACHE_VALIDATE_TIMESTAMPS=0 \
    OPCACHE_REVALIDATE_FREQ=2 \
    OPCACHE_SAVE_COMMENTS=0

WORKDIR /var/www/html
