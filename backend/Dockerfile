FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git curl unzip libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev libicu-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier les sources
WORKDIR /var/www/html
COPY app/ .

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["php-fpm"]