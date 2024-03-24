FROM php:8.1.10 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www
COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV PORT=8000
ENTRYPOINT ["docker/entrypoint.sh"]
