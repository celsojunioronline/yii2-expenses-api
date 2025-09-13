FROM php:8.3-fpm
LABEL authors="Celso Junior"

#Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev libpng-dev libonig-dev \
        && docker-php-ext-install pdo_mysql intl zip gd mbstring bcmath

#Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

#Instalar Task
RUN curl -sL https://taskfile.dev/install.sh | sh \
    && mv ./bin/task /usr/local/bin/task

#Definir pasta de trabalho
WORKDIR /var/www/html
