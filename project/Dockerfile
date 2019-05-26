FROM php:7.3.5-apache
RUN apt-get update && apt-get upgrade -y
RUN docker-php-ext-install mysqli

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get install curl \
  && curl -s https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip
EXPOSE 80
