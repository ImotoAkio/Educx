<?php
/**
 * Script simples para testar se a tabela missoes tem dados
 * Execute este script na hospedagem para verificar
 */

require 'db.php';

echo "<h2>🔍 Teste Simples da Tabela Missões</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar se a tabela existe
    echo "<h3>1. Verificando se a tabela 'missoes' existe...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'missoes'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'missoes' existe</p>";
    } else {
        echo "<p>❌ Tabela 'missoes' NÃO existe!</p>";
        echo "<p>Execute o SQL de criação da tabela primeiro.</p>";
        exit;
    }
    
    // 2. Contar registros
    echo "<h3>2. Contando registros na tabela...</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de missões:</strong> " . $count['total'] . "</p>";
    
    if ($count['total'] == 0) {
        echo "<p>❌ <strong>PROBLEMA:</strong> Nenhuma missão na tabela!</p>";
        echo "<p>Solução: Execute este SQL para inserir missões de exemplo:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "INSERT INTO missoes (nome, descricao, xp, moedas, status) VALUES
('Missão de Boas-vindas', 'Complete seu primeiro login no sistema', 50, 10, 'ativa'),
('Explorador Digital', 'Navegue por todas as seções do sistema', 100, 25, 'ativa'),
('Desafio de Conhecimento', 'Responda corretamente 5 perguntas em sequência', 200, 50, 'ativa');";
        echo "</pre>";
        exit;
    }
    
    // 3. Mostrar algumas missões
    echo "<h3>3. Mostrando missões existentes...</h3>";
    $stmt = $pdo->query("SELECT id, nome, descricao, xp, moedas, status FROM missoes ORDER BY id DESC LIMIT 5");
    $missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nome</th><th>Descrição</th><th>XP</th><th>Moedas</th><th>Status</th></tr>";
    foreach ($missoes as $missao) {
        echo "<tr>";
        echo "<td>" . $missao['id'] . "</td>";
        echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($missao['descricao'], 0, 50)) . "...</td>";
        echo "<td>" . $missao['xp'] . "</td>";
        echo "<td>" . $missao['moedas'] . "</td>";
        echo "<td>" . ($missao['status'] ? $missao['status'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Testar consulta simples
    echo "<h3>4. Testando consulta simples...</h3>";
    $stmt = $pdo->query("SELECT * FROM missoes WHERE status = 'ativa' OR status IS NULL");
    $missoes_ativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>Missões ativas ou sem status:</strong> " . count($missoes_ativas) . "</p>";
    
    // 5. Verificar estrutura da tabela
    echo "<h3>5. Estrutura da tabela...</h3>";
    $stmt = $pdo->query("DESCRIBE missoes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . ($coluna['Default'] ? $coluna['Default'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 6. Conclusão
    echo "<h3>6. 🎯 Conclusão</h3>";
    if ($count['total'] > 0 && count($missoes_ativas) > 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ TUDO OK!</h4>";
        echo "<p>A tabela 'missoes' tem dados e está funcionando.</p>";
        echo "<p>Se as missões não aparecem na página missoes.php, o problema pode ser:</p>";
        echo "<ul>";
        echo "<li>Aluno não está vinculado a uma turma</li>";
        echo "<li>Erro na consulta da página</li>";
        echo "<li>Problema de conexão com o banco</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ PROBLEMA ENCONTRADO</h4>";
        echo "<p>Execute os SQLs sugeridos acima para corrigir.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante o teste:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
