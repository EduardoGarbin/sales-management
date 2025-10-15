# Dockerfile de desenvolvimento para PHP-FPM
# Estende a imagem de produção e adiciona ferramentas de desenvolvimento

FROM php:8.3-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário não-root para evitar problemas de permissão
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} laravel && \
    useradd -u ${UID} -g laravel -m -s /bin/bash laravel

# Definir diretório de trabalho
WORKDIR /var/www/html

# Mudar para o usuário laravel
USER laravel

EXPOSE 9000
