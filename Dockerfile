# Dependency container

FROM serversideup/php:7.4-cli AS dependencies

WORKDIR /app

COPY composer.json composer.lock /app/

RUN composer install --optimize-autoloader --no-dev

# Production container

FROM serversideup/php:7.4-fpm-nginx

COPY --from=dependencies /app /var/www/html

COPY . /var/www/html

EXPOSE 80 443
