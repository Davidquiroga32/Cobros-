FROM webdevops/php-nginx:8.3

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 8080

CMD php artisan migrate --force && /opt/docker/bin/service.d/nginx.sh