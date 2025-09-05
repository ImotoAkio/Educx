# 🎮 Sistema de Avatares com Desbloqueio por Nível

## 📋 **Visão Geral**

O sistema de avatares foi completamente reformulado para incluir um sistema de desbloqueio baseado no nível de XP do aluno. Agora os avatares são desbloqueados conforme o aluno progride no sistema.

## 🏆 **Níveis de Desbloqueio**

### **Nível 1 (0-499 XP) - Iniciante**
- ✅ **Default** - Avatar padrão para iniciantes
- ✅ **Gatito** - Um gatinho fofo para começar sua jornada

### **Nível 2 (500-999 XP) - Explorador**
- 🔓 **Ratatui** - Um rato aventureiro para exploradores
- 🔓 **Abeia** - Uma abelha trabalhadora

### **Nível 3 (1000-1999 XP) - Guardião**
- 🔒 **Robo Estudante** - Robô dedicado aos estudos *(Raro)*
- 🔒 **Cachorrão** - O melhor amigo do estudante *(Raro)*

### **Nível 4 (2000+ XP) - Líder**
- 🔒 **Robo Legal** - Robô com estilo único *(Épico)*
- 🔒 **Rodolfo** - Avatar lendário para líderes *(Épico)*
- 🔒 **Robozão** - O avatar mais poderoso de todos *(Lendário)*

## 🎨 **Categorias de Raridade**

- **Comum** (Cinza) - Avatares básicos
- **Raro** (Azul) - Avatares especiais
- **Épico** (Roxo) - Avatares únicos
- **Lendário** (Laranja) - Avatares exclusivos

## 🔧 **Funcionalidades Implementadas**

### **1. Sistema de Desbloqueio**
- ✅ Verificação automática de nível
- ✅ Desbloqueio instantâneo ao atingir o nível
- ✅ Registro de avatares desbloqueados

### **2. Interface Visual**
- ✅ Indicadores de status (Desbloqueado/Disponível/Bloqueado)
- ✅ Filtro grayscale para avatares bloqueados
- ✅ Badges de categoria e requisitos
- ✅ Informações detalhadas ao clicar

### **3. Feedback ao Usuário**
- ✅ Mensagens informativas sobre requisitos
- ✅ Alertas de sucesso/erro
- ✅ Exibição do nível atual e XP

## 📊 **Estrutura do Banco de Dados**

### **Tabela `avatares`**
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR) - Nome do avatar
- arquivo (VARCHAR) - Nome do arquivo
- nivel_requerido (INT) - Nível necessário
- xp_requerido (INT) - XP necessário
- descricao (TEXT) - Descrição do avatar
- categoria (ENUM) - Comum/Raro/Épico/Lendário
- preco_moedas (INT) - Preço em moedas (futuro)
- disponivel (BOOLEAN) - Se está disponível
```

### **Tabela `avatares_alunos`**
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- aluno_id (INT, FOREIGN KEY) - ID do aluno
- avatar_id (INT, FOREIGN KEY) - ID do avatar
- data_desbloqueio (TIMESTAMP) - Data do desbloqueio
- metodo_desbloqueio (ENUM) - Nível/Compra/Evento
```

## 🚀 **Como Usar**

### **Para Alunos:**
1. Acesse `personalizar.php?id=SEU_ID`
2. Veja seu nível atual e XP no canto superior direito
3. Clique nos avatares para ver informações detalhadas
4. Avatares desbloqueados podem ser usados imediatamente
5. Avatares disponíveis são desbloqueados automaticamente

### **Para Professores/Administradores:**
1. Execute `configurar_avatares.php` para configurar o sistema
2. Execute `desbloquear_avatares_basicos.php` para dar acesso inicial
3. Use `sistema_avatares.sql` para configuração manual

## 🎯 **Benefícios**

- **Gamificação**: Sistema de progressão claro e consistente
- **Motivação**: Objetivos específicos para os alunos
- **Engajamento**: Desbloqueio gradual mantém interesse
- **Personalização**: Avatares únicos para cada nível
- **Escalabilidade**: Fácil adição de novos avatares
- **Consistência**: Usa as mesmas métricas de nível de `aluno.php`

## 🔮 **Funcionalidades Futuras**

- [ ] Compra de avatares com moedas
- [ ] Avatares exclusivos de eventos
- [ ] Sistema de conquistas
- [ ] Avatares animados
- [ ] Personalização de cores
- [ ] Avatares temporários

## 📝 **Notas Técnicas**

- O sistema é **100% compatível** com o sistema de XP existente em `aluno.php`
- Usa a mesma função `calcularNivelEProgresso()` para determinar títulos de nível
- Avatares básicos são automaticamente desbloqueados para alunos existentes
- O sistema verifica permissões antes de permitir uso
- Todos os avatares são armazenados em `asset/img/avatar/`
- O sistema é responsivo e funciona em dispositivos móveis
- **Consistência total** com as métricas de nível do sistema principal
