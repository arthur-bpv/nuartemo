# NUARTE IFRN/MO

Site institucional e agenda do NUARTE, migrados para Laravel 13 com autenticação administrativa e ambiente Docker.

## Requisitos

- Docker com Docker Compose

## Instalação

1. Crie a configuração local:

   ```bash
   cp .env.example .env
   docker run --rm --entrypoint php composer:2 -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
   ```

   Copie o valor gerado para `APP_KEY` no arquivo `.env`.

2. Preencha no `.env` valores fortes e exclusivos para:

   - `DB_PASSWORD`
   - `DB_ROOT_PASSWORD`
   - `ADMIN_EMAIL`
   - `ADMIN_PASSWORD`

   Em produção com HTTPS, configure também `SESSION_SECURE_COOKIE=true`.

3. Construa e inicie os serviços:

   ```bash
   docker compose up -d --build
   ```

4. Crie ou atualize o administrador:

   ```bash
   docker compose exec app php artisan db:seed --class=AdminUserSeeder
   ```

O site ficará disponível em `http://localhost:8000` e o painel em `http://localhost:8000/admin/eventos`.

## Comandos úteis

```bash
docker compose logs -f app
docker compose exec app php artisan migrate --force
docker run --rm --user "$(id -u):$(id -g)" --entrypoint php -e HOME=/tmp -v "$PWD:/app" -w /app composer:2 artisan test
docker run --rm --user "$(id -u):$(id -g)" --entrypoint php -e HOME=/tmp -v "$PWD:/app" -w /app composer:2 vendor/bin/pint --test
```

## Segurança

As credenciais do sistema antigo foram removidas do código. Como elas continuam presentes no histórico Git, a senha antiga do banco deve ser invalidada no servidor antes da publicação desta versão.
