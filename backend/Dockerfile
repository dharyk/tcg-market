FROM php:8.3-fpm-alpine3.18

WORKDIR /app/tcg-market

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_DISABLE_XDEBUG_WARN=1
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

# Install dependencies
RUN apk add --update linux-headers
RUN apk add --no-cache --virtual .build-deps \            
    $PHPIZE_DEPS \
    tzdata \
    git \
    curl \
    curl-dev \
    freetype-dev \
    icu-dev \
    pcre-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libsodium-dev \
    libwebp-dev \
    libzip-dev

# Install php extension intl
RUN docker-php-ext-install intl
# Install php extension opcache
RUN docker-php-ext-install opcache
# Install php extension apcu
RUN pecl install apcu \
    && docker-php-ext-enable apcu
# Install php extension gd
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install gd
# Install php extension bcmath
RUN docker-php-ext-install bcmath
# Install php extension curl
RUN docker-php-ext-install curl
# Install php extension mysqli
RUN docker-php-ext-install mysqli
# Install php extension pdo
RUN docker-php-ext-install pdo
# Install php extension pdo_mysql
RUN docker-php-ext-install pdo_mysql
# Install php extension redis
RUN pecl install redis \
    && docker-php-ext-enable redis
# Install php extension mbstring
RUN apk add --no-cache oniguruma-dev \
    && docker-php-ext-install mbstring
# Install php extension Xdebug
RUN pecl install xdebug\
    && docker-php-ext-enable xdebug

# Setup local timezone to UTC
RUN set -xe \
    && cp /usr/share/zoneinfo/UTC /etc/localtime \
    && echo 'UTC' > /etc/localtime

RUN echo "memory_limit = 128M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_reporting=E_ERROR | E_WARNING | E_PARSE" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "expose_php = Off" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "ping.path = /ping" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "catch_workers_output = yes" >> /usr/local/etc/php-fpm.d/zz-docker.conf 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN git config --global user.email "miguel.guerreiro@gmail.com" \
    && git config --global user.name "Miguel Guerreiro"

EXPOSE 9000

CMD ["php-fpm"]