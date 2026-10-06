FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    libonig-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/nuartemo-entrypoint
RUN chmod +x /usr/local/bin/nuartemo-entrypoint

WORKDIR /var/www/html
ENTRYPOINT ["/usr/local/bin/nuartemo-entrypoint"]
CMD ["apache2-foreground"]

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 CMD curl -fsS http://localhost/health.php || exit 1
