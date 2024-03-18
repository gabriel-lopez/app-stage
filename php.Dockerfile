FROM php:8.2-cli

RUN apt-get update && apt-get install -y
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli