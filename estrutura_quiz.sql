-- Estrutura das tabelas para o sistema de Quiz
-- Execute este script para criar ou corrigir as tabelas

-- Tabela de quizzes
CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    criador_id INT NOT NULL,
    turma_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    FOREIGN KEY (criador_id) REFERENCES professores(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
);

-- Tabela de perguntas
CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    texto TEXT NOT NULL,
    ordem INT DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Tabela de alternativas
CREATE TABLE IF NOT EXISTS alternativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_id INT NOT NULL,
    texto TEXT NOT NULL,
    correta BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
);

-- Tabela de quizzes finalizados pelos alunos
CREATE TABLE IF NOT EXISTS quizzes_finalizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    quiz_id INT NOT NULL,
    pontuacao INT DEFAULT 0,
    data_finalizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_aluno_quiz (aluno_id, quiz_id)
);

-- Tabela de respostas dos alunos
CREATE TABLE IF NOT EXISTS respostas_quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    quiz_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    alternativa_id INT NOT NULL,
    correta BOOLEAN DEFAULT FALSE,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE,
    FOREIGN KEY (alternativa_id) REFERENCES alternativas(id) ON DELETE CASCADE
);

-- Índices para melhorar performance
CREATE INDEX idx_quiz_criador ON quizzes(criador_id);
CREATE INDEX idx_quiz_turma ON quizzes(turma_id);
CREATE INDEX idx_pergunta_quiz ON perguntas(quiz_id);
CREATE INDEX idx_alternativa_pergunta ON alternativas(pergunta_id);
CREATE INDEX idx_quiz_finalizado_aluno ON quizzes_finalizados(aluno_id);
CREATE INDEX idx_quiz_finalizado_quiz ON quizzes_finalizados(quiz_id);
CREATE INDEX idx_resposta_aluno ON respostas_quiz(aluno_id);
CREATE INDEX idx_resposta_quiz ON respostas_quiz(quiz_id);

-- Verificar se as tabelas existem e corrigir AUTO_INCREMENT se necessário
-- Se as tabelas já existem, execute apenas os comandos ALTER TABLE abaixo:

-- Corrigir AUTO_INCREMENT das tabelas existentes (execute apenas se as tabelas já existem)
-- ALTER TABLE quizzes MODIFY id INT AUTO_INCREMENT;
-- ALTER TABLE perguntas MODIFY id INT AUTO_INCREMENT;
-- ALTER TABLE alternativas MODIFY id INT AUTO_INCREMENT;
-- ALTER TABLE quizzes_finalizados MODIFY id INT AUTO_INCREMENT;
-- ALTER TABLE respostas_quiz MODIFY id INT AUTO_INCREMENT;
