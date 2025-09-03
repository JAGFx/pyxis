FROM nginx:1.21-alpine AS base

LABEL org.opencontainers.image.source=https://github.com/JAGFx/pyxis
LABEL org.opencontainers.image.licenses=MIT

COPY docker/nginx/nginx.conf /etc/nginx/
COPY docker/nginx/templates /etc/nginx/templates/
RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

EXPOSE 80

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

WORKDIR /var/www/symfony