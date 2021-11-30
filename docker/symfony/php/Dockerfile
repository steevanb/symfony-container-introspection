FROM php:8.0.13-fpm-buster

COPY --from=composer:2.1.12 /usr/bin/composer /usr/local/bin/composer

RUN \
    apt-get update \
    && apt-get install -y zip git \
    && docker-php-ext-install opcache \
    # Fix locale
    && echo "date.timezone = Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini \
    && ln -snf /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && echo "Europe/Paris" > /etc/timezone

ENV I_AM_A_DOCKER_CONTAINER=true

WORKDIR /app
