FROM nginx:1.21.4-alpine

COPY docker/symfony/nginx/vhost.conf /etc/nginx/conf.d/default.conf

RUN \
    ln -snf /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && echo "Europe/Paris" > /etc/timezone

WORKDIR /app
