FROM composer:2.5.4 as composer_stage

WORKDIR /app

COPY composer.* ./

RUN composer install --ignore-platform-reqs --prefer-dist --no-scripts --no-progress --no-interaction --no-dev --no-autoloader

RUN composer dump-autoload --optimize --apcu --no-dev

FROM php:8.2.3-fpm-bullseye

WORKDIR /app

RUN docker-php-ext-install pdo pdo_mysql

# For signal handler
RUN docker-php-ext-install pcntl

COPY --from=composer_stage /app /app

COPY src/ /app/src

COPY index.php /app

ENTRYPOINT ["php", "/app/index.php"]
