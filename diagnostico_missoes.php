<?php
/**
 * Script para diagnosticar e corrigir problemas com missões
 * Execute este script para resolver o problema das missões não aparecendo
 */

require 'db.php';

echo "<h2>🔍 Diagnóstico do Sistema de Missões</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar se a tabela missoes existe
    echo "<h3>1. Verificando tabela 'missoes'...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'missoes'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'missoes' existe</p>";
        
        // Verificar estrutura atual
        $stmt = $pdo->query("DESCRIBE missoes");
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
        
        // Verificar campos necessários
        $campos_necessarios = ['id', 'nome', 'descricao', 'xp', 'moedas', 'link', 'status', 'turma_id', 'criador_id'];
        $campos_faltando = [];
        
        foreach ($campos_necessarios as $campo) {
            $existe = false;
            foreach ($colunas as $coluna) {
                if ($coluna['Field'] === $campo) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $campos_faltando[] = $campo;
            }
        }
        
        if (!empty($campos_faltando)) {
            echo "<p>⚠️ Campos faltando: " . implode(', ', $campos_faltando) . "</p>";
            
            // Adicionar campos faltando
            foreach ($campos_faltando as $campo) {
                switch ($campo) {
                    case 'status':
                        $pdo->exec("ALTER TABLE missoes ADD COLUMN status ENUM('ativa', 'inativa') DEFAULT 'ativa'");
                        echo "<p>✅ Campo 'status' adicionado</p>";
                        break;
                    case 'turma_id':
                        $pdo->exec("ALTER TABLE missoes ADD COLUMN turma_id INT NULL");
                        echo "<p>✅ Campo 'turma_id' adicionado</p>";
                        break;
                    case 'criador_id':
                        $pdo->exec("ALTER TABLE missoes ADD COLUMN criador_id INT NULL");
                        echo "<p>✅ Campo 'criador_id' adicionado</p>";
                        break;
                }
            }
        } else {
            echo "<p>✅ Todos os campos necessários existem</p>";
        }
        
    } else {
        echo "<p>❌ Tabela 'missoes' não existe. Criando...</p>";
        
        $sql = "CREATE TABLE missoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            descricao TEXT,
            xp INT DEFAULT 0,
            moedas INT DEFAULT 0,
            link VARCHAR(500),
            status ENUM('ativa', 'inativa') DEFAULT 'ativa',
            turma_id INT NULL,
            criador_id INT NULL,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE SET NULL,
            FOREIGN KEY (criador_id) REFERENCES professores(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'missoes' criada com sucesso!</p>";
    }
    
    // 2. Verificar missões existentes
    echo "<h3>2. Verificando missões existentes...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Total de missões: " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("SELECT id, nome, status, turma_id, criador_id FROM missoes ORDER BY id DESC LIMIT 5");
        $missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Últimas 5 missões:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Status</th><th>Turma ID</th><th>Criador ID</th></tr>";
        foreach ($missoes as $missao) {
            echo "<tr>";
            echo "<td>" . $missao['id'] . "</td>";
            echo "<td>" . htmlspecialchars($missao['nome']) . "</td>";
            echo "<td>" . $missao['status'] . "</td>";
            echo "<td>" . ($missao['turma_id'] ? $missao['turma_id'] : 'NULL') . "</td>";
            echo "<td>" . ($missao['criador_id'] ? $missao['criador_id'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Verificar se há alunos vinculados a turmas
    echo "<h3>3. Verificando vínculos aluno-turma...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos_turmas");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Total de vínculos aluno-turma: " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("
            SELECT a.nome as aluno_nome, t.nome as turma_nome 
            FROM alunos_turmas at
            JOIN alunos a ON at.aluno_id = a.id
            JOIN turmas t ON at.turma_id = t.id
            LIMIT 3
        ");
        $vinculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Exemplos de vínculos:</p>";
        echo "<ul>";
        foreach ($vinculos as $vinculo) {
            echo "<li>" . htmlspecialchars($vinculo['aluno_nome']) . " → " . htmlspecialchars($vinculo['turma_nome']) . "</li>";
        }
        echo "</ul>";
    }
    
    // 4. Testar consulta específica do missoes.php
    echo "<h3>4. Testando consulta do missoes.php...</h3>";
    
    // Simular um aluno vinculado a uma turma
    $stmt = $pdo->query("
        SELECT at.aluno_id, at.turma_id, a.nome as aluno_nome, t.nome as turma_nome
        FROM alunos_turmas at
        JOIN alunos a ON at.aluno_id = a.id
        JOIN turmas t ON at.turma_id = t.id
        LIMIT 1
    ");
    $exemplo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exemplo) {
        echo "<p>Testando com aluno: " . htmlspecialchars($exemplo['aluno_nome']) . " (ID: " . $exemplo['aluno_id'] . ")</p>";
        echo "<p>Turma: " . htmlspecialchars($exemplo['turma_nome']) . " (ID: " . $exemplo['turma_id'] . ")</p>";
        
        // Testar a consulta exata do missoes.php
        $stmt = $pdo->prepare("
            SELECT m.*
            FROM missoes m
            WHERE m.status = 'ativa'
              AND (m.turma_id = :turma_id OR m.turma_id IS NULL)
              AND NOT EXISTS (
                  SELECT 1 FROM solicitacoes_missoes sm
                  WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
              )
        ");
        $stmt->execute([':turma_id' => $exemplo['turma_id'], ':aluno_id' => $exemplo['aluno_id']]);
        $missoes_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>📊 Missões disponíveis para este aluno: " . count($missoes_disponiveis) . "</p>";
        
        if (count($missoes_disponiveis) > 0) {
            echo "<p>Missões encontradas:</p>";
            echo "<ul>";
            foreach ($missoes_disponiveis as $missao) {
                echo "<li>" . htmlspecialchars($missao['nome']) . " (Status: " . $missao['status'] . ", Turma: " . ($missao['turma_id'] ? $missao['turma_id'] : 'Todas') . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>⚠️ Nenhuma missão disponível para este aluno</p>";
            
            // Verificar se há missões ativas
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE status = 'ativa'");
            $count_ativas = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Missões ativas no sistema: " . $count_ativas['total'] . "</p>";
            
            if ($count_ativas['total'] > 0) {
                echo "<p>🔍 Possíveis causas:</p>";
                echo "<ul>";
                echo "<li>Missões não estão vinculadas à turma do aluno</li>";
                echo "<li>Aluno já realizou todas as missões disponíveis</li>";
                echo "<li>Problema na consulta SQL</li>";
                echo "</ul>";
            }
        }
    } else {
        echo "<p>❌ Nenhum aluno vinculado a turma encontrado</p>";
        echo "<p>🔍 Possível causa: Alunos não estão vinculados às turmas</p>";
    }
    
    // 5. Verificar tabela solicitacoes_missoes
    echo "<h3>5. Verificando tabela 'solicitacoes_missoes'...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'solicitacoes_missoes'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'solicitacoes_missoes' existe</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM solicitacoes_missoes");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>📊 Total de solicitações: " . $count['total'] . "</p>";
    } else {
        echo "<p>❌ Tabela 'solicitacoes_missoes' não existe</p>";
        echo "<p>🔍 Esta tabela é necessária para o sistema funcionar</p>";
    }
    
    echo "<h3>🎯 Conclusão do Diagnóstico</h3>";
    echo "<p>Se as missões não estão aparecendo, verifique:</p>";
    echo "<ol>";
    echo "<li>Se as missões têm status = 'ativa'</li>";
    echo "<li>Se as missões estão vinculadas à turma do aluno (turma_id)</li>";
    echo "<li>Se o aluno está vinculado a uma turma</li>";
    echo "<li>Se o aluno já não realizou todas as missões</li>";
    echo "</ol>";
    
    echo "<h3>🛠️ Soluções Recomendadas:</h3>";
    echo "<ol>";
    echo "<li>Execute este script para corrigir a estrutura</li>";
    echo "<li>Verifique se as missões criadas têm status 'ativa'</li>";
    echo "<li>Vincule as missões às turmas apropriadas</li>";
    echo "<li>Vincule os alunos às turmas</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante o diagnóstico:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
