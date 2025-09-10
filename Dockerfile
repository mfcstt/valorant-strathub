# Use PHP 8.1 com Apache
FROM php:8.1-apache

# Atualiza pacotes e instala extensões necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar DocumentRoot para a pasta public
WORKDIR /var/www/html

# Copiar todo o projeto
COPY . /var/www/html/

# Ajustar Apache para usar /public como root
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# Ajustar permissões de forma segura
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Expor porta 80
EXPOSE 80
