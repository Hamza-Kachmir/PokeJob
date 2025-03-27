FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql && a2enmod rewrite
COPY ../public/ /var/www/html/
COPY ../app/    /var/www/app/
WORKDIR /var/www/html
