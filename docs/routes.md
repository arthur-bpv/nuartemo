# Inventário de rotas atuais

Todas as rotas abaixo são arquivos físicos de `public/`, que deve ser o `DocumentRoot`. O prefixo `public` não aparece nas URLs.

## Site institucional

Todas são `GET`, públicas e servidas diretamente como HTML.

| Rota | Conteúdo |
| --- | --- |
| `/` ou `/index.html` | página inicial |
| `/about.html` | sobre o núcleo |
| `/artevivencias.html` | ArteVivências |
| `/a_cicuta.html` | grupo A Cicuta |
| `/andaluz.html` | grupo Andaluz |
| `/banda_descendentes.html` | Banda Descendentes |
| `/banda_sn.html` | Banda SN |
| `/clube_de_escrita.html` | Clube de Escrita |
| `/clube_do_livro.html` | Clube do Livro |
| `/flauteando.html` | Flauteando |
| `/nuav.html` | NUAV |
| `/passocompasso.html` | PassoComPasso |
| `/rabisco.html` | Rabisco |
| `/thank_you.html` | confirmação do formulário de contato |

## Autenticação e agenda

| Método | Rota | Acesso | Destino | Resposta |
| --- | --- | --- | --- | --- |
| GET | `/login.php` | público | View `auth/login` | HTML |
| POST | `/login.php` | público + CSRF | `AuthController` -> `User` | redirect ou erro HTML |
| GET | `/eventos.php` | sessão | View `events/manage` | HTML |
| GET | `/eventos2.php` | sessão | alias de `/eventos.php` | HTML |
| GET | `/listar_evento.php` | sessão | `EventController::index` -> `Event` | JSON |
| POST | `/cadastrar_evento.php` | sessão + CSRF | `EventController::create` -> `Event` | JSON |
| POST | `/editar_evento.php` | sessão + CSRF | `EventController::update` -> `Event` | JSON |
| POST | `/apagar_evento.php` | sessão + CSRF | `EventController::delete` -> `Event` | JSON |

Os caminhos foram preservados para não quebrar JavaScript e links existentes. `eventos2.php` agora é um alias, não uma segunda cópia da tela.

## Operação

| Método | Rota | Acesso | Finalidade |
| --- | --- | --- | --- |
| GET | `/health.php` | público | healthcheck mínimo do contêiner |
| GET | `/diagnostico.php` | sessão + flags de desenvolvimento | PHP, extensões, Apache e banco |

## Códigos relevantes

- `200`: tela ou operação concluída.
- `302`: login necessário ou concluído.
- `401`: JSON acessado sem sessão.
- `403`: método HTTP ou CSRF inválido.
- `404`: diagnóstico desabilitado.
- `422`: identificador ou evento inválido.
- `500`: falha interna sem detalhes sensíveis na resposta.
