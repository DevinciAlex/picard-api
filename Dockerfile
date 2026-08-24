FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libpq-dev \
    && docker-php-ext-install mbstring pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN mkdir -p config/jwt var/cache var/log \
    && chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data var config/jwt

EXPOSE 80

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
