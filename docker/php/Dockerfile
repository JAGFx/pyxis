FROM dunglas/frankenphp:php8.4-alpine AS base

LABEL org.opencontainers.image.source=https://github.com/JAGFx/pyxis
LABEL org.opencontainers.image.licenses=MIT

COPY docker/php/conf.d/override.ini /usr/local/etc/php/conf.d/override.ini

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && install-php-extensions opentelemetry protobuf opcache pdo pdo_mysql intl zip apcu

# Fatest way than install via install-php-extensions
RUN apk add --no-cache git grpc-cpp grpc-dev \
    && GRPC_VERSION=$(apk info grpc -d | grep grpc | cut -d- -f2) \
    && git clone --depth 1 -b v${GRPC_VERSION} https://github.com/grpc/grpc /tmp/grpc \
    && cd /tmp/grpc/src/php/ext/grpc \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable grpc \
    && rm -rf /tmp/grpc

RUN apk del grpc-dev git $PHPIZE_DEPS .build-dependencies  \
    && rm -rf /var/cache/apk/*

FROM base AS dev

RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/error_reporting.dev.ini $PHP_INI_DIR/error_reporting.ini
COPY docker/php/conf.d/opcache.dev.ini $PHP_INI_DIR/opcache.ini
COPY docker/php/conf.d/xdebug.ini $PHP_INI_DIR/docker-php-ext-xdebug.ini

RUN apk add --update linux-headers \
    && install-php-extensions xdebug \
    && rm -rf /var/cache/apk/*

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/local/bin/php-cs-fixer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/symfony

FROM base AS prod

ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV APP_RUNTIME="Runtime\\FrankenPhpSymfony\\Runtime"

ARG USER=symfony

RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/error_reporting.prod.ini $PHP_INI_DIR/error_reporting.ini
COPY docker/php/conf.d/opcache.prod.ini $PHP_INI_DIR/opcache.ini

RUN adduser -D ${USER} \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R ${USER}:${USER} /config/caddy /data/caddy

COPY --chown=${USER}:${USER} . /var/www/symfony
COPY --chown=${USER}:${USER} docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chmod -R 755 /var/www/symfony/var/ \
    && chown -R ${USER}:${USER} /var/www/symfony/var/ \
    && rm -rf /var/www/symfony/var/cache/dev \
    && rm -rf /var/www/symfony/var/cache/test \
    && rm -rf /var/www/symfony/var/log/* \
    && mkdir -p /var/www/symfony/var/log

USER ${USER}

WORKDIR /var/www/symfony

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]