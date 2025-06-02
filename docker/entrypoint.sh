#!/bin/sh
set -e

# Создаем необходимые директории если их нет
mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/bootstrap/cache

# Устанавливаем права доступа
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Запускаем PHP-FPM
exec php-fpm

