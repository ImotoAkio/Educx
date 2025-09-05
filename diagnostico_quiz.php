<?php
require 'db.php';

echo "=== DIAGNÓSTICO DAS TABELAS DE QUIZ ===\n\n";

try {
    // Verificar se a tabela quizzes existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'quizzes'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela 'quizzes' não existe.\n";
        echo "Criando tabela...\n";
        
        $sql = "CREATE TABLE quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            descricao TEXT,
            criador_id INT NOT NULL,
            turma_id INT NOT NULL,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('ativo', 'inativo') DEFAULT 'ativo'
        )";
        $pdo->exec($sql);
        echo "✅ Tabela 'quizzes' criada com sucesso!\n";
    } else {
        echo "✅ Tabela 'quizzes' existe.\n";
        
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE quizzes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Estrutura da tabela quizzes:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']} {$column['Extra']}\n";
        }
        
        // Verificar se há dados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total de registros: {$count['total']}\n";
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'quizzes'");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "AUTO_INCREMENT atual: {$status['Auto_increment']}\n";
    }
    
    // Verificar se a tabela perguntas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'perguntas'");
    if ($stmt->rowCount() == 0) {
        echo "\n❌ Tabela 'perguntas' não existe.\n";
        echo "Criando tabela...\n";
        
        $sql = "CREATE TABLE perguntas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            texto TEXT NOT NULL,
            ordem INT DEFAULT 0
        )";
        $pdo->exec($sql);
        echo "✅ Tabela 'perguntas' criada com sucesso!\n";
    } else {
        echo "\n✅ Tabela 'perguntas' existe.\n";
        
        // Verificar estrutura
        $stmt = $pdo->query("DESCRIBE perguntas");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Estrutura da tabela perguntas:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']} {$column['Extra']}\n";
        }
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'perguntas'");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "AUTO_INCREMENT atual: {$status['Auto_increment']}\n";
    }
    
    // Verificar se a tabela alternativas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'alternativas'");
    if ($stmt->rowCount() == 0) {
        echo "\n❌ Tabela 'alternativas' não existe.\n";
        echo "Criando tabela...\n";
        
        $sql = "CREATE TABLE alternativas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pergunta_id INT NOT NULL,
            texto TEXT NOT NULL,
            correta BOOLEAN DEFAULT FALSE,
            ordem INT DEFAULT 0
        )";
        $pdo->exec($sql);
        echo "✅ Tabela 'alternativas' criada com sucesso!\n";
    } else {
        echo "\n✅ Tabela 'alternativas' existe.\n";
        
        // Verificar estrutura
        $stmt = $pdo->query("DESCRIBE alternativas");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Estrutura da tabela alternativas:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']} {$column['Extra']}\n";
        }
        
        // Verificar AUTO_INCREMENT
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'alternativas'");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "AUTO_INCREMENT atual: {$status['Auto_increment']}\n";
    }
    
    echo "\n🎉 Diagnóstico concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
