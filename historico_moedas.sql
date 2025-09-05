-- Tabela para registrar o histórico de transações de moedas
CREATE TABLE IF NOT EXISTS historico_moedas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    professor_id INT NOT NULL,
    quantidade INT NOT NULL,
    tipo ENUM('adicao', 'remocao') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE
);

-- Índices para melhorar a performance das consultas
CREATE INDEX idx_historico_aluno ON historico_moedas(aluno_id);
CREATE INDEX idx_historico_professor ON historico_moedas(professor_id);
CREATE INDEX idx_historico_data ON historico_moedas(data);
