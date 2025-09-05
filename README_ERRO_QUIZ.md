# Resolução do Erro de Quiz

## Problema
O erro `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0' for key 'PRIMARY'` indica que há um problema com a estrutura das tabelas de quiz no banco de dados.

## Causa
Este erro geralmente ocorre quando:
1. As tabelas não existem no banco de dados
2. As colunas de ID não têm AUTO_INCREMENT configurado corretamente
3. Há referências de chave estrangeira incorretas

## Solução

### Opção 1: Executar o Script de Correção (Recomendado)
1. Acesse o arquivo `corrigir_tabelas_quiz.php` no seu navegador
2. O script irá:
   - Verificar se as tabelas existem
   - Criar as tabelas se necessário
   - Corrigir o AUTO_INCREMENT se estiver incorreto
   - Verificar todas as chaves estrangeiras

### Opção 2: Executar SQL Manualmente
Execute o arquivo `estrutura_quiz.sql` no seu banco de dados MySQL.

## Tabelas Criadas/Corrigidas

### 1. `quizzes`
- Armazena os quizzes criados pelos professores
- Chave estrangeira para `professores(id)` e `turmas(id)`

### 2. `perguntas`
- Armazena as perguntas de cada quiz
- Chave estrangeira para `quizzes(id)`

### 3. `alternativas`
- Armazena as alternativas de cada pergunta
- Chave estrangeira para `perguntas(id)`
- Campo `correta` indica se é a resposta correta

### 4. `quizzes_finalizados`
- Registra quais alunos finalizaram quais quizzes
- Chave única para evitar duplicatas
- Chaves estrangeiras para `alunos(id)` e `quizzes(id)`

## Após a Correção
1. Volte para a página "Criar Quiz"
2. Tente criar um novo quiz
3. O erro deve estar resolvido

## Verificação
Para verificar se tudo está funcionando:
1. Acesse `painel/professor/paginas/criar_quiz.php`
2. Preencha os dados do quiz
3. Adicione perguntas e alternativas
4. Salve o quiz
5. Se não houver erros, o problema foi resolvido

## Suporte
Se o problema persistir após executar o script de correção, verifique:
1. Se todas as tabelas foram criadas corretamente
2. Se as chaves estrangeiras estão apontando para as tabelas corretas
3. Se há dados inconsistentes no banco
