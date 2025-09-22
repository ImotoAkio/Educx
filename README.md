# 🎮 Educx - Sistema de Gamificação Escolar

## **Visão Geral**
O Educx é uma plataforma completa de gamificação escolar que recompensa alunos com moedas virtuais e XP (experiência) por atitudes positivas, bom comportamento e participação em atividades. O sistema inclui missões, loja virtual, ranking e painéis administrativos para professores e secretaria.

---

## **🚀 Funcionalidades Principais**

### **👨‍🎓 Para Alunos**
- **Acesso via QR Code**: Cada aluno possui um QR Code único para acesso rápido
- **Sistema de Moedas**: Acumulação de moedas virtuais para troca por recompensas
- **Sistema de XP**: Experiência que determina o nível do aluno
- **Missões**: Participação em atividades e desafios propostos pelos professores
- **Loja Virtual**: Troca de moedas por produtos e recompensas
- **Ranking**: Competição saudável entre alunos da mesma turma
- **Personalização**: Avatares e fundos personalizáveis
- **Histórico Completo**: Acompanhamento de todas as atividades e recompensas

### **👨‍🏫 Para Professores**
- **Painel Administrativo**: Dashboard completo com estatísticas
- **Gerenciamento de Alunos**: Visualização e edição de dados dos alunos
- **Criação de Missões**: Desafios personalizados para turmas específicas
- **Sistema de Aprovação**: Aprovação de missões e trocas dos alunos
- **Ações Rápidas**: Adição/remoção de XP e moedas via app móvel
- **Relatórios**: Análise de desempenho e participação dos alunos
- **Quiz Interativo**: Criação e aplicação de questionários gamificados

### **🏢 Para Secretaria**
- **Painel de Controle**: Gestão completa do sistema
- **Aprovação de Trocas**: Controle de recompensas solicitadas pelos alunos
- **Gestão de Produtos**: Cadastro e edição de itens da loja
- **Relatórios Financeiros**: Controle de moedas em circulação
- **Gestão de Usuários**: Cadastro de professores e administradores

---

## **📱 App Móvel para Professores**

### **Funcionalidades do App**
- **Leitura de QR Code**: Escaneamento rápido de códigos dos alunos
- **Ações Instantâneas**: Adição/remoção de XP e moedas
- **Criação de Missões**: Desafios rápidos para alunos específicos
- **Histórico em Tempo Real**: Visualização atualizada das atividades
- **Interface Responsiva**: Otimizada para dispositivos móveis

### **Tecnologia**
- **WebView**: App funciona como navegador integrado
- **Autenticação Web**: Login através de páginas web
- **Sincronização**: Dados atualizados em tempo real

---

## **🛠️ Tecnologias Utilizadas**

### **Backend**
- **PHP 7.4+**: Lógica do servidor e processamento de dados
- **MySQL**: Banco de dados relacional
- **PDO**: Interface de acesso ao banco de dados
- **Composer**: Gerenciamento de dependências

### **Frontend**
- **HTML5/CSS3**: Estrutura e estilização
- **JavaScript (ES6+)**: Interatividade e validações
- **Bootstrap 5**: Framework CSS responsivo
- **Font Awesome**: Ícones e elementos visuais
- **Chart.js**: Gráficos e visualizações

### **Bibliotecas e Ferramentas**
- **php-qrcode**: Geração de códigos QR
- **GSAP**: Animações avançadas
- **Swiper.js**: Carrosséis e sliders
- **jQuery**: Manipulação do DOM

---

## **🗄️ Estrutura do Banco de Dados**

### **Tabelas Principais**
- **`alunos`**: Dados pessoais, moedas, XP e configurações
- **`professores`**: Informações dos educadores e credenciais
- **`turmas`**: Classes e séries dos alunos
- **`missoes`**: Desafios e atividades propostas
- **`solicitacoes_missoes`**: Requisições de missões pelos alunos
- **`produtos`**: Itens disponíveis na loja virtual
- **`solicitacoes_trocas`**: Pedidos de troca de moedas por produtos
- **`log_acoes`**: Auditoria de ações dos professores

### **Relacionamentos**
- Alunos pertencem a turmas (N:1)
- Missões podem ser para turmas específicas ou gerais
- Solicitações vinculam alunos, missões e professores
- Log de ações registra todas as modificações

---

## **📁 Estrutura do Projeto**

