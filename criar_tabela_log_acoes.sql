-- Tabela para registrar todas as ações dos professores
CREATE TABLE IF NOT EXISTS log_acoes_professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    aluno_id INT NOT NULL,
    tipo_acao ENUM('adicionar_xp', 'remover_xp', 'adicionar_moedas', 'remover_moedas', 'criar_missao') NOT NULL,
    valor INT NOT NULL,
    motivo TEXT,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    INDEX idx_professor (professor_id),
    INDEX idx_aluno (aluno_id),
    INDEX idx_tipo_acao (tipo_acao),
    INDEX idx_data_acao (data_acao)
);
