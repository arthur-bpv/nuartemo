#!/bin/sh
set -eu

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "Aguardando o MySQL..."
    until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' >/dev/null 2>&1; do
        sleep 2
    done
fi

php artisan migrate --force

exec "$@"
