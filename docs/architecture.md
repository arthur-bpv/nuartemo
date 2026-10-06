# Arquitetura atual

Este documento descreve somente o sistema que existe hoje. Ele não antecipa entidades, telas ou regras do projeto futuro.

## Visão geral

O NuArteMO é um site institucional composto por páginas HTML estáticas e um módulo PHP de agenda. O módulo dinâmico possui autenticação por sessão, CRUD de eventos via JSON e duas tabelas MariaDB (`usuarios` e `events`).

```text
requisição HTTP
  -> arquivo de rota em public/
     -> autenticação, método HTTP e CSRF
     -> Controller (caso de uso)
        -> Model/Repository (PDO e SQL)
     -> View PHP ou resposta JSON
```

O servidor publica somente `public/` como `DocumentRoot`. Assim, código PHP interno, testes, documentação, banco e arquivos de infraestrutura não podem ser acessados pela web. As URLs externas continuam iguais.

## Estrutura e responsabilidades

```text
nuartemo/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php          autentica e migra senha legada
│   │   ├── DiagnosticsController.php   monta o diagnóstico do ambiente
│   │   └── EventController.php         lista e executa o CRUD de eventos
│   ├── Domain/
│   │   └── EventValidator.php          valida e normaliza dados do evento
│   ├── Http/
│   │   └── Security.php                sessão, autenticação, CSRF e JSON
│   ├── Models/
│   │   ├── Contracts/                  contratos usados pelos controllers
│   │   ├── Event.php                   SQL da tabela events
│   │   ├── SystemStatus.php            consulta de disponibilidade do banco
│   │   └── User.php                    SQL da tabela usuarios
│   ├── Support/
│   │   ├── bootstrap.php               autoload, ambiente e PDO
│   │   └── View.php                    carregador de templates
│   └── Views/
│       ├── auth/login.php              formulário de login
│       ├── events/manage.php           agenda administrativa única
│       └── system/diagnostics.php      diagnóstico de desenvolvimento
├── public/                              único diretório exposto pelo servidor
│   ├── *.html                           páginas institucionais
│   ├── *.php                            adaptadores das rotas HTTP
│   ├── css/, js/, images/, icomoon/    assets públicos
│   ├── style.css
│   └── .htaccess                       headers e opções da área pública
├── database/                            schema local
├── docker/                              configuração do ambiente
├── docs/                                documentação técnica
└── tests/                               testes automatizados
```

As páginas institucionais continuam estáticas. Convertê-las em templates compartilhados seria uma segunda etapa grande e visual, desnecessária para consolidar o MVC dinâmico atual.

## Fluxos atuais

### Login

1. `login.php` inicia a sessão e apresenta a View.
2. No POST, a rota valida o token CSRF.
3. `AuthController` normaliza o e-mail e consulta `User`.
4. Senhas com hash usam `password_verify()`.
5. Uma senha legada em texto puro, se válida, é substituída por hash no mesmo login.
6. A sessão recebe `id` e `nome`; o usuário segue para `eventos2.php`.

### Agenda

1. `eventos.php` e o alias `eventos2.php` exigem sessão.
2. Uma única View renderiza o FullCalendar e inclui tokens CSRF.
3. O JavaScript consome as quatro rotas JSON legadas.
4. Cada rota valida autenticação; mutações também exigem POST e CSRF.
5. `EventController` valida dados e delega persistência ao Model `Event`.

### Diagnóstico

`diagnostico.php` exige sessão, `APP_ENV=development` e `DIAGNOSTICS_ENABLED=true`. Nos demais ambientes responde 404.

## Dados

| Tabela | Campos e regras atuais |
| --- | --- |
| `usuarios` | `id`, `nome`, `email` único e `senha` (hash, com migração de legado) |
| `events` | `id`, `title` de 1–150 caracteres, `color` `#RRGGBB`, `start` e `end` |

O schema local está em `database/init/01_schema.sql`. Credenciais vêm de variáveis de ambiente.

## Limites atuais conhecidos

- Não existe rota de logout.
- Não há papéis; qualquer sessão válida administra toda a agenda.
- As páginas estáticas repetem cabeçalho, rodapé e dependências.
- O frontend depende de bibliotecas locais antigas e CDNs externas.
- A API mantém nomes de arquivo legados em vez de URLs orientadas a recurso.
- Ainda faltam testes de integração com banco e navegador.
- O deploy depende da hospedagem apontar explicitamente o `DocumentRoot` para `public/`.

Esses limites são inventário, não autorização para criar funcionalidades novas.
