# =======================================================
# INFOSOF TECHNOLOGIES - Pharmacy Management Software
# Dockerfile for Render Web Service (PHP 8.2 + Apache)
# Supports MySQL 8.x / 5.x & Environment Configuration
# =======================================================

FROM php:8.2-apache

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libsqlite3-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mysqli \
        bcmath \
        gd \
        intl \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \${APACHE_DOCUMENT_ROOT}>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Create writable upload and cache directories with proper permissions
RUN mkdir -p /var/www/html/writable/cache \
    /var/www/html/writable/logs \
    /var/www/html/writable/session \
    /var/www/html/public/uploads/prescriptions \
    /var/www/html/public/uploads/documents \
    /var/www/html/public/uploads/receipts \
    /var/www/html/database \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/writable /var/www/html/public/uploads /var/www/html/database

# Copy entrypoint script and ensure it is executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose default port (Render will override via $PORT at runtime)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
