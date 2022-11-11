FROM composer as vendor

WORKDIR /tmp/

COPY composer.json composer.json

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:alpine

# Source files
WORKDIR /opt/DVWS
COPY . .
COPY --from=vendor /tmp/vendor/ /opt/DVWS/vendor/

# Mysql Extension for php
RUN docker-php-ext-install mysqli pdo_mysql && docker-php-ext-enable mysqli

CMD ["./docker-entrypoint.sh"]

