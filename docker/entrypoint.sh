#!/bin/sh
set -eu

mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage
chmod -R u+rwX,g+rX,o-rwx /var/www/html/storage

exec "$@"
