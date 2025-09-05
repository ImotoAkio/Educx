<?php
/**
 * Script para verificar e corrigir a estrutura das tabelas de relacionamento
 * Execute este script para resolver o erro de coluna não encontrada
 */

require 'db.php';

echo "<h2>🔧 Correção das Tabelas de Relacionamento</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar se a tabela alunos_turmas existe
    echo "<h3>1. Verificando tabela 'alunos_turmas'...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'alunos_turmas'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'alunos_turmas' existe</p>";
        
        // Verificar estrutura atual
        $stmt = $pdo->query("DESCRIBE alunos_turmas");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Estrutura atual:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "<td>" . $coluna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se tem coluna id
        $tem_id = false;
        foreach ($colunas as $coluna) {
            if ($coluna['Field'] === 'id') {
                $tem_id = true;
                break;
            }
        }
        
        if (!$tem_id) {
            echo "<p>⚠️ Coluna 'id' não encontrada. Adicionando...</p>";
            $pdo->exec("ALTER TABLE alunos_turmas ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
            echo "<p>✅ Coluna 'id' adicionada com sucesso!</p>";
        } else {
            echo "<p>✅ Coluna 'id' já existe</p>";
        }
        
    } else {
        echo "<p>❌ Tabela 'alunos_turmas' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE alunos_turmas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aluno_id INT NOT NULL,
            turma_id INT NOT NULL,
            data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
            UNIQUE KEY unique_aluno_turma (aluno_id, turma_id)
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'alunos_turmas' criada com sucesso!</p>";
    }
    
    // 2. Verificar se a tabela turmas_professores existe
    echo "<h3>2. Verificando tabela 'turmas_professores'...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'turmas_professores'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'turmas_professores' existe</p>";
        
        // Verificar estrutura atual
        $stmt = $pdo->query("DESCRIBE turmas_professores");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Estrutura atual:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "<td>" . $coluna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se tem coluna id
        $tem_id = false;
        foreach ($colunas as $coluna) {
            if ($coluna['Field'] === 'id') {
                $tem_id = true;
                break;
            }
        }
        
        if (!$tem_id) {
            echo "<p>⚠️ Coluna 'id' não encontrada. Adicionando...</p>";
            $pdo->exec("ALTER TABLE turmas_professores ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
            echo "<p>✅ Coluna 'id' adicionada com sucesso!</p>";
        } else {
            echo "<p>✅ Coluna 'id' já existe</p>";
        }
        
    } else {
        echo "<p>❌ Tabela 'turmas_professores' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE turmas_professores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            professor_id INT NOT NULL,
            turma_id INT NOT NULL,
            data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
            UNIQUE KEY unique_professor_turma (professor_id, turma_id)
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'turmas_professores' criada com sucesso!</p>";
    }
    
    // 3. Criar índices para melhorar performance
    echo "<h3>3. Criando índices para performance...</h3>";
    
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_alunos_turmas_aluno ON alunos_turmas(aluno_id)",
        "CREATE INDEX IF NOT EXISTS idx_alunos_turmas_turma ON alunos_turmas(turma_id)",
        "CREATE INDEX IF NOT EXISTS idx_turmas_professores_professor ON turmas_professores(professor_id)",
        "CREATE INDEX IF NOT EXISTS idx_turmas_professores_turma ON turmas_professores(turma_id)"
    ];
    
    foreach ($indices as $indice) {
        try {
            $pdo->exec($indice);
            echo "<p>✅ Índice criado</p>";
        } catch (PDOException $e) {
            echo "<p>⚠️ Índice já existe ou erro: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Verificar estrutura final
    echo "<h3>4. Verificação final...</h3>";
    
    $tabelas = ['alunos_turmas', 'turmas_professores'];
    
    foreach ($tabelas as $tabela) {
        echo "<h4>Tabela: $tabela</h4>";
        $stmt = $pdo->query("DESCRIBE $tabela");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "<td>" . $coluna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Registros: " . $count['total'] . "</p><br>";
    }
    
    echo "<h3>🎉 Correção Concluída!</h3>";
    echo "<p>As tabelas de relacionamento foram verificadas e corrigidas.</p>";
    echo "<p>Agora você pode usar o sistema de vínculos normalmente.</p>";
    
    echo "<h3>Próximos passos:</h3>";
    echo "<ol>";
    echo "<li>Teste vincular um aluno a uma turma</li>";
    echo "<li>Teste vincular um professor a uma turma</li>";
    echo "<li>Verifique se os dados aparecem corretamente</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante a correção:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
