FROM php:8.3-fpm-alpine

# Instala dependências do sistema necessárias para extensões PHP e ferramentas
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Instala extensões PHP necessárias para o projeto
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        xml \
        zip

# Instala Redis via PECL (necessário para cache e filas do Horizon)
RUN pecl install redis && docker-php-ext-enable redis

# Instala Composer globalmente
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configura diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do projeto com permissões adequadas
COPY --chown=www-data:www-data . .

# Instala dependências PHP sem scripts de pós-instalação (evita falha sem .env)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Ajusta permissões dos diretórios de escrita do Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
