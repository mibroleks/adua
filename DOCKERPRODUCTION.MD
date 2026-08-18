# ============================================================
# ADUA UNIVERSITY ADMISSIONS PORTAL
# Production Dockerfile
#
# Stack:
# - Laravel
# - PHP 8.3
# - Vite
# - Tailwind CSS
# - PostgreSQL
# - Render
# ============================================================

# ------------------------------------------------------------
# Stage 1: Composer dependencies
# ------------------------------------------------------------
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ------------------------------------------------------------
# Stage 2: Frontend build
# ------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js ./ 2>/dev/null || true
COPY postcss.config.js ./ 2>/dev/null || true

RUN npm run build


# ------------------------------------------------------------
# Stage 3: Production PHP application
# ------------------------------------------------------------
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# ------------------------------------------------------------
# System dependencies
# ------------------------------------------------------------
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    nginx \
    supervisor \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        zip \
        gd


# ------------------------------------------------------------
# PHP production configuration
# ------------------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini


# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --from=composer /app/vendor ./vendor


# ------------------------------------------------------------
# Application source
# ------------------------------------------------------------
COPY . .


# ------------------------------------------------------------
# Frontend compiled assets
# ------------------------------------------------------------
COPY --from=frontend /app/public/build ./public/build


# ------------------------------------------------------------
# Laravel permissions
# ------------------------------------------------------------
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        public \
    && chmod -R ug+rwx \
        storage \
        bootstrap/cache


# ------------------------------------------------------------
# Nginx configuration
# ------------------------------------------------------------
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf


# ------------------------------------------------------------
# PHP-FPM configuration
# ------------------------------------------------------------
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf


# ------------------------------------------------------------
# Supervisor
# ------------------------------------------------------------
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf


# ------------------------------------------------------------
# Render uses PORT environment variable.
# Default is only a local fallback.
# ------------------------------------------------------------
ENV PORT=10000

EXPOSE 10000


# ------------------------------------------------------------
# Production startup
#
# IMPORTANT:
# No migrations.
# No seeders.
# No cache clearing.
# No development server.
# ------------------------------------------------------------
CMD ["sh", "-c", "sed -i \"s/__PORT__/${PORT}/g\" /etc/nginx/http.d/default.conf && nginx -t && php-fpm -t && exec supervisord -c /etc/supervisord.conf"]






Yes. For your Laravel + Vite + Tailwind application on Render, I would replace the current Dockerfile with a **production-oriented Dockerfile** rather than trying to patch the current one.

Your current Dockerfile has several production problems, especially:

* `php artisan serve` is a development server.
* `npm install` should use `npm ci` when you have a lockfile.
* migrations/seeding should **not** happen automatically on every container start.
* `config:clear`, `route:clear`, etc. on every startup defeat production caching.
* you are not explicitly configuring Laravel's trusted HTTPS/proxy behavior.
* the HTTPS issue is partly caused by Laravel believing the request is HTTP, which causes `asset()`/Vite URLs to become `http://...`.
* PHP-FPM is installed but you're not actually using PHP-FPM to serve the application.
* `db:seed --force` on every restart is dangerous.

### Production Dockerfile

Save this as your project's root:

