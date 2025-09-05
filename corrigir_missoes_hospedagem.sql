-- Script SQL para corrigir o problema das missões na hospedagem
-- Execute este script no phpMyAdmin ou similar da sua hospedagem

-- 1. Adicionar campo 'status' se não existir
ALTER TABLE missoes ADD COLUMN status ENUM('ativa', 'inativa') DEFAULT 'ativa';

-- 2. Adicionar campo 'turma_id' se não existir  
ALTER TABLE missoes ADD COLUMN turma_id INT NULL;

-- 3. Adicionar campo 'criador_id' se não existir
ALTER TABLE missoes ADD COLUMN criador_id INT NULL;

-- 4. Definir todas as missões existentes como 'ativa'
UPDATE missoes SET status = 'ativa' WHERE status IS NULL;

-- 5. Verificar se a tabela solicitacoes_missoes existe
CREATE TABLE IF NOT EXISTS solicitacoes_missoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    missao_id INT NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (missao_id) REFERENCES missoes(id) ON DELETE CASCADE
);

-- 6. Verificar estrutura final
DESCRIBE missoes;
