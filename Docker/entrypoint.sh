#!/bin/bash

set -e

composer install 
cp .env.example .env

php artisan key:generate
php artisan jwt:secret --force

php artisan migrate --seed --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan l5-swagger:generate
php artisan test

php artisan serve --host=0.0.0.0 --port=$PORT