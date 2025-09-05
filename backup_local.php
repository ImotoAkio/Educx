<?php
/**
 * Script para fazer backup completo do banco de dados local
 * Execute este script no seu ambiente local ANTES de fazer qualquer alteração
 */

require 'db.php'; // Inclui a conexão com o banco de dados local

try {
    echo "<h2>Backup do Banco de Dados Local</h2>";
    echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";
    
    // Lista todas as tabelas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tabelas encontradas:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Backup de cada tabela
    $backup_data = [];
    
    foreach ($tables as $table) {
        echo "<h4>Backup da tabela: $table</h4>";
        
        // Contar registros
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<p>Registros encontrados: $count</p>";
        
        if ($count > 0) {
            // Buscar todos os dados
            $stmt = $pdo->query("SELECT * FROM $table");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backup_data[$table] = $data;
            
            echo "<p>✓ Dados copiados com sucesso</p>";
        } else {
            echo "<p>⚠ Tabela vazia</p>";
        }
    }
    
    // Salvar backup em arquivo JSON
    $backup_file = 'backup_local_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
    
    echo "<h3>✓ Backup salvo em: $backup_file</h3>";
    echo "<p><strong>IMPORTANTE:</strong> Mantenha este arquivo seguro!</p>";
    
    // Mostrar resumo
    echo "<h3>Resumo do Backup:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tabela</th><th>Registros</th></tr>";
    
    foreach ($tables as $table) {
        $count = isset($backup_data[$table]) ? count($backup_data[$table]) : 0;
        echo "<tr><td>$table</td><td>$count</td></tr>";
    }
    
    echo "</table>";
    
    // Mostrar estrutura das tabelas
    echo "<h3>Estrutura das Tabelas:</h3>";
    foreach ($tables as $table) {
        echo "<h4>Tabela: $table</h4>";
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
