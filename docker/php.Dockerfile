# PHP 8.3 + Apache image with the MySQL PDO driver and URL rewriting enabled.
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
	&& a2enmod rewrite

# Project is mounted at /var/www/html by docker-compose.
EXPOSE 80
