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

## ChaveNUARTE — protótipo frontend

- Botão no header: `resources/views/pages/home.blade.php`, dentro do offcanvas existente.
- CSS da entrada: `public/css/key-panel-entry.css`, idêntico ao aprovado na branch visual.
- Painel estático: `public/painel-chaves/` (HTML, JS, CSS, favicon e ícones).
- URL pública: `/painel-chaves/`; Histórico: `/painel-chaves/#/historico`.
- O document root deve ser o `public/` já previsto pelo Laravel. Os assets do painel usam caminhos relativos; o HashRouter permite atualizar o Histórico sem rota Laravel adicional.

Artefatos reutilizados de `origin/frontend/chavenuarte-entry` (`ba7c6f7`), equivalentes aos de `bdda3d0`. O CSS, SVG e animações da entrada foram preservados. A única alteração no JS compilado substitui cinco literais de perfil/responsáveis por exemplos explícitos, pois não havia comprovação da origem fictícia dos nomes. O aviso de demonstração fica documentado neste README; a faixa adicionada na migração foi removida para restaurar o layout aprovado. Não é necessário recompilar o painel para esta migração.

O bundle publicado é `public/painel-chaves/assets/index-a588e939141e56c5.js`: o sufixo corresponde aos primeiros 16 caracteres do SHA-256 do conteúdo publicado (`a588e939141e56c502f330f5cf72df4c9a86046e219385a926a7c80e5bba73b5`). A finalização apenas renomeia o arquivo já anonimizado e atualiza sua referência no HTML, sem editar novamente o bundle nem o repositório `key-panel`. Os caminhos novos da homepage usam `asset()` e `url()`, respeitando a URL base do Laravel; a barra final mantém os assets relativos do painel.

O painel atual é demonstrativo, utiliza dados fictícios e não possui autenticação real. As operações existem apenas na memória da página: desaparecem ao recarregar. O perfil de demonstração não resulta de login, não comprova vínculo de bolsista e não autoriza acesso a dados reais. O botão “Sair” já era apenas demonstrativo.

### Contrato com o backend SUAP

**SUAP_LOGIN=PENDING_BACKEND**. Na auditoria da base `a743336`, não foi encontrada rota, controller ou configuração de autenticação SUAP. O `/login` existente autentica administradores de eventos por e-mail/senha e permanece independente.

| Etapa | Estado nesta migração |
| --- | --- |
| A — link visual para o protótipo | Implementado em `/painel-chaves/` |
| B — início da autenticação SUAP | PENDENTE_BACKEND: endpoint real ainda ausente |
| C — autenticação SUAP concluída | PENDENTE_BACKEND |
| D — autorização de bolsista | PENDENTE_BACKEND |
| E — persistência de retiradas/devoluções | PENDENTE_BACKEND |

Para a futura integração, a equipe backend deverá:

1. Implementar e validar a rota real de início SUAP no Laravel (`routes/web.php`) e informar sua URL/nome. O ponto de ligação frontend é o `href` de `.key-panel-link` em `resources/views/pages/home.blade.php`, marcado com `PENDENTE_BACKEND`. Só substituir `/painel-chaves/` após comprovar o endpoint; esta migração não cria uma rota provisória.
2. Obter o cadastro/aprovação institucional da aplicação, identificador público do cliente, domínios e URIs de callback autorizados para cada ambiente, endpoints vigentes e escopos necessários. Eventuais segredos devem permanecer exclusivamente no servidor, fora do Git e dos assets públicos.
3. Implementar a validação da identidade retornada, sessão e tratamento de falhas; definir e validar o vínculo/autorização de bolsista por matrícula com a instituição.
4. Fornecer persistência e endpoints protegidos para retiradas/devoluções. O diretório público do protótipo não deve receber dados reais antes dessa proteção.

As referências [cliente SUAP IFRN](https://github.com/ifrn-oficial/cliente_suap_javascript) e [login SUAP JavaScript](https://github.com/claytonmaciel/login_suap_javascript) servem apenas para consulta de protocolo. Nenhum cliente antigo foi incorporado. Não usar o OAuth Implicit legado, tokens em cookies via JavaScript, `/api/eu/` legado ou captura de senha institucional neste frontend.

### Validação local desta migração

O CSS da entrada pode ser comparado diretamente com o artefato aprovado. Conferir o botão no desktop (hover/foco/underline) e no offcanvas mobile, abertura de `/painel-chaves/`, CSS/JS/SVG e navegação/refresh de `#/historico`.

**PASS no Chrome local** (desktop 1440 px e mobile 390 px): equivalência dos estilos e medidas do header com a referência aprovada, hover/foco/underline, movimento reduzido, abertura/fechamento do offcanvas, clique até o protótipo, HTTP 200 dos assets e Histórico com refresh. Retirada/devolução demonstrativas e descarte do estado ao recarregar também verificados. `git diff --check` passou; backend, autenticação, banco, dependências, `public/style.css` e `public/.htaccess` permanecem iguais à base.

Na finalização, os oito arquivos foram reconciliados por SHA-256 com a auditoria anterior. **PASS nas 12 verificações pontuais no Chrome**: caminhos novos e CSS, homepage → painel, ausência da faixa e perfil fictício, Histórico → refresh e HTTP CSS/JS/SVG, na raiz (desktop) e sob o prefixo `/nuartemo/` (mobile); nenhuma referência local quebrada ou exceção JS do painel. A prévia estática substituiu somente os dois helpers novos por URLs de teste, sem executar Blade. As evidências anteriores de hover, foco, animações e operações demonstrativas continuam válidas: CSS, SVG, componente e conteúdo do bundle foram preservados. O HTML do painel coincide com o aprovado, exceto pelo novo nome do JS.

**LARAVEL_RUNTIME=NOT_RUN**: renderização real de Blade e testes Laravel pendentes no PC `lain`, pois PHP, Composer e `vendor/` não estão disponíveis. Docker está disponível, mas não há contêiner NUARTEMO em execução. Nenhuma dependência foi instalada, serviço iniciado ou migration executada. A prévia estática da homepage não substitui essa validação pelo Laravel; não há homologação integral do sistema.
