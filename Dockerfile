FROM php:7.4-apache

# mod_rewrite improves SEO by creating cleaner URLs
RUN a2enmod rewrite

# dev settings with logs
RUN { \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
} > /usr/local/etc/php/conf.d/dev.ini

# curl (for healthcheck) + Postgres extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# entrypoint
COPY entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
