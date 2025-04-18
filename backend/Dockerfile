FROM jflahaut/nginx-php:latest

# Copier les sources
WORKDIR /var/www/html

COPY app/ .

# Permissions
RUN sudo chown -R debian:debian /var/www/html \
    && sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN ls -alsh && composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 80

CMD ["/var/bin/startup.sh"]
