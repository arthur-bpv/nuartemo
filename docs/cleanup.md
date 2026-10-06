# Auditoria de limpeza

## Removido nesta organização

| Item | Motivo |
| --- | --- |
| `.cache/replit/` | metadados locais do editor |
| `.config/configstore/` | cache local de notificação do npm |
| `teste.txt` | arquivo de teste sem referência |
| `tela-de-login.html` | protótipo sem referência; login ativo é `login.php` |
| `eventos.html` | página vazia sem referência; rota ativa é `eventos.php` |
| `conexao.php`, `conexao2.php` | fachadas sem consumidores após os Models centralizarem PDO |
| `protect.php` | fachada sem consumidores após uso direto de `Security` |
| segunda cópia da agenda | substituída por uma View única e alias compatível |
| páginas, endpoints e assets soltos na raiz | movidos para `public/`, a única raiz web |

## Estado da raiz após a organização

```text
.github/  app/  database/  docker/  docs/  public/  storage/  tests/
Dockerfile  docker-compose.yml  README.md  e arquivos de configuração ocultos
```

Não permanecem HTML, endpoints PHP, CSS, JavaScript ou imagens soltos na raiz do repositório.

## Mantido deliberadamente

| Item | Motivo |
| --- | --- |
| `eventos2.php` | destino do login e possível favorito externo; agora é alias pequeno |
| `thank_you.html` | referenciado pelo formulário em `about.html` |
| builds minificados e não minificados do FullCalendar | o uso varia; remoção exige teste visual |
| imagens semelhantes | referências distribuídas e formatos distintos |
| `.replit` | configuração histórica pequena, mantida até decisão de hospedagem |

## Próxima auditoria possível

- Consolidar JavaScript duplicado após teste visual de todas as páginas.
- Medir CSS sem uso no navegador antes de remover seletores.
- Decidir se `.replit` ainda faz parte do fluxo.
- Inspecionar variantes `images/billboard-img.*` visualmente e por referência.

Nada desta seção deve ser removido automaticamente sem a respectiva verificação.
