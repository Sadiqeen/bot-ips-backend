FROM serversideup/php:7.4-fpm-nginx

COPY . /var/www/html

RUN composer install --optimize-autoloader --no-dev

USER $PUID:$PGID

COPY --chown=$PUID:$PGID . /var/www/html

USER root:root

EXPOSE 80 443
