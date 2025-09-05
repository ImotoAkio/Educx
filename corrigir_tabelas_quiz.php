<?php
require 'db.php';

echo "<h2>Verificando e corrigindo tabelas de Quiz</h2>";

try {
    // Verificar se a tabela quizzes existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'quizzes'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Tabela 'quizzes' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE quizzes (
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
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'quizzes' criada com sucesso!</p>";
    } else {
        echo "<p>✅ Tabela 'quizzes' já existe.</p>";
        
        // Verificar se a coluna id tem AUTO_INCREMENT
        $stmt = $pdo->query("DESCRIBE quizzes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $autoIncrement = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] == 'id' && strpos($column['Extra'], 'auto_increment') !== false) {
                $autoIncrement = true;
                break;
            }
        }
        
        if (!$autoIncrement) {
            echo "<p>⚠️ Coluna 'id' não tem AUTO_INCREMENT. Corrigindo...</p>";
            $pdo->exec("ALTER TABLE quizzes MODIFY id INT AUTO_INCREMENT");
            echo "<p>✅ AUTO_INCREMENT corrigido!</p>";
        } else {
            echo "<p>✅ AUTO_INCREMENT já está configurado corretamente.</p>";
        }
    }
    
    // Verificar se a tabela perguntas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'perguntas'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Tabela 'perguntas' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE perguntas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            texto TEXT NOT NULL,
            ordem INT DEFAULT 0,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'perguntas' criada com sucesso!</p>";
    } else {
        echo "<p>✅ Tabela 'perguntas' já existe.</p>";
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("DESCRIBE perguntas");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $autoIncrement = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] == 'id' && strpos($column['Extra'], 'auto_increment') !== false) {
                $autoIncrement = true;
                break;
            }
        }
        
        if (!$autoIncrement) {
            echo "<p>⚠️ Coluna 'id' não tem AUTO_INCREMENT. Corrigindo...</p>";
            $pdo->exec("ALTER TABLE perguntas MODIFY id INT AUTO_INCREMENT");
            echo "<p>✅ AUTO_INCREMENT corrigido!</p>";
        }
    }
    
    // Verificar se a tabela alternativas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'alternativas'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Tabela 'alternativas' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE alternativas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pergunta_id INT NOT NULL,
            texto TEXT NOT NULL,
            correta BOOLEAN DEFAULT FALSE,
            ordem INT DEFAULT 0,
            FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'alternativas' criada com sucesso!</p>";
    } else {
        echo "<p>✅ Tabela 'alternativas' já existe.</p>";
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("DESCRIBE alternativas");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $autoIncrement = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] == 'id' && strpos($column['Extra'], 'auto_increment') !== false) {
                $autoIncrement = true;
                break;
            }
        }
        
        if (!$autoIncrement) {
            echo "<p>⚠️ Coluna 'id' não tem AUTO_INCREMENT. Corrigindo...</p>";
            $pdo->exec("ALTER TABLE alternativas MODIFY id INT AUTO_INCREMENT");
            echo "<p>✅ AUTO_INCREMENT corrigido!</p>";
        }
    }
    
    // Verificar se a tabela quizzes_finalizados existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'quizzes_finalizados'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Tabela 'quizzes_finalizados' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE quizzes_finalizados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            quiz_id INT NOT NULL,
            pontuacao INT DEFAULT 0,
            data_finalizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_aluno_quiz (aluno_id, quiz_id)
        )";
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'quizzes_finalizados' criada com sucesso!</p>";
    } else {
        echo "<p>✅ Tabela 'quizzes_finalizados' já existe.</p>";
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("DESCRIBE quizzes_finalizados");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $autoIncrement = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] == 'id' && strpos($column['Extra'], 'auto_increment') !== false) {
                $autoIncrement = true;
                break;
            }
        }
        
        if (!$autoIncrement) {
            echo "<p>⚠️ Coluna 'id' não tem AUTO_INCREMENT. Corrigindo...</p>";
            $pdo->exec("ALTER TABLE quizzes_finalizados MODIFY id INT AUTO_INCREMENT");
            echo "<p>✅ AUTO_INCREMENT corrigido!</p>";
        }
    }
    
    echo "<h3>🎉 Verificação concluída! Todas as tabelas estão corretas.</h3>";
    echo "<p><a href='painel/professor/paginas/criar_quiz.php'>← Voltar para Criar Quiz</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
