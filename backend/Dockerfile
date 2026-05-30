# syntax=docker/dockerfile:1

# Binaire composer (le phar est indépendant du PHP qui l'exécute ; on l'exécute sous
# PHP 8.2 ci-dessous pour respecter les platform reqs du composer.lock).
FROM composer:2 AS composer

# ============ base runtime : php-fpm officiel + extensions ============
FROM php:8.2-fpm-alpine AS base
# install-php-extensions : helper posé sur la base OFFICIELLE, gère les deps système
# des extensions (icu, libzip…) sans bricoler les -dev alpine à la main.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_sqlite bcmath intl zip opcache pcntl
WORKDIR /var/www/html

# ============ deps composer (prod, sans dev) — sous PHP 8.2 + extensions ============
FROM base AS vendor
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY app/ .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ============ image de PROD : app + vendor bakés, immuable, AUCUN mount ============
FROM base AS prod
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY --chown=www-data:www-data app/ .
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# ============ image de DEV : TOOLCHAIN seule ============
# Ni app ni vendor : en dev, le code ET le vendor viennent du bind-mount (docker-compose.dev.yml).
# Cette image n'apporte que le runtime PHP + extensions + Xdebug + composer. Cf. `make init`
# pour le premier `composer install` dans le volume monté.
FROM base AS dev
# UID/GID de l'hôte : on aligne www-data dessus pour que les workers fpm puissent écrire
# le `storage` monté en bind-mount (sinon "Permission denied" -> 500). fpm garde son
# master root et fait tourner les workers en www-data (désormais = uid hôte).
ARG UID=1000
ARG GID=1000
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
# Xdebug installé mais OFF par défaut ; activé via XDEBUG_MODE (voir docker-compose.dev.yml).
# start_with_request=trigger => ne tente le debugger QUE sur trigger (pas à chaque requête).
RUN install-php-extensions xdebug \
 && printf "xdebug.mode=off\nxdebug.start_with_request=trigger\nxdebug.client_host=host.docker.internal\n" \
      > /usr/local/etc/php/conf.d/zz-xdebug.ini \
 && apk add --no-cache shadow \
 && usermod -u ${UID} www-data \
 && groupmod -g ${GID} www-data
EXPOSE 9000
CMD ["php-fpm"]
