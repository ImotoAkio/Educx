<?php
/**
 * Script para atualizar a estrutura do banco de dados na hospedagem
 * Execute este script na hospedagem APÓS testar a migração local
 */

// Configurações do banco da hospedagem
$host = 'localhost'; // Altere conforme sua hospedagem
$dbname = 'coinz'; // Nome do banco na hospedagem
$username = 'root'; // Usuário da hospedagem
$password = ''; // Senha da hospedagem

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Atualização da Estrutura do Banco - Hospedagem</h2>";
    echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";
    
    // Verificar se o usuário confirma a atualização
    if (!isset($_GET['confirmar'])) {
        echo "<h3>⚠ ATENÇÃO!</h3>";
        echo "<p>Este processo irá:</p>";
        echo "<ul>";
        echo "<li>Atualizar a estrutura das tabelas</li>";
        echo "<li>Adicionar novas tabelas se necessário</li>";
        echo "<li>Corrigir índices e chaves estrangeiras</li>";
        echo "</ul>";
        echo "<p><strong>Certifique-se de ter feito backup completo antes!</strong></p>";
        echo "<a href='?confirmar=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>CONFIRMAR ATUALIZAÇÃO</a>";
        exit;
    }
    
    echo "<h3>Iniciando atualização da estrutura...</h3>";
    
    // 1. Criar/Atualizar tabelas de Quiz
    echo "<h4>1. Atualizando tabelas de Quiz...</h4>";
    
    // Tabela de quizzes
    $sql_quizzes = "
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
    )";
    $pdo->exec($sql_quizzes);
    echo "<p>✓ Tabela 'quizzes' criada/atualizada</p>";
    
    // Tabela de perguntas
    $sql_perguntas = "
    CREATE TABLE IF NOT EXISTS perguntas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,
        texto TEXT NOT NULL,
        ordem INT DEFAULT 0,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_perguntas);
    echo "<p>✓ Tabela 'perguntas' criada/atualizada</p>";
    
    // Tabela de alternativas
    $sql_alternativas = "
    CREATE TABLE IF NOT EXISTS alternativas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pergunta_id INT NOT NULL,
        texto TEXT NOT NULL,
        correta BOOLEAN DEFAULT FALSE,
        ordem INT DEFAULT 0,
        FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_alternativas);
    echo "<p>✓ Tabela 'alternativas' criada/atualizada</p>";
    
    // Tabela de quizzes finalizados
    $sql_quizzes_finalizados = "
    CREATE TABLE IF NOT EXISTS quizzes_finalizados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        aluno_id INT NOT NULL,
        quiz_id INT NOT NULL,
        pontuacao INT DEFAULT 0,
        data_finalizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
        UNIQUE KEY unique_aluno_quiz (aluno_id, quiz_id)
    )";
    $pdo->exec($sql_quizzes_finalizados);
    echo "<p>✓ Tabela 'quizzes_finalizados' criada/atualizada</p>";
    
    // Tabela de respostas dos alunos
    $sql_respostas = "
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
    )";
    $pdo->exec($sql_respostas);
    echo "<p>✓ Tabela 'respostas_quiz' criada/atualizada</p>";
    
    // 2. Criar/Atualizar tabela de histórico de moedas
    echo "<h4>2. Atualizando tabela de histórico de moedas...</h4>";
    
    $sql_historico = "
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
    )";
    $pdo->exec($sql_historico);
    echo "<p>✓ Tabela 'historico_moedas' criada/atualizada</p>";
    
    // 3. Criar índices para melhorar performance
    echo "<h4>3. Criando índices...</h4>";
    
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_quiz_criador ON quizzes(criador_id)",
        "CREATE INDEX IF NOT EXISTS idx_quiz_turma ON quizzes(turma_id)",
        "CREATE INDEX IF NOT EXISTS idx_pergunta_quiz ON perguntas(quiz_id)",
        "CREATE INDEX IF NOT EXISTS idx_alternativa_pergunta ON alternativas(pergunta_id)",
        "CREATE INDEX IF NOT EXISTS idx_quiz_finalizado_aluno ON quizzes_finalizados(aluno_id)",
        "CREATE INDEX IF NOT EXISTS idx_quiz_finalizado_quiz ON quizzes_finalizados(quiz_id)",
        "CREATE INDEX IF NOT EXISTS idx_resposta_aluno ON respostas_quiz(aluno_id)",
        "CREATE INDEX IF NOT EXISTS idx_resposta_quiz ON respostas_quiz(quiz_id)",
        "CREATE INDEX IF NOT EXISTS idx_historico_aluno ON historico_moedas(aluno_id)",
        "CREATE INDEX IF NOT EXISTS idx_historico_professor ON historico_moedas(professor_id)",
        "CREATE INDEX IF NOT EXISTS idx_historico_data ON historico_moedas(data)"
    ];
    
    foreach ($indices as $indice) {
        try {
            $pdo->exec($indice);
            echo "<p>✓ Índice criado</p>";
        } catch (PDOException $e) {
            echo "<p>⚠ Índice já existe ou erro: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Verificar estrutura final
    echo "<h4>4. Verificando estrutura final...</h4>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>✅ Atualização concluída!</h3>";
    echo "<p>Tabelas no banco:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<li>$table: $count registros</li>";
    }
    echo "</ul>";
    
    echo "<h3>Próximos passos:</h3>";
    echo "<ol>";
    echo "<li>Faça upload do arquivo de backup da hospedagem</li>";
    echo "<li>Execute o script de migração de dados</li>";
    echo "<li>Teste todas as funcionalidades</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante a atualização:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
