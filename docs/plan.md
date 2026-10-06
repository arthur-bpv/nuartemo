# Planejamento sobre o estado atual

O plano continua focado no sistema existente. Não inclui entidades, telas ou regras do projeto futuro.

## 1. Consolidar a base atual — concluído nesta branch

- Separar controllers, models e views.
- Centralizar validação e PDO.
- Preservar as rotas usadas pelo frontend.
- Isolar páginas, endpoints e assets em `public/` e configurar o `DocumentRoot`.
- Remover duplicações e arquivos comprovadamente mortos.
- Documentar arquitetura, rotas e limites.
- Manter testes unitários para regras e controllers.

## 2. Verificar integração atual — próximo passo recomendado

- Criar dados descartáveis de teste para `usuarios` e `events`.
- Testar login, listagem, criação, edição e exclusão contra MariaDB.
- Automatizar respostas 401, 403 e 422.
- Executar smoke test no navegador para agenda e páginas institucionais.

Critério de saída: fluxos atuais comprovados sem validação manual informal.

## 3. Reduzir dívida visual estática

- Extrair cabeçalho e rodapé repetidos somente após capturas de referência.
- Padronizar Bootstrap, FullCalendar e fontes.
- Revisar HTML e acessibilidade sem redesenhar o site.
- Remover assets somente com relatório de referências e teste visual.

Critério de saída: nenhuma mudança perceptível não intencional.

## 4. Endurecer operação

- Priorizar logout e expiração de sessão como melhorias do sistema atual.
- Definir rate limit de login compatível com a hospedagem.
- Migrar senhas restantes para hash por procedimento administrativo.
- Confirmar no ambiente real que o `DocumentRoot` aponta para `public/`.
- Documentar backup e rollback do banco.

Critério de saída: deploy reproduzível e superfície pública limitada.

## Fora de escopo

- Novas entidades, módulos ou desenho de produto.
- Mudança de framework.
- Renomear URLs ou contratos JSON atuais.
- Redesign das páginas institucionais.
