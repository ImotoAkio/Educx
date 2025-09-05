<?php
// Verificar estrutura das tabelas relacionadas
require 'db.php';

echo "<h2>Verificação das Tabelas</h2>";

// Verificar tabela turmas
echo "<h3>1. Tabela 'turmas':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE turmas");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar tabela alunos
echo "<h3>2. Tabela 'alunos':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE alunos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar tabela alunos_turmas
echo "<h3>3. Tabela 'alunos_turmas':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE alunos_turmas");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar tabela professores
echo "<h3>4. Tabela 'professores':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE professores");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar tabela turmas_professores
echo "<h3>5. Tabela 'turmas_professores':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE turmas_professores");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// Testar consulta específica
echo "<h3>6. Teste de Consulta (Turma ID 20):</h3>";
try {
    $turma_id = 20;
    
    // Buscar turma
    $sql_turma = "SELECT nome, descricao, ano_letivo FROM turmas WHERE id = :turma_id";
    $stmt_turma = $pdo->prepare($sql_turma);
    $stmt_turma->execute(['turma_id' => $turma_id]);
    $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Turma:</strong> " . ($turma ? $turma['nome'] : 'Não encontrada') . "</p>";
    
    // Buscar alunos
    $sql_alunos = "
        SELECT 
            a.id AS aluno_id,
            a.nome AS aluno_nome,
            a.moedas,
            a.xp_total,
            a.nivel
        FROM alunos a
        JOIN alunos_turmas at ON a.id = at.aluno_id
        WHERE at.turma_id = :turma_id
        ORDER BY a.nome ASC
    ";
    
    $stmt_alunos = $pdo->prepare($sql_alunos);
    $stmt_alunos->execute(['turma_id' => $turma_id]);
    $alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Alunos encontrados:</strong> " . count($alunos) . "</p>";
    
    // Buscar professores
    $sql_professores = "
        SELECT 
            p.id AS professor_id,
            p.nome AS professor_nome
        FROM professores p
        JOIN turmas_professores tp ON p.id = tp.professor_id
        WHERE tp.turma_id = :turma_id
        ORDER BY p.nome ASC
    ";
    
    $stmt_professores = $pdo->prepare($sql_professores);
    $stmt_professores->execute(['turma_id' => $turma_id]);
    $professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Professores encontrados:</strong> " . count($professores) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na consulta: " . $e->getMessage() . "</p>";
}

echo "<p><a href='painel/secretaria/paginas/turmas.php'>← Voltar para Turmas</a></p>";
?>

