<?php
/**
 * Script para diagnosticar diferenças entre ambiente local e hospedagem
 * Execute este script na HOSPEDAGEM para identificar o problema
 */

require 'db.php';

echo "<h2>🔍 Diagnóstico Específico da Hospedagem</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>⚠️ Este script deve ser executado na HOSPEDAGEM</strong></p>";

try {
    // 1. Verificar estrutura da tabela missoes
    echo "<h3>1. Estrutura da tabela 'missoes' na hospedagem:</h3>";
    
    $stmt = $pdo->query("DESCRIBE missoes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>" . $coluna['Field'] . "</strong></td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . ($coluna['Default'] ? $coluna['Default'] : 'NULL') . "</td>";
        echo "<td>" . $coluna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar se há campo 'status'
    $tem_status = false;
    $tem_turma_id = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'status') $tem_status = true;
        if ($coluna['Field'] === 'turma_id') $tem_turma_id = true;
    }
    
    echo "<h3>2. Campos críticos:</h3>";
    echo "<ul>";
    echo "<li>Campo 'status': " . ($tem_status ? "✅ Existe" : "❌ FALTANDO") . "</li>";
    echo "<li>Campo 'turma_id': " . ($tem_turma_id ? "✅ Existe" : "❌ FALTANDO") . "</li>";
    echo "</ul>";
    
    // 3. Verificar missões existentes
    echo "<h3>3. Missões na hospedagem:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de missões:</strong> " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        // Verificar se há campo status
        if ($tem_status) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE status = 'ativa'");
            $count_ativas = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p><strong>Missões ativas:</strong> " . $count_ativas['total'] . "</p>";
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE status = 'inativa'");
            $count_inativas = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p><strong>Missões inativas:</strong> " . $count_inativas['total'] . "</p>";
        } else {
            echo "<p>⚠️ <strong>PROBLEMA:</strong> Campo 'status' não existe! Todas as missões serão consideradas inativas.</p>";
        }
        
        // Mostrar algumas missões
        $stmt = $pdo->query("SELECT id, nome, xp, moedas" . ($tem_status ? ", status" : "") . ($tem_turma_id ? ", turma_id" : "") . " FROM missoes ORDER BY id DESC LIMIT 5");
        $missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Últimas 5 missões:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nome</th><th>XP</th><th>Moedas</th>";
        if ($tem_status) echo "<th>Status</th>";
        if ($tem_turma_id) echo "<th>Turma ID</th>";
        echo "</tr>";
        
        foreach ($missoes as $missao) {
            echo "<tr>";
            echo "<td>" . $missao['id'] . "</td>";
            echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
            echo "<td>" . $missao['xp'] . "</td>";
            echo "<td>" . $missao['moedas'] . "</td>";
            if ($tem_status) {
                echo "<td>" . ($missao['status'] ? $missao['status'] : 'NULL') . "</td>";
            }
            if ($tem_turma_id) {
                echo "<td>" . ($missao['turma_id'] ? $missao['turma_id'] : 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Verificar vínculos aluno-turma
    echo "<h3>4. Vínculos aluno-turma:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos_turmas");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de vínculos:</strong> " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("
            SELECT a.nome as aluno_nome, t.nome as turma_nome, at.aluno_id, at.turma_id
            FROM alunos_turmas at
            JOIN alunos a ON at.aluno_id = a.id
            JOIN turmas t ON at.turma_id = t.id
            LIMIT 3
        ");
        $vinculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Exemplos de vínculos:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>Aluno ID</th><th>Aluno</th><th>Turma ID</th><th>Turma</th></tr>";
        foreach ($vinculos as $vinculo) {
            echo "<tr>";
            echo "<td>" . $vinculo['aluno_id'] . "</td>";
            echo "<td>" . htmlspecialchars($vinculo['aluno_nome']) . "</td>";
            echo "<td>" . $vinculo['turma_id'] . "</td>";
            echo "<td>" . htmlspecialchars($vinculo['turma_nome']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ <strong>PROBLEMA:</strong> Nenhum aluno vinculado a turma!</p>";
    }
    
    // 5. Testar consulta específica do missoes.php
    echo "<h3>5. Teste da consulta do missoes.php:</h3>";
    
    if ($count['total'] > 0) {
        // Pegar primeiro vínculo para teste
        $stmt = $pdo->query("
            SELECT at.aluno_id, at.turma_id, a.nome as aluno_nome, t.nome as turma_nome
            FROM alunos_turmas at
            JOIN alunos a ON at.aluno_id = a.id
            JOIN turmas t ON at.turma_id = t.id
            LIMIT 1
        ");
        $exemplo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exemplo) {
            echo "<p><strong>Testando com:</strong></p>";
            echo "<ul>";
            echo "<li>Aluno: " . htmlspecialchars($exemplo['aluno_nome']) . " (ID: " . $exemplo['aluno_id'] . ")</li>";
            echo "<li>Turma: " . htmlspecialchars($exemplo['turma_nome']) . " (ID: " . $exemplo['turma_id'] . ")</li>";
            echo "</ul>";
            
            // Construir consulta baseada nos campos disponíveis
            if ($tem_status) {
                $sql = "
                    SELECT m.*
                    FROM missoes m
                    WHERE m.status = 'ativa'
                      AND (m.turma_id = :turma_id OR m.turma_id IS NULL)
                      AND NOT EXISTS (
                          SELECT 1 FROM solicitacoes_missoes sm
                          WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
                      )
                ";
            } else {
                $sql = "
                    SELECT m.*
                    FROM missoes m
                    WHERE (m.turma_id = :turma_id OR m.turma_id IS NULL)
                      AND NOT EXISTS (
                          SELECT 1 FROM solicitacoes_missoes sm
                          WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
                      )
                ";
            }
            
            echo "<p><strong>SQL executado:</strong></p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($sql) . "</pre>";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':turma_id' => $exemplo['turma_id'], ':aluno_id' => $exemplo['aluno_id']]);
            $missoes_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Resultado:</strong> " . count($missoes_disponiveis) . " missões encontradas</p>";
            
            if (count($missoes_disponiveis) > 0) {
                echo "<h4>Missões disponíveis:</h4>";
                echo "<ul>";
                foreach ($missoes_disponiveis as $missao) {
                    echo "<li>" . htmlspecialchars($missao['nome']) . " (ID: " . $missao['id'] . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<h4>🔍 Análise do problema:</h4>";
                echo "<ul>";
                
                if (!$tem_status) {
                    echo "<li>❌ <strong>Campo 'status' faltando:</strong> Todas as missões são consideradas inativas</li>";
                }
                
                // Verificar se há missões para esta turma
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE turma_id = " . $exemplo['turma_id'] . " OR turma_id IS NULL");
                $count_turma = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<li>Missões para esta turma: " . $count_turma['total'] . "</li>";
                
                // Verificar solicitações existentes
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM solicitacoes_missoes WHERE aluno_id = " . $exemplo['aluno_id']);
                $count_solicitacoes = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<li>Solicitações deste aluno: " . $count_solicitacoes['total'] . "</li>";
                
                echo "</ul>";
            }
        }
    }
    
    // 6. Soluções recomendadas
    echo "<h3>6. 🛠️ Soluções Recomendadas:</h3>";
    
    if (!$tem_status) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ SOLUÇÃO CRÍTICA - Adicionar campo 'status':</h4>";
        echo "<p>Execute este SQL na hospedagem:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "ALTER TABLE missoes ADD COLUMN status ENUM('ativa', 'inativa') DEFAULT 'ativa';\n";
        echo "UPDATE missoes SET status = 'ativa' WHERE status IS NULL;";
        echo "</pre>";
        echo "</div>";
    }
    
    if (!$tem_turma_id) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>ℹ️ SOLUÇÃO OPCIONAL - Adicionar campo 'turma_id':</h4>";
        echo "<p>Execute este SQL na hospedagem:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "ALTER TABLE missoes ADD COLUMN turma_id INT NULL;";
        echo "</pre>";
        echo "</div>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ PRÓXIMOS PASSOS:</h4>";
    echo "<ol>";
    echo "<li>Execute os SQLs acima na hospedagem</li>";
    echo "<li>Atualize as missões existentes para status 'ativa'</li>";
    echo "<li>Teste acessando: <code>missoes.php?id=1</code></li>";
    echo "<li>Verifique se as missões aparecem</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante o diagnóstico:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
