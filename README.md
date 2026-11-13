# NUARTE IFRN/MO

Site institucional e agenda do NUARTE, migrados para Laravel 13 com autenticação administrativa e ambiente Docker.

## Requisitos

- Docker com Docker Compose

## Instalação

1. Crie a configuração local:

   ```bash
   cp .env.example .env
   docker run --rm php:8.5-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
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

## Produção no CapRover do IFRN

O código está no [GitHub](https://github.com/nuartemo/nuartemo). Aponte o deploy para a branch `main`, que contém o `captain-definition` e o `Dockerfile` na raiz.

1. Em **HTTP Settings**, configure **Container HTTP Port** como **80** e mantenha a porta do contêiner sem mapeamento público.
2. Aponte o DNS do domínio institucional para o CapRover e adicione o domínio à aplicação. A infraestrutura deve liberar **443/TCP** para HTTPS e **80/TCP** para redirecionamento e validação do certificado via HTTP.
3. Clique em **Enable HTTPS** no domínio. Após a emissão do certificado, habilite **Force HTTPS**. O CapRover encerra a conexão TLS na porta pública **443** e encaminha as requisições ao Apache na porta interna **80**. O Apache usa `X-Forwarded-Proto` para sinalizar HTTPS ao Laravel e enviar o cabeçalho HSTS. [Domínios e HTTPS no CapRover](https://caprover.com/docs/domains)

Solicite à infraestrutura uma base **MySQL** na porta **3306** e preencha as variáveis abaixo no painel do CapRover. A aplicação atual **não precisa de volume persistente**. Os dados, as sessões, o cache e as tabelas de fila ficam no MySQL. Os arquivos públicos são entregues na imagem.

```dotenv
APP_NAME=NUARTE
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=https://<dominio-institucional>
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=<fornecido-pela-infra>
DB_PORT=3306
DB_DATABASE=<fornecido-pela-infra>
DB_USERNAME=<fornecido-pela-infra>
DB_PASSWORD=<fornecido-pela-infra>

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
LOG_CHANNEL=stderr
LOG_LEVEL=info
```

Gere a `APP_KEY` uma vez com `php artisan key:generate --show` em um checkout com as dependências instaladas e mantenha o mesmo valor entre deploys. Insira a chave e as credenciais diretamente no painel. O MySQL é fornecido pela infraestrutura e o CapRover usa somente o contêiner da aplicação.

Na inicialização, o contêiner aguarda o MySQL e aplica as migrations antes de iniciar o Apache. Após o primeiro deploy, verifique `/up`, a página inicial e a agenda pelo domínio HTTPS. Para criar a conta inicial do painel, configure `ADMIN_NAME`, `ADMIN_EMAIL` e `ADMIN_PASSWORD` temporariamente e execute no contêiner:

```bash
php artisan db:seed --class=AdminUserSeeder --force --no-interaction
```

Remova `ADMIN_PASSWORD` do painel após criar a conta. A versão atual não dispara tarefas em fila e pode operar sem um worker.

O Laravel gera o token CSRF por sessão. Os formulários internos de login, logout e manutenção de eventos enviam o campo `_token` via `@csrf`. Para requisições JavaScript que alterem dados na própria aplicação, use o valor da meta tag `csrf-token` no cabeçalho `X-CSRF-TOKEN`. A `APP_KEY` protege os cookies e deve permanecer estável entre deploys. [Proteção CSRF no Laravel](https://laravel.com/docs/13.x/csrf)

## Comandos úteis

```bash
docker compose logs -f app
docker compose exec app php artisan migrate --force
docker run --rm --user "$(id -u):$(id -g)" --entrypoint php -e HOME=/tmp -v "$PWD:/app" -w /app composer:2 artisan test
docker run --rm --user "$(id -u):$(id -g)" --entrypoint php -e HOME=/tmp -v "$PWD:/app" -w /app composer:2 vendor/bin/pint --test
```

## Segurança

As credenciais do sistema antigo foram removidas do código. Como elas continuam presentes no histórico Git, a senha antiga do banco deve ser invalidada no servidor antes da publicação desta versão.

## ChaveNUARTE — protótipo frontend

- Botão no header: `resources/views/pages/home.blade.php`, dentro do offcanvas existente.
- CSS da entrada: `public/css/key-panel-entry.css`, idêntico ao aprovado na branch visual.
- Painel estático: `resources/views/pages/key-panel.blade.php` (HTML) e `public/chavenuarte-assets/` (JS, CSS, favicon e ícones).
- URL com sessão SUAP: `/painel-chaves`; Histórico: `/painel-chaves#/historico`.
- O document root deve ser o `public/` já previsto pelo Laravel. Os assets do painel usam caminhos relativos; o HashRouter permite atualizar o Histórico sem rota Laravel adicional.

Artefatos reutilizados de `origin/frontend/chavenuarte-entry` (`ba7c6f7`), equivalentes aos de `bdda3d0`. O CSS, SVG e animações da entrada foram preservados. A única alteração no JS compilado substitui cinco literais de perfil/responsáveis por exemplos explícitos, pois não havia comprovação da origem fictícia dos nomes. O aviso de demonstração fica documentado neste README; a faixa adicionada na migração foi removida para restaurar o layout aprovado. Não é necessário recompilar o painel para esta migração.

