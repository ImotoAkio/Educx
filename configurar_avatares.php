<?php
require 'db.php';

echo "=== CONFIGURANDO SISTEMA DE AVATARES ===\n\n";

try {
    // Criar tabela avatares
    echo "Criando tabela 'avatares'...\n";
    $sql = "CREATE TABLE IF NOT EXISTS avatares (
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
    )";
    $pdo->exec($sql);
    echo "✅ Tabela 'avatares' criada com sucesso!\n";
    
    // Criar tabela avatares_alunos
    echo "\nCriando tabela 'avatares_alunos'...\n";
    $sql = "CREATE TABLE IF NOT EXISTS avatares_alunos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        aluno_id INT NOT NULL,
        avatar_id INT NOT NULL,
        data_desbloqueio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        metodo_desbloqueio ENUM('nivel', 'compra', 'evento') DEFAULT 'nivel',
        FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
        FOREIGN KEY (avatar_id) REFERENCES avatares(id) ON DELETE CASCADE,
        UNIQUE KEY unique_aluno_avatar (aluno_id, avatar_id)
    )";
    $pdo->exec($sql);
    echo "✅ Tabela 'avatares_alunos' criada com sucesso!\n";
    
    // Verificar se já existem avatares
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM avatares");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] == 0) {
        echo "\nInserindo avatares no banco de dados...\n";
        
        $avatares = [
            ['Default', 'default.gif', 1, 0, 'Avatar padrão para iniciantes', 'comum', 0],
            ['Gatito', 'Gatito.gif', 1, 0, 'Um gatinho fofo para começar sua jornada', 'comum', 0],
            ['Ratatui', 'Ratatui.gif', 2, 500, 'Um rato aventureiro para exploradores', 'comum', 50],
            ['Abeia', 'Abeia.gif', 2, 500, 'Uma abelha trabalhadora', 'comum', 50],
            ['Robo Estudante', 'Robo Estudante.gif', 3, 1000, 'Robô dedicado aos estudos', 'raro', 100],
            ['Cachorrão', 'Cachorrão.gif', 3, 1000, 'O melhor amigo do estudante', 'raro', 100],
            ['Robo Legal', 'Robo Legal.gif', 4, 2000, 'Robô com estilo único', 'epico', 200],
            ['Rodolfo', 'Rodolfo.gif', 4, 2000, 'Avatar lendário para líderes', 'epico', 200],
            ['Robozão', 'Robozão.gif', 5, 3000, 'O avatar mais poderoso de todos', 'lendario', 500]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO avatares (nome, arquivo, nivel_requerido, xp_requerido, descricao, categoria, preco_moedas) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($avatares as $avatar) {
            $stmt->execute($avatar);
            echo "✅ Avatar '{$avatar[0]}' inserido (Nível {$avatar[2]}, {$avatar[3]} XP)\n";
        }
    } else {
        echo "✅ Avatares já existem no banco de dados.\n";
    }
    
    // Criar índices
    echo "\nCriando índices para melhorar performance...\n";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_avatar_nivel ON avatares(nivel_requerido)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_avatar_xp ON avatares(xp_requerido)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_avatar_categoria ON avatares(categoria)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_avatares_alunos_aluno ON avatares_alunos(aluno_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_avatares_alunos_avatar ON avatares_alunos(avatar_id)");
    echo "✅ Índices criados com sucesso!\n";
    
    echo "\n🎉 Sistema de avatares configurado com sucesso!\n";
    echo "\nNíveis de desbloqueio:\n";
    echo "- Nível 1 (0 XP): Default, Gatito\n";
    echo "- Nível 2 (500 XP): Ratatui, Abeia\n";
    echo "- Nível 3 (1000 XP): Robo Estudante, Cachorrão\n";
    echo "- Nível 4 (2000 XP): Robo Legal, Rodolfo\n";
    echo "- Nível 5 (3000 XP): Robozão\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
