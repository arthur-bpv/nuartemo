#!/bin/sh
set -eu

if [ "${APP_ENV:-}" = "production" ] && [ -z "${APP_KEY:-}" ]; then
    echo "Defina APP_KEY no ambiente de produção." >&2
    exit 1
fi

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "Aguardando o MySQL..."
    attempts=0
    until php -r '
        $dsn = "mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE");
        new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_TIMEOUT => 2]);
    ' >/dev/null 2>&1; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge 15 ]; then
            echo "Não foi possível conectar ao MySQL. Confira a rede e as variáveis DB_*." >&2
            exit 1
        fi
        sleep 2
    done
fi

php artisan migrate --force --no-interaction

exec "$@"
