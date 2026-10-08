#!/bin/bash
set -e

# Render provides $PORT dynamically (default 10000 on Render, fallback 8080)
PORT="${PORT:-8080}"

echo "Configuring Apache to listen on port ${PORT}..."

# Dynamically bind Apache to the assigned Render port
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure writable directories and permissions exist for www-data
mkdir -p /var/www/html/writable/cache \
         /var/www/html/writable/logs \
         /var/www/html/writable/session \
         /var/www/html/public/uploads/documents \
         /var/www/html/public/uploads/prescriptions \
         /var/www/html/public/uploads/receipts \
         /var/www/html/database

chown -R www-data:www-data /var/www/html/writable /var/www/html/public/uploads /var/www/html/database
chmod -R 775 /var/www/html/writable /var/www/html/public/uploads /var/www/html/database

echo "Starting Apache foreground process..."
exec apache2-foreground
