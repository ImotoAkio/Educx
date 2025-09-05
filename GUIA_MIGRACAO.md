# Guia Completo de Migração de Banco de Dados

## Situação Atual
Você tem dados importantes na hospedagem (novos alunos, compras, trocas e missões) que não podem ser perdidos, mas também precisa das atualizações estruturais do banco local.

## Solução Criada
Criamos scripts automatizados para fazer a migração de forma segura, preservando todos os dados importantes.

## Passo a Passo para Resolver

### 1. BACKUP DA HOSPEDAGEM (CRÍTICO!)
**Execute primeiro na hospedagem:**
```
https://seudominio.com/backup_hospedagem.php
```
- Este script criará um arquivo JSON com todos os dados da hospedagem
- **BAIXE O ARQUIVO GERADO** e mantenha-o seguro
- Anote quantos registros cada tabela tem

### 2. BACKUP LOCAL (SEGURANÇA)
**Execute no seu ambiente local:**
```
http://localhost/Educx/backup_local.php
```
- Este script criará um backup do seu banco local atualizado
- Mantenha este arquivo como segurança

### 3. TESTE LOCAL (OPCIONAL MAS RECOMENDADO)
**Execute no seu ambiente local:**
```
http://localhost/Educx/migrar_dados_hospedagem.php?arquivo=backup_hospedagem_YYYY-MM-DD_HH-MM-SS.json
```
- Substitua o nome do arquivo pelo arquivo baixado da hospedagem
- Este processo irá:
  - Limpar o banco local
  - Inserir os dados da hospedagem
  - Manter a estrutura atualizada
- **TESTE TODAS AS FUNCIONALIDADES** antes de prosseguir

### 4. ATUALIZAR ESTRUTURA NA HOSPEDAGEM
**Execute na hospedagem:**
```
https://seudominio.com/atualizar_estrutura_hospedagem.php
```
- Este script irá:
  - Criar/atualizar tabelas de quiz
  - Criar tabela de histórico de moedas
  - Adicionar índices para performance
  - **NÃO PERDERÁ OS DADOS EXISTENTES**

### 5. MIGRAÇÃO FINAL NA HOSPEDAGEM
**Execute na hospedagem:**
```
https://seudominio.com/migrar_dados_hospedagem_final.php?arquivo=backup_hospedagem_YYYY-MM-DD_HH-MM-SS.json
```
- Substitua o nome do arquivo pelo arquivo de backup
- Este processo irá:
  - Limpar as tabelas
  - Inserir os dados do backup
  - Manter a estrutura atualizada

## Arquivos Criados

1. **backup_hospedagem.php** - Backup dos dados da hospedagem
2. **backup_local.php** - Backup dos dados locais
3. **migrar_dados_hospedagem.php** - Migração para teste local
4. **atualizar_estrutura_hospedagem.php** - Atualização da estrutura na hospedagem
5. **migrar_dados_hospedagem_final.php** - Migração final na hospedagem

## Verificações Importantes

### Antes de Começar:
- [ ] Backup da hospedagem feito
- [ ] Arquivo de backup baixado
- [ ] Backup local feito (segurança)

### Após Migração Local (se fizer teste):
- [ ] Login funcionando
- [ ] Alunos listados corretamente
- [ ] Produtos da loja visíveis
- [ ] Histórico de moedas funcionando
- [ ] Sistema de quiz funcionando

### Após Migração Final na Hospedagem:
- [ ] Login funcionando
- [ ] Todos os alunos presentes
- [ ] Compras e trocas preservadas
- [ ] Missões realizadas mantidas
- [ ] Novas funcionalidades funcionando

## Em Caso de Problemas

### Se algo der errado:
1. **NÃO ENTRE EM PÂNICO**
2. Você tem backups de segurança
3. Execute novamente o script de backup da hospedagem
4. Restaure os dados usando os backups

### Contatos de Suporte:
- Mantenha os arquivos de backup seguros
- Documente qualquer erro que aparecer
- Teste sempre em ambiente local primeiro

## Benefícios da Solução

✅ **Dados Preservados**: Todos os alunos, compras, trocas e missões mantidos
✅ **Estrutura Atualizada**: Novas funcionalidades disponíveis
✅ **Processo Seguro**: Múltiplos backups e verificações
✅ **Testável**: Possibilidade de testar localmente antes
✅ **Reversível**: Possibilidade de voltar ao estado anterior

## Tempo Estimado
- Backup hospedagem: 2-5 minutos
- Backup local: 1-2 minutos
- Teste local (opcional): 5-10 minutos
- Atualização hospedagem: 2-5 minutos
- Migração final: 2-5 minutos

**Total: 10-25 minutos** (dependendo do tamanho dos dados)

---

**IMPORTANTE**: Execute os passos na ordem correta e sempre faça backups antes de qualquer alteração!
