#!/bin/sh
set -e

# give permission to write in www-data
chown -R www-data:www-data /var/www/html/.uploads || true
chmod -R 775 /var/www/html/.uploads || true

# launches apache
exec docker-php-entrypoint apache2-foreground