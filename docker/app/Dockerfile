# Lego images. The last stage (app) is the production image: PHP-FPM with the
# code and production dependencies inside, so Dokploy, the home server and CI
# run exactly the same thing. The `test` stage adds Composer for the test suite
# (see compose.test.yml).

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM php:8.3-fpm-alpine AS php-base
RUN apk add --no-cache libpq \
    && apk add --no-cache --virtual .build-deps postgresql-dev \
    && docker-php-ext-install pdo_pgsql opcache \
    && apk del .build-deps

FROM php-base AS test
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app

FROM php-base AS app
COPY docker/app/php.ini /usr/local/etc/php/conf.d/zz-lego.ini
COPY docker/app/fpm.conf /usr/local/etc/php-fpm.d/zz-lego.conf
COPY --chmod=0755 docker/app/entrypoint.sh /usr/local/bin/lego-entrypoint

WORKDIR /app
COPY --from=vendor --chown=www-data:www-data /app /app

USER www-data
EXPOSE 9000

ENTRYPOINT ["lego-entrypoint"]
CMD ["php-fpm"]
