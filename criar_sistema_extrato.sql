-- Script para criar o sistema de extrato de moedas e atitudes
-- Execute este script no seu banco de dados MySQL

-- Tabela de Atitudes (regras do sistema)
CREATE TABLE IF NOT EXISTS atitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    valor_moedas INT NOT NULL COMMENT 'Valor positivo para ganho, negativo para perda',
    tipo ENUM('ganho', 'perda') NOT NULL,
    status ENUM('ativa', 'inativa') DEFAULT 'ativa',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Extrato de Moedas (histórico de transações)
CREATE TABLE IF NOT EXISTS extrato_moedas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    professor_id INT NULL COMMENT 'ID do professor que executou a ação (NULL se for automático)',
    atitude_id INT NULL COMMENT 'ID da atitude aplicada (NULL se for manual)',
    tipo ENUM('ganho', 'perda', 'manual') NOT NULL,
    valor INT NOT NULL COMMENT 'Valor sempre positivo, tipo indica se é ganho ou perda',
    descricao TEXT,
    motivo TEXT COMMENT 'Motivo específico da transação',
    data_transacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_aluno (aluno_id),
    INDEX idx_data (data_transacao),
    INDEX idx_atitude (atitude_id),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL,
    FOREIGN KEY (atitude_id) REFERENCES atitudes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir algumas atitudes de exemplo
INSERT INTO atitudes (titulo, descricao, valor_moedas, tipo, status) VALUES
('Participação Ativa', 'Aluno participou ativamente da aula e contribuiu com ideias.', 10, 'ganho', 'ativa'),
('Ajuda a Colegas', 'Aluno ajudou um colega com dificuldade em uma atividade.', 5, 'ganho', 'ativa'),
('Comportamento Exemplar', 'Aluno demonstrou excelente comportamento durante a aula.', 15, 'ganho', 'ativa'),
('Tarefa Completa', 'Aluno completou a tarefa de casa corretamente.', 10, 'ganho', 'ativa'),
('Atraso na Entrega', 'Aluno entregou atividade com atraso sem justificativa.', 5, 'perda', 'ativa'),
('Uso Indevido de Celular', 'Aluno foi pego usando celular durante a aula.', 10, 'perda', 'ativa'),
('Desrespeito', 'Aluno desrespeitou um colega ou professor.', 20, 'perda', 'ativa'),
('Atraso', 'Aluno chegou atrasado à aula.', 2, 'perda', 'ativa'),
('Esqueceu Material', 'Aluno esqueceu material escolar necessário.', 3, 'perda', 'ativa');