```dockerfile
# ============================================================
# ADUA UNIVERSITY ADMISSIONS PORTAL
# Production Dockerfile
#
# Stack:
# - Laravel
# - PHP 8.3
# - Vite
# - Tailwind CSS
# - PostgreSQL
# - Render
# ============================================================

# ------------------------------------------------------------
# Stage 1: Composer dependencies
# ------------------------------------------------------------
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ------------------------------------------------------------
# Stage 2: Frontend build
# ------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js ./ 2>/dev/null || true
COPY postcss.config.js ./ 2>/dev/null || true

RUN npm run build


# ------------------------------------------------------------
# Stage 3: Production PHP application
# ------------------------------------------------------------
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# ------------------------------------------------------------
# System dependencies
# ------------------------------------------------------------
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    nginx \
    supervisor \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        zip \
        gd


# ------------------------------------------------------------
# PHP production configuration
# ------------------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini


# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --from=composer /app/vendor ./vendor


# ------------------------------------------------------------
# Application source
# ------------------------------------------------------------
COPY . .


# ------------------------------------------------------------
# Frontend compiled assets
# ------------------------------------------------------------
COPY --from=frontend /app/public/build ./public/build


# ------------------------------------------------------------
# Laravel permissions
# ------------------------------------------------------------
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        public \
    && chmod -R ug+rwx \
        storage \
        bootstrap/cache


# ------------------------------------------------------------
# Nginx configuration
# ------------------------------------------------------------
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf


# ------------------------------------------------------------
# PHP-FPM configuration
# ------------------------------------------------------------
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf


# ------------------------------------------------------------
# Supervisor
# ------------------------------------------------------------
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf


# ------------------------------------------------------------
# Render uses PORT environment variable.
# Default is only a local fallback.
# ------------------------------------------------------------
ENV PORT=10000

EXPOSE 10000


# ------------------------------------------------------------
# Production startup
#
# IMPORTANT:
# No migrations.
# No seeders.
# No cache clearing.
# No development server.
# ------------------------------------------------------------
CMD ["sh", "-c", "sed -i \"s/__PORT__/${PORT}/g\" /etc/nginx/http.d/default.conf && nginx -t && php-fpm -t && exec supervisord -c /etc/supervisord.conf"]
```

### Important

Because this Dockerfile references a few production configuration files, create these too.

---

## 1. `docker/nginx/default.conf`

```nginx
server {
    listen __PORT__;
    listen [::]:__PORT__;

    server_name _;

    root /var/www/html/public;

    index index.php index.html;

    charset utf-8;

    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~ \.php$ {
        try_files $uri =404;

        include fastcgi_params;

        fastcgi_pass 127.0.0.1:9000;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        fastcgi_param HTTP_PROXY "";

        fastcgi_param HTTPS on;
        fastcgi_param SERVER_PORT 443;

        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|svg|ico|webp|woff|woff2|ttf)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";

        try_files $uri =404;
    }
}
```

---

## 2. `docker/php/www.conf`

```ini
[www]

user = www-data
group = www-data

listen = 127.0.0.1:9000

listen.owner = www-data
listen.group = www-data

pm = dynamic

pm.max_children = 20
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 5

clear_env = no

catch_workers_output = yes

decorate_workers_output = no

php_admin_value[log_errors] = 1
php_admin_value[error_log] = /proc/self/fd/2
```

---

## 3. `docker/php/opcache.ini`

```ini
opcache.enable=1
opcache.enable_cli=1

opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000

opcache.validate_timestamps=0
opcache.revalidate_freq=0

opcache.save_comments=1
opcache.fast_shutdown=1
```

The important production setting here is:

```ini
opcache.validate_timestamps=0
```

because your deployment creates a new container when code changes.

---

## 4. `docker/supervisor/supervisord.conf`

```ini
[supervisord]
nodaemon=true
user=root

logfile=/dev/null
pidfile=/tmp/supervisord.pid

[program:php-fpm]
command=php-fpm --nodaemonize

priority=10
autostart=true
autorestart=true

stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0

stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0


[program:nginx]
command=nginx -g "daemon off;"

priority=20
autostart=true
autorestart=true

stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0

stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

---

# But we also need to fix your HTTPS issue

The Dockerfile alone is **not enough**.

Your current error:

```text
Mixed Content:
https://adua.onrender.com/
requested:
http://adua.onrender.com/build/assets/app-....css
```

is a strong indication that Laravel is generating URLs using `http`.

Your layout:

```blade
@vite([
    'resources/css/app.css',
    'resources/js/app.js'
])
```

is fine.

The generated Vite asset should be:

```text
https://adua.onrender.com/build/assets/app-xxxxx.css
```

not:

```text
http://adua.onrender.com/build/assets/app-xxxxx.css
```

So we should fix Laravel's HTTPS/proxy awareness.

---

# 5. Update `bootstrap/app.php`

Your current file has:

```php
->withMiddleware(function (Middleware $middleware): void {
    //
})
```

Change it to:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
         * Render terminates HTTPS at its proxy/load balancer.
         *
         * The application container receives the proxied request,
         * so Laravel must trust the proxy headers in order to correctly
         * determine that the original request was HTTPS.
         */
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

    })
    ->create();
```

