FROM php:8.2-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

WORKDIR /var/www/src

# Copy project Lumen dari host ke container
COPY . .

# Install dependencies
RUN composer install --ignore-platform-req=ext-mongodb

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
