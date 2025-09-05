<?php
/**
 * Script de diagnóstico para identificar problemas na página personalizar.php
 * Execute este script na hospedagem para identificar o erro HTTP 500
 */

// Configurações do banco da hospedagem
$host = 'localhost'; // Altere conforme sua hospedagem
$dbname = 'coinz'; // Nome do banco na hospedagem
$username = 'root'; // Usuário da hospedagem
$password = ''; // Senha da hospedagem

echo "<h2>🔍 Diagnóstico da Página personalizar.php</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

// 1. Verificar conexão com banco
echo "<h3>1. Verificando conexão com banco de dados...</h3>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Conexão com banco estabelecida com sucesso</p>";
} catch (PDOException $e) {
    echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
    die("Não é possível continuar sem conexão com o banco.");
}

// 2. Verificar se as tabelas necessárias existem
echo "<h3>2. Verificando tabelas necessárias...</h3>";
$tabelas_necessarias = ['alunos', 'avatares', 'avatares_alunos'];

foreach ($tabelas_necessarias as $tabela) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabela '$tabela' existe</p>";
            
            // Verificar estrutura da tabela
            $stmt = $pdo->query("DESCRIBE $tabela");
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>   Colunas: " . implode(', ', $colunas) . "</p>";
        } else {
            echo "<p>❌ Tabela '$tabela' NÃO existe</p>";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Erro ao verificar tabela '$tabela': " . $e->getMessage() . "</p>";
    }
}

// 3. Verificar se existem dados nas tabelas
echo "<h3>3. Verificando dados nas tabelas...</h3>";

// Verificar alunos
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Alunos cadastrados: " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("SELECT id, nome, xp_total, moedas, avatar, fundo FROM alunos LIMIT 3");
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>   Primeiros alunos:</p>";
        echo "<ul>";
        foreach ($alunos as $aluno) {
            echo "<li>ID: {$aluno['id']}, Nome: {$aluno['nome']}, XP: {$aluno['xp_total']}, Avatar: {$aluno['avatar']}</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p>❌ Erro ao verificar alunos: " . $e->getMessage() . "</p>";
}

// Verificar avatares
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM avatares");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Avatares cadastrados: " . $count['total'] . "</p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("SELECT id, nome, arquivo, nivel_requerido, categoria FROM avatares LIMIT 3");
        $avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>   Primeiros avatares:</p>";
        echo "<ul>";
        foreach ($avatares as $avatar) {
            echo "<li>ID: {$avatar['id']}, Nome: {$avatar['nome']}, Arquivo: {$avatar['arquivo']}, Nível: {$avatar['nivel_requerido']}</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p>❌ Erro ao verificar avatares: " . $e->getMessage() . "</p>";
}

// Verificar avatares_alunos
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM avatares_alunos");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Avatares desbloqueados: " . $count['total'] . "</p>";
} catch (PDOException $e) {
    echo "<p>❌ Erro ao verificar avatares_alunos: " . $e->getMessage() . "</p>";
}

// 4. Testar consulta específica da página personalizar.php
echo "<h3>4. Testando consulta específica da página...</h3>";

$aluno_id = 1; // Teste com ID 1
echo "<p>Testando com aluno_id = $aluno_id</p>";

