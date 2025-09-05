<?php
/**
 * Script de teste específico para identificar exatamente onde está o problema
 * Execute este script na HOSPEDAGEM para debugar a consulta das missões
 */

require 'db.php';

echo "<h2>🔍 Teste Detalhado da Consulta de Missões</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

// Simular um aluno_id para teste (você pode alterar este valor)
$aluno_id_teste = 1; // Altere para um ID de aluno que existe na sua hospedagem

echo "<h3>📋 Testando com Aluno ID: $aluno_id_teste</h3>";

try {
    // TESTE 1: Verificar se o aluno existe
    echo "<h4>1. Verificando se o aluno existe...</h4>";
    $stmt = $pdo->prepare("SELECT id, nome FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $aluno_id_teste]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aluno) {
        echo "<p>❌ <strong>ERRO:</strong> Aluno com ID $aluno_id_teste não encontrado!</p>";
        echo "<p>Altere a variável \$aluno_id_teste no início deste script para um ID válido.</p>";
        
        // Mostrar alunos disponíveis
        $stmt = $pdo->query("SELECT id, nome FROM alunos LIMIT 5");
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Alunos disponíveis:</strong></p>";
        echo "<ul>";
        foreach ($alunos as $a) {
            echo "<li>ID: " . $a['id'] . " - " . htmlspecialchars($a['nome']) . "</li>";
        }
        echo "</ul>";
        exit;
    }
    
    echo "<p>✅ Aluno encontrado: " . htmlspecialchars($aluno['nome']) . " (ID: " . $aluno['id'] . ")</p>";
    
    // TESTE 2: Verificar se o aluno está vinculado a uma turma
    echo "<h4>2. Verificando vínculo aluno-turma...</h4>";
    $stmt = $pdo->prepare("
        SELECT t.id AS turma_id, t.nome AS turma_nome
        FROM alunos_turmas at
        JOIN turmas t ON at.turma_id = t.id
        WHERE at.aluno_id = :aluno_id
    ");
    $stmt->execute([':aluno_id' => $aluno_id_teste]);
    $turma = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$turma) {
        echo "<p>❌ <strong>ERRO:</strong> Aluno não está vinculado a nenhuma turma!</p>";
        echo "<p>Este é provavelmente o problema. O aluno precisa estar vinculado a uma turma.</p>";
        exit;
    }
    
    echo "<p>✅ Aluno vinculado à turma: " . htmlspecialchars($turma['turma_nome']) . " (ID: " . $turma['turma_id'] . ")</p>";
    
    // TESTE 3: Verificar todas as missões no banco
    echo "<h4>3. Verificando todas as missões no banco...</h4>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes");
    $count_total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de missões no banco:</strong> " . $count_total['total'] . "</p>";
    
    if ($count_total['total'] > 0) {
        $stmt = $pdo->query("SELECT id, nome, status, turma_id FROM missoes ORDER BY id DESC LIMIT 10");
        $missoes_todas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5>Últimas 10 missões:</h5>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nome</th><th>Status</th><th>Turma ID</th></tr>";
        foreach ($missoes_todas as $missao) {
            echo "<tr>";
            echo "<td>" . $missao['id'] . "</td>";
            echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
            echo "<td>" . ($missao['status'] ? $missao['status'] : 'NULL') . "</td>";
            echo "<td>" . ($missao['turma_id'] ? $missao['turma_id'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // TESTE 4: Verificar missões com status 'ativa'
    echo "<h4>4. Verificando missões com status 'ativa'...</h4>";
    
    // Primeiro verificar se o campo status existe
    $stmt = $pdo->query("SHOW COLUMNS FROM missoes LIKE 'status'");
    $status_exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$status_exists) {
        echo "<p>❌ <strong>PROBLEMA IDENTIFICADO:</strong> Campo 'status' não existe na tabela missoes!</p>";
        echo "<p>Execute este SQL para corrigir:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "ALTER TABLE missoes ADD COLUMN status ENUM('ativa', 'inativa') DEFAULT 'ativa';\n";
        echo "UPDATE missoes SET status = 'ativa';";
        echo "</pre>";
        exit;
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE status = 'ativa'");
    $count_ativas = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Missões ativas:</strong> " . $count_ativas['total'] . "</p>";
    
    // TESTE 5: Verificar missões para a turma específica
    echo "<h4>5. Verificando missões para a turma específica...</h4>";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM missoes 
        WHERE status = 'ativa' 
          AND (turma_id = :turma_id OR turma_id IS NULL)
    ");
    $stmt->execute([':turma_id' => $turma['turma_id']]);
    $count_turma = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Missões ativas para esta turma:</strong> " . $count_turma['total'] . "</p>";
    
    if ($count_turma['total'] > 0) {
        $stmt = $pdo->prepare("
            SELECT id, nome, turma_id 
            FROM missoes 
            WHERE status = 'ativa' 
              AND (turma_id = :turma_id OR turma_id IS NULL)
            ORDER BY id DESC
        ");
        $stmt->execute([':turma_id' => $turma['turma_id']]);
        $missoes_turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5>Missões disponíveis para esta turma:</h5>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nome</th><th>Turma ID</th></tr>";
        foreach ($missoes_turma as $missao) {
            echo "<tr>";
            echo "<td>" . $missao['id'] . "</td>";
            echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
            echo "<td>" . ($missao['turma_id'] ? $missao['turma_id'] : 'Todas') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // TESTE 6: Verificar tabela solicitacoes_missoes
    echo "<h4>6. Verificando tabela solicitacoes_missoes...</h4>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'solicitacoes_missoes'");
    $tabela_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tabela_existe) {
        echo "<p>⚠️ <strong>AVISO:</strong> Tabela 'solicitacoes_missoes' não existe!</p>";
        echo "<p>Isso pode causar erro na consulta. Execute este SQL:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "CREATE TABLE solicitacoes_missoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    missao_id INT NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL
);";
        echo "</pre>";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM solicitacoes_missoes WHERE aluno_id = :aluno_id");
        $stmt->execute([':aluno_id' => $aluno_id_teste]);
        $count_solicitacoes = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Solicitações deste aluno:</strong> " . $count_solicitacoes['total'] . "</p>";
    }
    
    // TESTE 7: Executar a consulta completa
    echo "<h4>7. Executando a consulta completa do missoes.php...</h4>";
    
    $sql_completo = "
        SELECT m.*
        FROM missoes m
        WHERE m.status = 'ativa'
          AND (m.turma_id = :turma_id OR m.turma_id IS NULL)
          AND NOT EXISTS (
              SELECT 1 FROM solicitacoes_missoes sm
              WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
          )
    ";
    
    echo "<p><strong>SQL executado:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>" . htmlspecialchars($sql_completo) . "</pre>";
    
    try {
        $stmt = $pdo->prepare($sql_completo);
        $stmt->execute([':turma_id' => $turma['turma_id'], ':aluno_id' => $aluno_id_teste]);
        $missoes_finais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Resultado final:</strong> " . count($missoes_finais) . " missões encontradas</p>";
        
        if (count($missoes_finais) > 0) {
            echo "<h5>✅ Missões que aparecerão para o aluno:</h5>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #d4edda;'><th>ID</th><th>Nome</th><th>XP</th><th>Moedas</th></tr>";
            foreach ($missoes_finais as $missao) {
                echo "<tr>";
                echo "<td>" . $missao['id'] . "</td>";
                echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
                echo "<td>" . $missao['xp'] . "</td>";
                echo "<td>" . $missao['moedas'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p>✅ <strong>SUCESSO:</strong> As missões estão aparecendo corretamente!</p>";
        } else {
            echo "<h5>❌ Nenhuma missão encontrada. Possíveis causas:</h5>";
            echo "<ul>";
            echo "<li>Todas as missões já foram realizadas pelo aluno</li>";
            echo "<li>Não há missões ativas para esta turma</li>";
            echo "<li>Problema na consulta SQL</li>";
            echo "</ul>";
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ <strong>ERRO na consulta:</strong> " . $e->getMessage() . "</p>";
    }
    
    // TESTE 8: Resumo e soluções
    echo "<h4>8. 📋 Resumo e Soluções:</h4>";
    
    if ($count_total['total'] == 0) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h5>❌ PROBLEMA: Nenhuma missão no banco</h5>";
        echo "<p>Solução: Crie missões pelo painel da secretaria</p>";
        echo "</div>";
    } elseif ($count_ativas['total'] == 0) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h5>⚠️ PROBLEMA: Nenhuma missão ativa</h5>";
        echo "<p>Solução: Execute: <code>UPDATE missoes SET status = 'ativa';</code></p>";
        echo "</div>";
    } elseif ($count_turma['total'] == 0) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
        echo "<h5>ℹ️ PROBLEMA: Nenhuma missão para esta turma</h5>";
        echo "<p>Solução: Vincule missões à turma ou crie missões para todas as turmas</p>";
        echo "</div>";
    } elseif (count($missoes_finais) == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h5>✅ Missões existem mas aluno já realizou todas</h5>";
        echo "<p>Isso é normal - o aluno já completou todas as missões disponíveis</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h5>✅ TUDO FUNCIONANDO CORRETAMENTE!</h5>";
        echo "<p>As missões estão aparecendo normalmente para o aluno</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante o teste:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
