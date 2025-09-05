-- Tabela para gerenciar avatares e seus requisitos de desbloqueio
CREATE TABLE IF NOT EXISTS avatares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    nivel_requerido INT DEFAULT 1,
    xp_requerido INT DEFAULT 0,
    descricao TEXT,
    categoria ENUM('comum', 'raro', 'epico', 'lendario') DEFAULT 'comum',
    preco_moedas INT DEFAULT 0,
    disponivel BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir avatares existentes com requisitos de nível
INSERT INTO avatares (nome, arquivo, nivel_requerido, xp_requerido, descricao, categoria, preco_moedas) VALUES
-- Avatares básicos (nível 1)
('Default', 'default.gif', 1, 0, 'Avatar padrão para iniciantes', 'comum', 0),
('Gatito', 'Gatito.gif', 1, 0, 'Um gatinho fofo para começar sua jornada', 'comum', 0),

-- Avatares nível 2 (Explorador - 500 XP)
('Ratatui', 'Ratatui.gif', 2, 500, 'Um rato aventureiro para exploradores', 'comum', 50),
('Abeia', 'Abeia.gif', 2, 500, 'Uma abelha trabalhadora', 'comum', 50),

-- Avatares nível 3 (Guardião - 1000 XP)
('Robo Estudante', 'Robo Estudante.gif', 3, 1000, 'Robô dedicado aos estudos', 'raro', 100),
('Cachorrão', 'Cachorrão.gif', 3, 1000, 'O melhor amigo do estudante', 'raro', 100),

-- Avatares nível 4 (Líder - 2000 XP)
('Robo Legal', 'Robo Legal.gif', 4, 2000, 'Robô com estilo único', 'epico', 200),
('Rodolfo', 'Rodolfo.gif', 4, 2000, 'Avatar lendário para líderes', 'epico', 200),

-- Avatar especial (nível máximo)
('Robozão', 'Robozão.gif', 5, 3000, 'O avatar mais poderoso de todos', 'lendario', 500);

-- Tabela para registrar avatares desbloqueados pelos alunos
CREATE TABLE IF NOT EXISTS avatares_alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    avatar_id INT NOT NULL,
    data_desbloqueio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_desbloqueio ENUM('nivel', 'compra', 'evento') DEFAULT 'nivel',
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (avatar_id) REFERENCES avatares(id) ON DELETE CASCADE,
    UNIQUE KEY unique_aluno_avatar (aluno_id, avatar_id)
);

-- Índices para melhorar performance
CREATE INDEX idx_avatar_nivel ON avatares(nivel_requerido);
CREATE INDEX idx_avatar_xp ON avatares(xp_requerido);
CREATE INDEX idx_avatar_categoria ON avatares(categoria);
CREATE INDEX idx_avatares_alunos_aluno ON avatares_alunos(aluno_id);
CREATE INDEX idx_avatares_alunos_avatar ON avatares_alunos(avatar_id);