This is important for your Render deployment.

---

# 6. Add this to Render Environment

In your Render environment variables, add:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://adua.onrender.com
```

Also make sure you have your normal:

```text
APP_KEY=...
DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Your production application should **never** have:

```text
APP_DEBUG=true
```

---

# 7. `config/app.php`

If your Laravel version/project has `config/app.php`, make sure the URL isn't being hardcoded to HTTP anywhere.

You should ultimately have:

```php
'url' => env('APP_URL', 'http://localhost'),
```

not:

```php
'url' => 'http://adua.onrender.com',
```

---

# 8. Your ThemeController is actually fine

This:

```php
return response($css, 200)
    ->header('Content-Type', 'text/css')
    ->header('Cache-Control', 'public, max-age=300')
    ->header('X-Theme-Preset', $theme->preset())
    ->header('X-Theme-Mode', $theme->mode());
```

is not the source of the problem.

And:

```blade
<link rel="stylesheet" href="{{ route('theme.css') }}">
```

is also conceptually correct.

Once Laravel recognizes the original request as HTTPS, this should become:

```html
<link rel="stylesheet" href="https://adua.onrender.com/theme.css">
```

instead of:

```html
<link rel="stylesheet" href="http://adua.onrender.com/theme.css">
```

---

# One more important production correction

Do **not** keep this in your Docker startup:

```dockerfile
php artisan migrate --force && php artisan db:seed --force
```

That is particularly dangerous:

```text
Container starts
       ↓
migrate
       ↓
seed database
       ↓
application starts
```

Render can restart your service for reasons unrelated to deployment.

You don't want a restart to automatically execute your seeders.

Instead:

### Deployment

```text
Docker build
    ↓
Composer
    ↓
npm build
    ↓
Container created
    ↓
Nginx + PHP-FPM
    ↓
Application
```

### Database migration

Run migrations deliberately during deployment, preferably as a separate Render job/process.

For your current MVP, you can manually run:

```bash
php artisan migrate --force
```

from the Render Shell when needed.

Do **not** automatically run:

```bash
php artisan db:seed --force
```

in production.

---

# Also add `.dockerignore`

Create:

```text
.dockerignore
```

with:

```text
.git
.gitignore

.env
.env.*
!.env.example

node_modules

vendor

storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*

bootstrap/cache/*.php

docker-compose.yml
docker-compose.*.yml

npm-debug.log*
yarn-debug.log*
yarn-error.log*

.idea
.vscode

.DS_Store
Thumbs.db
```

This keeps secrets and unnecessary development files out of the Docker build context.

---

# Your final structure

I would keep this structure:

```text
aduamain/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   │
│   ├── php/
│   │   ├── opcache.ini
│   │   └── www.conf
│   │
│   └── supervisor/
│       └── supervisord.conf
│
├── .dockerignore
├── Dockerfile
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
└── vite.config.js
```

### And the production flow becomes

```text
GitHub
   │
   ▼
Render
   │
   ├── Docker build
   │      │
   │      ├── Composer dependencies
   │      ├── npm ci
   │      └── npm run build
   │
   ▼
Production container
   │
   ├── Nginx
   │       │
   │       ▼
   │   public/
   │
   └── PHP-FPM
           │
           ▼
        Laravel
           │
           ▼
       PostgreSQL
```

And most importantly:

```text
Browser
   │
   │ HTTPS
   ▼
Render HTTPS Proxy
   │
   │ X-Forwarded-Proto: https
   ▼
Nginx
   │
   ▼
Laravel
   │
   ├── Vite assets → HTTPS
   ├── route()      → HTTPS
   ├── asset()      → HTTPS
   └── theme.css    → HTTPS
```

That is the production foundation I would save as the baseline for this project.
