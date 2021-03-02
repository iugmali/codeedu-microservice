#!/bin/bash

chown -R www-data:www-data .

npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install

cd /var/www/backend
if [ ! -f ".env" ]; then
    cp .env.example .env
fi
if [ ! -f ".env.testing" ]; then
    cp .env.testing.example .env.testing
fi
composer install
php artisan key:generate
php artisan migrate

php-fpm
