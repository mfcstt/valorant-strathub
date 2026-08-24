# =============================================================================
# Valorant StratHub — imagem de produção
#
# A imagem anterior rodava `php -S` (servidor embutido, single-thread, que a
# própria documentação do PHP marca como inadequado para produção) e fazia
# `composer install || true`, engolindo silenciosamente uma falha de instalação.
#
# Build em dois estágios: as dependências são resolvidas com o Composer no
# primeiro estágio e apenas o resultado vai para a imagem final.
# =============================================================================

# --- Estágio 1: dependências -------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./

# Sem --no-dev implícito: a ausência de `|| true` é intencional, uma falha aqui
# deve quebrar o build em vez de gerar uma imagem sem dependências.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# --- Estágio 2: assets -------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY tailwind.config.mjs ./
COPY resources ./resources
COPY src ./src
COPY public ./public
COPY scripts ./scripts
RUN npm run build

# --- Estágio 3: runtime ------------------------------------------------------
FROM php:8.3-apache AS runtime

# libpq para o driver do PostgreSQL; o resto sai da imagem para não carregar
# compiladores no runtime.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# O DocumentRoot aponta para public/: nada fora dessa pasta é acessível pela web,
# então .env, src/ e vendor/ não podem ser baixados nem se o PHP parar de
# interpretar arquivos.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

COPY docker/php.ini /usr/local/etc/php/conf.d/strathub.ini
COPY docker/apache-strathub.conf /etc/apache2/conf-available/strathub.conf
RUN a2enconf strathub

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/CSS/tailwind.build.css ./public/CSS/tailwind.build.css
COPY --from=assets --chown=www-data:www-data /app/public/vendor ./public/vendor

# O .env nunca entra na imagem: a configuração vem de variáveis de ambiente do
# host (Render, Vercel, Fly). O .dockerignore também o exclui.
RUN rm -f .env

ENV APP_ENV=production \
    APP_DEBUG=false \
    USE_SQLITE=false

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/login") === false ? 1 : 0);'
