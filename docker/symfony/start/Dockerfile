FROM php:8.0.13-fpm-buster

COPY --from=composer:2.1.12 /usr/bin/composer /usr/local/bin/composer

ARG USER_APP_ID
ARG GROUP_APP_ID
ARG GROUP_DOCKER_ID

RUN \
    apt-get update \
    && apt-get install -y zip git \
    && docker-php-ext-install opcache \

    # Fix locale
    && echo "date.timezone = Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini \
    && ln -snf /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && echo "Europe/Paris" > /etc/timezone \

    # Create user app and add him in group docker
    && addgroup --gid ${GROUP_DOCKER_ID} docker \
    && addgroup --gid ${GROUP_APP_ID} app \
    && adduser --uid ${USER_APP_ID} --gid ${GROUP_APP_ID} app \
    && adduser app docker

ENV I_AM_A_DOCKER_CONTAINER=true

WORKDIR /app
