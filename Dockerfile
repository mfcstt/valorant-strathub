FROM php:8.1-apache

# Atualiza pacotes e instala dependências
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Habilitar mod_rewrite (rotas bonitas /index.php)
RUN a2enmod rewrite

# Copiar projeto para dentro do container
COPY . /var/www/html/

# Definir pasta de trabalho
WORKDIR /var/www/html

# Definir permissões para cache, storage e uploads (se necessário)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor porta do Apache
EXPOSE 80
