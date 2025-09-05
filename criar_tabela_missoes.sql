-- Script SQL para criar a tabela missoes do zero
-- Execute este script no phpMyAdmin da sua hospedagem

-- 1. Criar a tabela missoes
CREATE TABLE missoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    xp INT DEFAULT 0,
    moedas INT DEFAULT 0,
    link VARCHAR(500),
    status ENUM('ativa', 'inativa') DEFAULT 'ativa',
    turma_id INT NULL,
    criador_id INT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para melhor performance
    INDEX idx_status (status),
    INDEX idx_turma_id (turma_id),
    INDEX idx_criador_id (criador_id),
    
    -- Chaves estrangeiras (opcional - descomente se as tabelas existirem)
    -- FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE SET NULL,
    -- FOREIGN KEY (criador_id) REFERENCES professores(id) ON DELETE SET NULL
);

-- 2. Criar a tabela solicitacoes_missoes (necessária para o sistema funcionar)
CREATE TABLE solicitacoes_missoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    missao_id INT NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    observacoes TEXT,
    
    -- Índices para melhor performance
    INDEX idx_aluno_id (aluno_id),
    INDEX idx_missao_id (missao_id),
    INDEX idx_status (status),
    
    -- Chaves estrangeiras (opcional - descomente se as tabelas existirem)
    -- FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    -- FOREIGN KEY (missao_id) REFERENCES missoes(id) ON DELETE CASCADE,
    
    -- Evitar duplicatas
    UNIQUE KEY unique_aluno_missao (aluno_id, missao_id)
);

-- 3. Inserir algumas missões de exemplo (opcional)
INSERT INTO missoes (nome, descricao, xp, moedas, link, status, turma_id) VALUES
('Missão de Boas-vindas', 'Complete seu primeiro login no sistema', 50, 10, NULL, 'ativa', NULL),
('Explorador Digital', 'Navegue por todas as seções do sistema', 100, 25, NULL, 'ativa', NULL),
('Desafio de Conhecimento', 'Responda corretamente 5 perguntas em sequência', 200, 50, NULL, 'ativa', NULL),
('Social Media Master', 'Compartilhe uma conquista nas redes sociais', 75, 15, 'https://example.com', 'ativa', NULL),
('Estudante Dedicado', 'Acesse o sistema por 7 dias consecutivos', 300, 75, NULL, 'ativa', NULL);

-- 4. Verificar se as tabelas foram criadas corretamente
SHOW TABLES LIKE 'missoes';
SHOW TABLES LIKE 'solicitacoes_missoes';

-- 5. Verificar a estrutura das tabelas
DESCRIBE missoes;
DESCRIBE solicitacoes_missoes;

-- 6. Verificar os dados inseridos
SELECT * FROM missoes;
