# syntax=docker/dockerfile:1
FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Assets statiques (public/) servis directement par nginx ; le PHP est exécuté par php-fpm
# (conteneur `pea_php`). Le chemin doit être identique des deux côtés (/var/www/html/public)
# pour que SCRIPT_FILENAME résolve un fichier existant côté fpm.
COPY app/public /var/www/html/public

EXPOSE 80
