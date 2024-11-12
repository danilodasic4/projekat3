FROM php:8.1.0-fpm-alpine

MAINTAINER Miljan Ristic <rista.le@gmail.com>

# APK Applications & Libraries
RUN apk add --no-cache acl \
                       fcgi \
                       file \
                       gettext \
                       git \
                       icu-dev \
                       libzip-dev \
                       zlib-dev \
                       supervisor \
                       mediainfo;

# PHP Extensions
RUN docker-php-ext-install bcmath \
		                   intl \
		                   opcache \
		                   pdo_mysql \
		                   zip ;

# Docker PHP Extensions Installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions amqp \
		                   apcu \
		                   redis;

# PHP configuration
RUN ln -s $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

# Add Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /var/www/
