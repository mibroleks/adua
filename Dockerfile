# Use official PHP 8.3 FPM image
FROM php:8.3-fpm

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpng-dev libpq-dev \
    && docker-php-ext-install intl zip gd pdo pdo_pgsql

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies (skip artisan scripts during build)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts

# Expose port
EXPOSE 10000

# Run migrations and seeders automatically at startup
CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=10000"]
