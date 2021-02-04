FROM composer:2 AS composer
FROM php:8.0-cli-alpine

RUN apk update
RUN docker-php-ext-install sockets
RUN apk add libsodium-dev && docker-php-ext-install sodium
RUN docker-php-ext-install bcmath

RUN apk add git
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /usr/src/mt-wg-auto
WORKDIR /usr/src/mt-wg-auto
RUN php /usr/bin/composer install -o -a -n

ENTRYPOINT ["php", "./bin/console"]
