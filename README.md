# Sistema de Gerenciamento de Moedas para Alunos

## **Objetivo**
Criar um sistema que permite aos alunos acumularem moedas virtuais através de bons comportamentos ou perderem moedas por comportamentos inadequados. As moedas podem ser trocadas por brindes no colégio.

---

## **Principais Funcionalidades**

### **1. Alunos**
- Cada aluno possui um crachá com QR Code único.
- Ao escanear o QR Code, o aluno acessa uma página que exibe:
  - Seu nome.
  - Saldo atual de moedas.
  - Histórico de transações (ganhos e perdas de moedas).
- **Nota**: Os alunos não precisam de login para acessar o saldo.

### **2. Professores**
- Professores têm acesso ao sistema via login.
- Após o login, podem:
  - Adicionar moedas para um aluno (ex.: "Fez atividade" +5).
  - Remover moedas de um aluno (ex.: "Conversa em sala" -10).
  - Visualizar o saldo e histórico de transações de qualquer aluno.

### **3. Admin (Opcional)**
- Pode cadastrar novos alunos e professores.
- Gera QR Codes para os alunos.
- Gerencia os registros no banco de dados.

---

## **Estrutura do Sistema**

### **1. Frontend (Interfaces)**
- **Alunos**: Página simples e visualmente atrativa, mostrando saldo e histórico.
- **Professores**: Página para login, listagem de alunos e controle de moedas.
- **QR Code**: Gerado para cada aluno, vinculado ao ID único no banco de dados.

### **2. Backend**
- **Rotas principais**:
  - `aluno.php`: Exibe saldo e histórico do aluno com base no ID.
  - `professor.php`: Dashboard para professores após login.
  - `add_moedas.php`: Adiciona moedas a um aluno.
  - `remove_moedas.php`: Remove moedas de um aluno.
- **Segurança**:
  - Sistema de autenticação para professores.
  - Dados do aluno acessíveis somente pelo QR Code.

### **3. Banco de Dados**
#### **Tabelas**
- **`alunos`**: Dados do aluno (ID, nome, moedas, link do QR Code).
- **`professores`**: Dados de login do professor (ID, nome, email, senha).
- **`transacoes`**: Histórico de transações (quem, quanto, descrição e quando).

---

## **Fluxo do Sistema**

1. **Cadastro de Alunos e Professores**
   - O administrador cadastra alunos e professores no sistema.

2. **Geração de QR Codes**
   - Cada aluno recebe um QR Code com o link exclusivo que leva à página de saldo.

3. **Uso pelos Alunos**
   - Os alunos escaneiam o QR Code para ver moedas acumuladas e histórico.

4. **Uso pelos Professores**
   - Professores fazem login, selecionam alunos e gerenciam as moedas.

5. **Troca de Moedas por Brindes**
   - Alunos podem trocar moedas acumuladas por brindes, que são registrados manualmente ou via sistema (opcional).

---

## **Tecnologias e Ferramentas**

### **Frontend**
- HTML/CSS/JavaScript: Interfaces responsivas e intuitivas.

### **Backend**
- PHP: Processamento de dados e lógica do sistema.

### **Banco de Dados**
- MySQL: Armazenamento das informações dos alunos, professores e transações.

### **Outras Ferramentas**
- Biblioteca de QR Code: Para geração de QR Codes vinculados aos alunos.

---

## **Principais Usuários**
1. **Alunos**: Acompanham moedas e histórico.
2. **Professores**: Gerenciam moedas para os alunos.
3. **Admin (Opcional)**: Gerencia o cadastro de alunos e professores.

---

## **Benefícios do Projeto**
- Incentiva bons comportamentos de forma gamificada.
- Promove um sistema justo e transparente para alunos e professores.
- Facilita a gestão de recompensas no ambiente escolar.

---
