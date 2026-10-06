# NuArteMO

Site institucional com agenda administrativa em PHP 8.2 e MariaDB. O módulo dinâmico está organizado em MVC sem framework, mantendo as URLs legadas.

O código público fica exclusivamente em `public/`; configure sempre o `DocumentRoot` do servidor para esse diretório.

## Documentação

- [Arquitetura atual](docs/architecture.md)
- [Inventário de rotas](docs/routes.md)
- [Auditoria de limpeza](docs/cleanup.md)
- [Planejamento incremental](docs/plan.md)

## Ambiente local

1. Copie `.env.example` para `.env` e escolha senhas locais exclusivas.
2. Execute `docker compose up --build`.
3. Acesse `http://localhost:8080`.
4. Para o phpMyAdmin local, use `docker compose --profile tools up -d` e acesse `http://127.0.0.1:8081`.

O schema de desenvolvimento fica em `database/init/01_schema.sql`. Ele não cria usuário administrador: cadastre um usuário de teste com senha gerada por `password_hash()` antes de testar o login.

## Verificações

```bash
docker compose exec web php tests/run.php
docker compose exec web php -m
docker compose exec web apache2ctl -M
```

`/diagnostico.php` exige sessão administrativa e só responde quando `APP_ENV=development` e `DIAGNOSTICS_ENABLED=true`.

## Deploy

A Action SFTP está em `.github/workflows/deploy.yml`. Configure os secrets `SFTP_SERVER`, `SFTP_USER`, `SFTP_PASSWORD` e `SFTP_TARGET_DIR`. O destino é a raiz da aplicação no servidor, e o `DocumentRoot` da hospedagem deve apontar para `SFTP_TARGET_DIR/public`.