try {
    // Testar consulta de aluno
    $stmt = $pdo->prepare("SELECT id, nome, xp_total, moedas, avatar, fundo FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($aluno) {
        echo "<p>✅ Consulta de aluno funcionou</p>";
        echo "<p>   Dados: " . json_encode($aluno) . "</p>";
        
        // Testar função de cálculo de nível
        $xp_total = $aluno['xp_total'];
        $nivel_atual = 1;
        if ($xp_total >= 2000) $nivel_atual = 4;
        elseif ($xp_total >= 1000) $nivel_atual = 3;
        elseif ($xp_total >= 500) $nivel_atual = 2;
        
        echo "<p>✅ Cálculo de nível funcionou: Nível $nivel_atual</p>";
        
        // Testar consulta de avatares
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   CASE WHEN aa.aluno_id IS NOT NULL THEN 1 ELSE 0 END as desbloqueado,
                   CASE WHEN a.nivel_requerido <= :nivel_atual THEN 1 ELSE 0 END as nivel_suficiente
            FROM avatares a 
            LEFT JOIN avatares_alunos aa ON a.id = aa.avatar_id AND aa.aluno_id = :aluno_id
            WHERE a.disponivel = 1
            ORDER BY a.nivel_requerido ASC, a.xp_requerido ASC
        ");
        $stmt->execute([':nivel_atual' => $nivel_atual, ':aluno_id' => $aluno_id]);
        $avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>✅ Consulta de avatares funcionou: " . count($avatares) . " avatares encontrados</p>";
        
    } else {
        echo "<p>❌ Aluno com ID $aluno_id não encontrado</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Erro na consulta específica: " . $e->getMessage() . "</p>";
}

// 5. Verificar arquivos CSS necessários
echo "<h3>5. Verificando arquivos CSS...</h3>";
$css_files = ['asset/fundos.css', 'asset/loja.css'];

foreach ($css_files as $css) {
    if (file_exists($css)) {
        echo "<p>✅ Arquivo CSS '$css' existe</p>";
    } else {
        echo "<p>❌ Arquivo CSS '$css' NÃO existe</p>";
    }
}

// 6. Verificar imagens de avatares
echo "<h3>6. Verificando pasta de avatares...</h3>";
if (is_dir('asset/img/avatar/')) {
    $files = scandir('asset/img/avatar/');
    $avatar_files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'gif';
    });
    echo "<p>✅ Pasta de avatares existe com " . count($avatar_files) . " arquivos GIF</p>";
    
    if (count($avatar_files) > 0) {
        echo "<p>   Arquivos encontrados: " . implode(', ', array_slice($avatar_files, 0, 5)) . "</p>";
    }
} else {
    echo "<p>❌ Pasta 'asset/img/avatar/' NÃO existe</p>";
}

// 7. Teste de simulação da página
echo "<h3>7. Teste de simulação da página...</h3>";
echo "<p>Testando se a página carregaria sem erros...</p>";

// Simular o que acontece na página
try {
    // Incluir db.php (simular)
    // $aluno_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $aluno_id = 1; // Para teste
    
    if (!$aluno_id) {
        echo "<p>❌ ID do aluno não fornecido</p>";
    } else {
        echo "<p>✅ ID do aluno fornecido: $aluno_id</p>";
        
        // Testar todas as operações da página
        $stmt = $pdo->prepare("SELECT id, nome, xp_total, moedas, avatar, fundo FROM alunos WHERE id = :id");
        $stmt->execute([':id' => $aluno_id]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($aluno) {
            echo "<p>✅ Aluno encontrado</p>";
            
            $nivel_atual = 1;
            if ($aluno['xp_total'] >= 2000) $nivel_atual = 4;
            elseif ($aluno['xp_total'] >= 1000) $nivel_atual = 3;
            elseif ($aluno['xp_total'] >= 500) $nivel_atual = 2;
            
            $stmt = $pdo->prepare("
                SELECT a.*, 
                       CASE WHEN aa.aluno_id IS NOT NULL THEN 1 ELSE 0 END as desbloqueado,
                       CASE WHEN a.nivel_requerido <= :nivel_atual THEN 1 ELSE 0 END as nivel_suficiente
                FROM avatares a 
                LEFT JOIN avatares_alunos aa ON a.id = aa.avatar_id AND aa.aluno_id = :aluno_id
                WHERE a.disponivel = 1
                ORDER BY a.nivel_requerido ASC, a.xp_requerido ASC
            ");
            $stmt->execute([':nivel_atual' => $nivel_atual, ':aluno_id' => $aluno_id]);
            $avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>✅ Consulta de avatares executada com sucesso</p>";
            echo "<p>✅ Página deveria carregar normalmente</p>";
            
        } else {
            echo "<p>❌ Aluno não encontrado - isso causaria erro na página</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro durante simulação: " . $e->getMessage() . "</p>";
}

echo "<h3>🎯 Conclusão do Diagnóstico</h3>";
echo "<p>Se todos os testes acima passaram, o problema pode ser:</p>";
echo "<ul>";
echo "<li>Erro de sintaxe PHP não detectado</li>";
echo "<li>Problema de permissões de arquivo</li>";
echo "<li>Erro de memória ou timeout</li>";
echo "<li>Problema com bibliotecas PHP</li>";
echo "</ul>";

echo "<p><strong>Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Verifique os logs de erro do servidor</li>";
echo "<li>Teste com um aluno_id específico: personalizar.php?id=1</li>";
echo "<li>Execute o script de configuração de avatares se necessário</li>";
echo "</ol>";
?>
