FROM nginx:1.21-alpine AS base

COPY docker/nginx/nginx.conf /etc/nginx/
COPY docker/nginx/templates /etc/nginx/templates/
RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

EXPOSE 80

FROM base AS prod

RUN addgroup -g 1000 symfony \
    && adduser -u 1000 -G symfony -s /bin/sh -D symfony

COPY --chown=symfony:symfony . /var/www/symfony
USER symfony

WORKDIR /var/www/symfony