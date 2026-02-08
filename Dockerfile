FROM php:8.4-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        mariadb-client \
        unzip \
        libcurl4-openssl-dev \
        libxml2-dev \
        libzip-dev \
        libc-client-dev \
        libkrb5-dev \
        libssl-dev \
    && docker-php-ext-install \
        curl \
        intl \
        opcache \
        pdo_mysql \
        xml \
        zip \
    && pecl install imap \
    && docker-php-ext-enable imap \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-casilium.ini
COPY docker/entrypoint.sh /usr/local/bin/casilium-entrypoint

RUN chmod +x /usr/local/bin/casilium-entrypoint

RUN chmod -R a+rx /var/www/html/vendor/bin \
    && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["casilium-entrypoint"]
CMD ["apache2-foreground"]
