FROM php:8.4-fpm-alpine AS base

LABEL org.opencontainers.image.source=https://github.com/JAGFx/pyxis
LABEL org.opencontainers.image.licenses=MIT

COPY docker/php/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql opcache intl

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del $PHPIZE_DEPS \
    && pecl clear-cache \
    && apk del .build-dependencies

RUN rm -rf /var/cache/apk/* \
    && rm -rf /tmp/pear

COPY docker/php/conf.d/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
EXPOSE 9000

FROM base AS dev

RUN apk add --update linux-headers \
    && apk add --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl clear-cache \
    && apk del .build-dependencies \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/pear
COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/local/bin/php-cs-fixer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/symfony

FROM base AS prod

RUN addgroup -g 1000 symfony \
    && adduser -u 1000 -G symfony -s /bin/sh -D symfony

COPY --chown=symfony:symfony . /var/www/symfony
RUN chmod -R 755 /var/www/symfony/var/ \
    && chown -R symfony:symfony /var/www/symfony/var/ \
    && rm -rf /var/www/symfony/var/cache/dev \
    && rm -rf /var/www/symfony/var/cache/test \
    && rm -rf /var/www/symfony/var/log/* \
    && mkdir -p /var/www/symfony/var/log \
    && chown -R symfony:symfony /var/www/symfony/var/

RUN rm -f /usr/local/etc/php/conf.d/error_reporting.ini
USER symfony

WORKDIR /var/www/symfony