```
Educx/
├── 📄 Páginas Principais
│   ├── index.html              # Página inicial
│   ├── login.php               # Sistema de autenticação
│   ├── aluno.php               # Interface do aluno
│   ├── professor_aluno.php     # Gerenciamento via app móvel
│   ├── missoes.php             # Lista de missões disponíveis
│   ├── loja.php                # Loja virtual
│   └── ranking.php             # Classificação dos alunos
│
├── 🎯 Sistema de Missões
│   ├── confirmar_missao.php    # Confirmação de missão
│   ├── realizar_missao.php     # Execução de missão
│   └── resultado_quiz.php      # Resultado de questionários
│
├── 🛒 Sistema de Trocas
│   ├── confirmacao.php         # Confirmação de compra
│   ├── troca_confirmada.php    # Status da troca
│   └── verificar_status.php    # Verificação de status
│
├── 📊 Painéis Administrativos
│   ├── painel/professor/       # Dashboard do professor
│   ├── painel/secretaria/      # Painel da secretaria
│   └── api/                    # APIs para comunicação
│
├── 🎨 Recursos Visuais
│   ├── assets/                 # CSS, JS e imagens
│   ├── asset/                  # Recursos específicos do sistema
│   └── vendor/                 # Bibliotecas externas
│
└── 📋 Scripts e Configurações
    ├── db.php                  # Conexão com banco
    ├── estrutura_quiz.sql      # Estrutura do banco
    └── sistema_avatares.sql    # Sistema de avatares
```

---

## **🚀 Instalação e Configuração**

### **Requisitos**
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Composer (opcional)

### **Passos de Instalação**

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/educx.git
   cd educx
   ```

2. **Configure o banco de dados**
   - Crie um banco de dados MySQL
   - Execute os scripts SQL em `estrutura_quiz.sql` e `sistema_avatares.sql`
   - Configure as credenciais em `db.php`

3. **Configure o servidor web**
   - Aponte o DocumentRoot para a pasta do projeto
   - Certifique-se de que o PHP está habilitado

4. **Instale dependências (opcional)**
   ```bash
   composer install
   ```

5. **Acesse o sistema**
   - Abra `http://seu-dominio/index.html`
   - Configure usuários administrativos
   - Cadastre alunos e professores

---

## **📱 Configuração do App Móvel**

### **Para Desenvolvedores**
1. **Crie um projeto React Native** ou similar
2. **Configure WebView** para carregar as páginas web
3. **Implemente leitor de QR Code** usando bibliotecas nativas
4. **Configure navegação** para `professor_aluno.php?id=XXX`

### **URLs Importantes**
- **Login**: `login.php`
- **Gerenciamento de Aluno**: `professor_aluno.php?id=XXX`
- **API de Ações**: `api/professor_acoes.php`

---

## **🔧 Configurações Avançadas**

### **Personalização**
- **Avatares**: Adicione imagens em `asset/img/avatar/`
- **Temas**: Modifique CSS em `assets/css/`
- **Cores**: Ajuste variáveis CSS em `:root`

### **Segurança**
- **Senhas**: Sistema usa `password_hash()` para criptografia
- **Sessões**: Controle de acesso baseado em sessões PHP
- **Validação**: Sanitização de todos os inputs

### **Performance**
- **Cache**: Implemente cache de consultas frequentes
- **CDN**: Use CDN para assets estáticos
- **Otimização**: Minifique CSS e JavaScript

---

## **📊 Funcionalidades por Módulo**

### **🎯 Sistema de Missões**
- Criação de desafios personalizados
- Aprovação automática ou manual
- Recompensas em XP e moedas
- Categorização por turma

### **🛒 Loja Virtual**
- Catálogo de produtos
- Sistema de troca de moedas
- Aprovação pela secretaria
- Controle de estoque

### **📈 Sistema de Ranking**
- Classificação por turma
- Métricas de desempenho
- Histórico de evolução
- Gamificação competitiva

### **👥 Gestão de Usuários**
- Cadastro de alunos e professores
- Controle de permissões
- Auditoria de ações
- Relatórios de atividade

---

## **🤝 Contribuição**

### **Como Contribuir**
1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

### **Padrões de Código**
- Use PSR-12 para PHP
- Comente funções complexas
- Mantenha consistência no CSS
- Teste suas alterações

---

## **📞 Suporte e Contato**

- **Email**: ccontato@echotec.site
- **Telefone**: (87) 9 9168-2773
- **Endereço**: Avenida 01, nº 86 - Quati, Petrolina - PE

---

## **📄 Licença**

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

## **🔄 Changelog**

### **v2.0.0** - Atual
- ✅ Sistema completo de gamificação
- ✅ App móvel para professores
- ✅ Painéis administrativos responsivos
- ✅ Sistema de missões e trocas
- ✅ Ranking e personalização

### **v1.0.0** - Inicial
- ✅ Sistema básico de moedas
- ✅ QR Codes para alunos
- ✅ Painel de professores

---

**Desenvolvido com ❤️ para transformar a educação através da gamificação!**
