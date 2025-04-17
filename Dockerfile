FROM php:8.3-cli AS php

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libpq-dev \
    libcurl4-gnutls-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
COPY . .

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

ENV PORT=3000
ENTRYPOINT ["sh", "/var/www/Docker/entrypoint.sh"]