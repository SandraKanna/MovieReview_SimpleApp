#!/bin/sh
set -e

# give permission to write in www-data
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads

# launches apache
exec docker-php-entrypoint apache2-foreground