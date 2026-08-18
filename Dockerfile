# Use official PHP 8.3 FPM image
FROM php:8.3-fpm

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    nodejs \
    npm \
    && docker-php-ext-install intl zip gd pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

# Build frontend assets
RUN npm install && npm run build

# Fix permissions
RUN chown -R www-data:www-data \
    /app/storage \
    /app/bootstrap/cache

# Expose Render port (use dynamic PORT)
EXPOSE ${PORT}

# Start Laravel with migrations and storage symlink
CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT"]
