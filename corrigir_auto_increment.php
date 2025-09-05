<?php
require 'db.php';

echo "=== CORRIGINDO AUTO_INCREMENT DAS TABELAS ===\n\n";

try {
    // Corrigir AUTO_INCREMENT da tabela quizzes
    echo "Corrigindo AUTO_INCREMENT da tabela 'quizzes'...\n";
    $pdo->exec("ALTER TABLE quizzes MODIFY id INT AUTO_INCREMENT");
    echo "✅ AUTO_INCREMENT da tabela 'quizzes' corrigido!\n";
    
    // Verificar se foi corrigido
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'quizzes'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "AUTO_INCREMENT atual da tabela 'quizzes': {$status['Auto_increment']}\n";
    
    // Corrigir AUTO_INCREMENT da tabela perguntas
    echo "\nCorrigindo AUTO_INCREMENT da tabela 'perguntas'...\n";
    $pdo->exec("ALTER TABLE perguntas MODIFY id INT AUTO_INCREMENT");
    echo "✅ AUTO_INCREMENT da tabela 'perguntas' corrigido!\n";
    
    // Verificar se foi corrigido
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'perguntas'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "AUTO_INCREMENT atual da tabela 'perguntas': {$status['Auto_increment']}\n";
    
    echo "\n🎉 Correção concluída! Agora você pode criar quizzes normalmente.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
