FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

RUN install-php-extensions pdo_pgsql pdo_mysql zip gd intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan storage:link || true

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
