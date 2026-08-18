# Use official PHP 8.3 FPM image
FROM php:8.3-fpm

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpng-dev \
    && docker-php-ext-install intl zip gd pdo pdo_mysql pdo_pgsql

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Clear Laravel caches to avoid old configs
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Run migrations and seeders automatically
RUN php artisan migrate --force && \
    php artisan db:seed --force

# Expose port
EXPOSE 10000

# Start Laravel server (JSON format CMD)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
