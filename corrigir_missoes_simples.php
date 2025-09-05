<?php
/**
 * Script simples para corrigir o problema das missões na hospedagem
 * Execute este script na HOSPEDAGEM
 */

require 'db.php';

echo "<h2>🛠️ Correção Rápida das Missões</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar se o campo status existe
    echo "<h3>1. Verificando campo 'status'...</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM missoes LIKE 'status'");
    $status_exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$status_exists) {
        echo "<p>❌ Campo 'status' não existe. Adicionando...</p>";
        $pdo->exec("ALTER TABLE missoes ADD COLUMN status ENUM('ativa', 'inativa') DEFAULT 'ativa'");
        echo "<p>✅ Campo 'status' adicionado!</p>";
    } else {
        echo "<p>✅ Campo 'status' já existe</p>";
    }
    
    // 2. Verificar se o campo turma_id existe
    echo "<h3>2. Verificando campo 'turma_id'...</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM missoes LIKE 'turma_id'");
    $turma_id_exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$turma_id_exists) {
        echo "<p>❌ Campo 'turma_id' não existe. Adicionando...</p>";
        $pdo->exec("ALTER TABLE missoes ADD COLUMN turma_id INT NULL");
        echo "<p>✅ Campo 'turma_id' adicionado!</p>";
    } else {
        echo "<p>✅ Campo 'turma_id' já existe</p>";
    }
    
    // 3. Definir todas as missões como ativas
    echo "<h3>3. Definindo missões como ativas...</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes");
    $count_total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de missões: " . $count_total['total'] . "</p>";
    
    if ($count_total['total'] > 0) {
        $pdo->exec("UPDATE missoes SET status = 'ativa' WHERE status IS NULL OR status = ''");
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM missoes WHERE status = 'ativa'");
        $count_ativas = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Missões ativas: " . $count_ativas['total'] . "</p>";
    }
    
    // 4. Verificar/criar tabela solicitacoes_missoes
    echo "<h3>4. Verificando tabela 'solicitacoes_missoes'...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'solicitacoes_missoes'");
    $tabela_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tabela_existe) {
        echo "<p>❌ Tabela 'solicitacoes_missoes' não existe. Criando...</p>";
        $pdo->exec("
            CREATE TABLE solicitacoes_missoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                missao_id INT NOT NULL,
                status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
                data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                data_aprovacao TIMESTAMP NULL
            )
        ");
        echo "<p>✅ Tabela 'solicitacoes_missoes' criada!</p>";
    } else {
        echo "<p>✅ Tabela 'solicitacoes_missoes' já existe</p>";
    }
    
    // 5. Teste final
    echo "<h3>5. Teste final...</h3>";
    
    // Verificar se há alunos vinculados a turmas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos_turmas");
    $count_vinculos = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Vínculos aluno-turma: " . $count_vinculos['total'] . "</p>";
    
    if ($count_vinculos['total'] > 0) {
        // Testar com primeiro aluno vinculado
        $stmt = $pdo->query("
            SELECT at.aluno_id, at.turma_id, a.nome as aluno_nome, t.nome as turma_nome
            FROM alunos_turmas at
            JOIN alunos a ON at.aluno_id = a.id
            JOIN turmas t ON at.turma_id = t.id
            LIMIT 1
        ");
        $exemplo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exemplo) {
            echo "<p>Testando com: " . htmlspecialchars($exemplo['aluno_nome']) . " (Turma: " . htmlspecialchars($exemplo['turma_nome']) . ")</p>";
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM missoes m
                WHERE m.status = 'ativa'
                  AND (m.turma_id = :turma_id OR m.turma_id IS NULL)
                  AND NOT EXISTS (
                      SELECT 1 FROM solicitacoes_missoes sm
                      WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
                  )
            ");
            $stmt->execute([':turma_id' => $exemplo['turma_id'], ':aluno_id' => $exemplo['aluno_id']]);
            $count_disponiveis = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Missões disponíveis para este aluno: " . $count_disponiveis['total'] . "</p>";
            
            if ($count_disponiveis['total'] > 0) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ SUCESSO!</h4>";
                echo "<p>As missões estão funcionando corretamente!</p>";
                echo "<p>Teste acessando: <code>missoes.php?id=" . $exemplo['aluno_id'] . "</code></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>⚠️ AVISO</h4>";
                echo "<p>Nenhuma missão disponível para este aluno.</p>";
                echo "<p>Isso pode ser normal se o aluno já realizou todas as missões.</p>";
                echo "</div>";
            }
        }
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ PROBLEMA</h4>";
        echo "<p>Nenhum aluno está vinculado a turmas!</p>";
        echo "<p>Use o painel da secretaria para vincular alunos às turmas.</p>";
        echo "</div>";
    }
    
    echo "<h3>🎯 Resumo da Correção:</h3>";
    echo "<ul>";
    echo "<li>✅ Campos da tabela 'missoes' verificados/corrigidos</li>";
    echo "<li>✅ Missões definidas como 'ativas'</li>";
    echo "<li>✅ Tabela 'solicitacoes_missoes' verificada/criada</li>";
    echo "<li>✅ Sistema testado</li>";
    echo "</ul>";
    
    echo "<p><strong>Próximo passo:</strong> Teste acessando <code>missoes.php?id=1</code> (substitua 1 pelo ID de um aluno)</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro durante a correção:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