O bundle publicado é `public/painel-chaves/assets/index-a588e939141e56c5.js`: o sufixo corresponde aos primeiros 16 caracteres do SHA-256 do conteúdo publicado (`a588e939141e56c502f330f5cf72df4c9a86046e219385a926a7c80e5bba73b5`). A finalização apenas renomeia o arquivo já anonimizado e atualiza sua referência no HTML, sem editar novamente o bundle nem o repositório `key-panel`. Os caminhos novos da homepage usam `asset()` e `url()`, respeitando a URL base do Laravel; a barra final mantém os assets relativos do painel.

O painel usa autenticação SUAP na entrada e apresenta dados fictícios. As operações existem apenas na memória da página: desaparecem ao recarregar. O perfil de demonstração não resulta de login, não comprova vínculo de bolsista e não autoriza acesso a dados reais. O botão “Sair” já era apenas demonstrativo.

### Login institucional com SUAP

Cadastre a aplicação no SUAP como cliente **Confidential**, fluxo **Authorization Code**, com a URI de redirecionamento exata `https://nuartemo.ifrn.edu.br/auth/suap/callback`.

No CapRover, mantenha `APP_URL=https://nuartemo.ifrn.edu.br` e configure `SUAP_CLIENT_ID` e `SUAP_CLIENT_SECRET`. O escopo padrão é `identificacao`. Se o cadastro exigir outro valor, defina em `SUAP_SCOPE` o escopo autorizado pelo IFRN que dá acesso a `GET /api/rh/eu/`. O fluxo usa PKCE S256 e valida um `state` da sessão antes de trocar o código no servidor.

- `GET /login` redireciona para o SUAP para iniciar o login administrativo.
- `GET /auth/suap` inicia a autorização em `https://suap.ifrn.edu.br/o/authorize/`.
- `GET /auth/suap/callback` recebe o código, solicita o token em `https://suap.ifrn.edu.br/o/token/` e lê o campo `identificacao` de `https://suap.ifrn.edu.br/api/rh/eu/`.

O botão Painel de Chaves na página inicial abre `GET /painel-chaves`. Sem sessão SUAP, essa rota inicia a autorização e retorna ao painel após o callback. A sessão do painel demonstrativo permite acesso a identidades válidas do SUAP. Para administrar a agenda pelo SUAP, é preciso ter uma conta local previamente autorizada. Após confirmar a identidade institucional do titular e aplicar as migrations, vincule a conta pelo terminal do contêiner:

```bash
php artisan suap:link administrador@example.com IDENTIFICADOR_SUAP
```

Para desenvolvimento via Docker, cadastre também `http://10.38.0.90:8000/auth/suap/callback` no cliente de desenvolvimento e use `APP_URL=http://10.38.0.90:8000`. O protótipo ChaveNUARTE continua demonstrativo. O vínculo com a conta local autoriza o acesso à administração da agenda e deve ser reservado às pessoas que podem exercer essa função.

### Validação local desta migração

O CSS da entrada pode ser comparado diretamente com o artefato aprovado. Conferir o botão no desktop (hover/foco/underline) e no offcanvas mobile, abertura de `/painel-chaves/`, CSS/JS/SVG e navegação/refresh de `#/historico`.

**PASS no Chrome local** (desktop 1440 px e mobile 390 px): equivalência dos estilos e medidas do header com a referência aprovada, hover/foco/underline, movimento reduzido, abertura/fechamento do offcanvas, clique até o protótipo, HTTP 200 dos assets e Histórico com refresh. Retirada/devolução demonstrativas e descarte do estado ao recarregar também verificados. `git diff --check` passou; backend, autenticação, banco, dependências, `public/style.css` e `public/.htaccess` permanecem iguais à base.

Na finalização, os oito arquivos foram reconciliados por SHA-256 com a auditoria anterior. **PASS nas 12 verificações pontuais no Chrome**: caminhos novos e CSS, homepage → painel, ausência da faixa e perfil fictício, Histórico → refresh e HTTP CSS/JS/SVG, na raiz (desktop) e sob o prefixo `/nuartemo/` (mobile); nenhuma referência local quebrada ou exceção JS do painel. A prévia estática substituiu somente os dois helpers novos por URLs de teste, sem executar Blade. As evidências anteriores de hover, foco, animações e operações demonstrativas continuam válidas: CSS, SVG, componente e conteúdo do bundle foram preservados. O HTML do painel coincide com o aprovado, exceto pelo novo nome do JS.

**LARAVEL_RUNTIME=NOT_RUN**: renderização real de Blade e testes Laravel pendentes no PC `lain`, pois PHP, Composer e `vendor/` não estão disponíveis. Docker está disponível, mas não há contêiner NUARTEMO em execução. Nenhuma dependência foi instalada, serviço iniciado ou migration executada. A prévia estática da homepage não substitui essa validação pelo Laravel; não há homologação integral do sistema.
