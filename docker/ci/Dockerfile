FROM ubuntu:22.04

COPY --from=composer:2.4.4 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_HOME=/composer
ENV COMPOSER_CACHE_DIR=/app/var/composer

# To use phpstan with differents PHP versions, like php7.4 "${PHPSTAN_BIN_PATH}" ...
ENV PHPSTAN_BIN_PATH=/usr/local/bin/phpstan

COPY docker/ci/composer.json ${COMPOSER_HOME}/composer.json

RUN \
    apt-get update \
    && apt-get upgrade -y \
    # To add add-apt-repository
    && apt-get install -y software-properties-common \
    && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
    && apt-get install -y \
        bash \
        shellcheck \
        php7.4-cli \
        php7.4-simplexml \
        php7.4-mbstring \
        php8.0-cli \
        php8.0-simplexml \
        php8.0-mbstring \
        php8.1-cli \
        php8.1-simplexml \
        php8.1-mbstring \
        # For Composer
        curl \
        zip \
        php7.4-curl \
        php7.4-zip \
        php8.0-curl \
        php8.0-zip \
        php8.1-curl \
        php8.1-zip \

    # For php scripts in bin who will be executed with PHP 8.0
    && php7.4 /usr/local/bin/composer global require steevanb/php-parallel-processes:0.2.* \

    # Install CI tools
    && php7.4 /usr/local/bin/composer global require \
        maglnet/composer-require-checker:3.5.* \
        wapmorgan/php-deprecation-detector:2.0.* \
        steevanb/php-code-sniffs:4.2.* \
        phpstan/phpstan:1.1.* \
        phpstan/phpstan-deprecation-rules:1.0.* \
        phpstan/phpstan-strict-rules:1.0. \
        phpstan/phpstan-phpunit:1.0.* \
        spaze/phpstan-disallowed-calls:2.0.* \
        ergebnis/phpstan-rules:1.0.* \
        insolita/unused-scanner:2.3.* \
        ergebnis/composer-normalize:2.15.* \
    && ln -s ${COMPOSER_HOME}/vendor/bin/composer-require-checker /usr/local/bin/composer-require-checker \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpdd /usr/local/bin/phpdd \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpcs /usr/local/bin/phpcs \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpstan /usr/local/bin/phpstan \
    && ln -s ${COMPOSER_HOME}/vendor/bin/unused_scanner /usr/local/bin/unused-scanner \

    # Purge
    && apt-get purge -y software-properties-common \
    && apt-get autoremove -y \
    && apt-get clean \
    && apt-get autoclean \
    && rm -rf /tmp/* \
    && rm -rf /composer/cache

WORKDIR /app
