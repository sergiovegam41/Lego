# Lego application image: PHP-FPM with the code and production dependencies inside.
# Self-contained so Dokploy, the home server and CI run exactly the same image.

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM php:8.3-fpm-alpine

RUN apk add --no-cache libpq \
    && apk add --no-cache --virtual .build-deps postgresql-dev \
    && docker-php-ext-install pdo_pgsql opcache \
    && apk del .build-deps

COPY docker/app/php.ini /usr/local/etc/php/conf.d/zz-lego.ini
COPY docker/app/fpm.conf /usr/local/etc/php-fpm.d/zz-lego.conf
COPY --chmod=0755 docker/app/entrypoint.sh /usr/local/bin/lego-entrypoint

WORKDIR /app
COPY --from=vendor --chown=www-data:www-data /app /app

USER www-data
EXPOSE 9000

ENTRYPOINT ["lego-entrypoint"]
CMD ["php-fpm"]
