FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install
RUN npm run build
RUN php artisan optimize:clear

EXPOSE 8080
RUN php artisan optimize:clear

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"]