<?php
/**
 * Script para migrar dados da hospedagem para o banco local atualizado
 * Execute este script APÓS fazer backup da hospedagem e ter o arquivo JSON
 */

require 'db.php'; // Inclui a conexão com o banco de dados local

// Verificar se o arquivo de backup existe
$backup_file = $_GET['arquivo'] ?? 'backup_hospedagem.json';

if (!file_exists($backup_file)) {
    die("<h3>❌ Erro: Arquivo de backup não encontrado!</h3>
         <p>Certifique-se de que o arquivo '$backup_file' está na pasta do projeto.</p>
         <p>Ou especifique o arquivo via URL: ?arquivo=nome_do_arquivo.json</p>");
}

echo "<h2>Migração de Dados da Hospedagem para Local</h2>";
echo "<p>Arquivo de backup: $backup_file</p>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // Carregar dados do backup
    $backup_data = json_decode(file_get_contents($backup_file), true);
    
    if (!$backup_data) {
        die("<h3>❌ Erro: Não foi possível carregar o arquivo de backup!</h3>");
    }
    
    echo "<h3>Dados carregados do backup:</h3>";
    echo "<ul>";
    foreach ($backup_data as $table => $data) {
        $count = count($data);
        echo "<li>$table: $count registros</li>";
    }
    echo "</ul>";
    
    // Verificar se o usuário confirma a migração
    if (!isset($_GET['confirmar'])) {
        echo "<h3>⚠ ATENÇÃO!</h3>";
        echo "<p>Este processo irá:</p>";
        echo "<ul>";
        echo "<li>Limpar os dados atuais das tabelas</li>";
        echo "<li>Inserir os dados do backup da hospedagem</li>";
        echo "<li>Manter a estrutura atualizada do banco</li>";
        echo "</ul>";
        echo "<p><strong>Certifique-se de ter feito backup do banco local antes!</strong></p>";
        echo "<a href='?arquivo=$backup_file&confirmar=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>CONFIRMAR MIGRAÇÃO</a>";
        exit;
    }
    
    echo "<h3>Iniciando migração...</h3>";
    
    // Desabilitar verificações de chave estrangeira temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $total_migrados = 0;
    
    foreach ($backup_data as $table => $data) {
        echo "<h4>Migrando tabela: $table</h4>";
        
        // Limpar tabela atual
        $pdo->exec("TRUNCATE TABLE $table");
        echo "<p>✓ Tabela limpa</p>";
        
        if (count($data) > 0) {
            // Preparar inserção
            $first_row = $data[0];
            $columns = array_keys($first_row);
            $placeholders = ':' . implode(', :', $columns);
            $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            
            $migrados = 0;
            foreach ($data as $row) {
                try {
                    $stmt->execute($row);
                    $migrados++;
                } catch (PDOException $e) {
                    echo "<p>⚠ Erro ao inserir registro: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<p>✓ $migrados registros migrados</p>";
            $total_migrados += $migrados;
        } else {
            echo "<p>⚠ Tabela vazia no backup</p>";
        }
    }
    
    // Reabilitar verificações de chave estrangeira
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<h3>✅ Migração concluída!</h3>";
    echo "<p>Total de registros migrados: $total_migrados</p>";
    
    // Verificar integridade dos dados
    echo "<h3>Verificação de Integridade:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tabela</th><th>Registros</th></tr>";
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<tr><td>$table</td><td>$count</td></tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Próximos passos:</h3>";
    echo "<ol>";
    echo "<li>Teste todas as funcionalidades do sistema</li>";
    echo "<li>Verifique se os dados estão corretos</li>";
    echo "<li>Se tudo estiver OK, execute o script de atualização na hospedagem</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante a migração:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Reabilitar verificações de chave estrangeira em caso de erro
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e2) {
        // Ignorar erro de reabilitação
    }
}
?>
