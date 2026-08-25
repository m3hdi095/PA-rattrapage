FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y libcurl4-openssl-dev pkg-config \
    && docker-php-ext-install curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

COPY . /var/www/html/

EXPOSE 80
