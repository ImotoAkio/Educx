<?php
require 'db.php';

echo "=== TESTE DO SISTEMA DE AVATARES COM MÉTRICAS DE ALUNO.PHP ===\n\n";

// Função para calcular nível baseado no XP (usando a mesma lógica de aluno.php)
function calcularNivelEProgresso($xp_total) {
    $nivel = 1;
    $xp_para_proximo_nivel = 499; // XP inicial necessário para o nível 1
    $titulo_nivel = "Iniciante"; // Título padrão

    // Determina o título do nível com base no XP total
    if ($xp_total >= 2000) {
        $titulo_nivel = "Líder";
        $xp_para_proximo_nivel = 0; // Sem próximo nível definido
    } elseif ($xp_total >= 1000) {
        $titulo_nivel = "Guardião";
        $xp_para_proximo_nivel = 1999;
    } elseif ($xp_total >= 500) {
        $titulo_nivel = "Explorador";
        $xp_para_proximo_nivel = 999;
    }

    // Calcula o progresso dentro do nível atual
    $xp_atual_no_nivel = $xp_total % ($xp_para_proximo_nivel + 1);

    return [
        'nivel' => $nivel,
        'titulo_nivel' => $titulo_nivel,
        'xp_atual_no_nivel' => $xp_atual_no_nivel,
        'xp_para_proximo_nivel' => $xp_para_proximo_nivel
    ];
}

// Função para obter nível numérico para desbloqueio de avatares
function calcularNivelParaAvatares($xp_total) {
    if ($xp_total >= 2000) return 4; // Líder
    if ($xp_total >= 1000) return 3; // Guardião
    if ($xp_total >= 500) return 2;  // Explorador
    return 1; // Iniciante
}

try {
    // Testar com diferentes níveis de XP
    $testes_xp = [0, 250, 500, 750, 1000, 1500, 2000, 2500];
    
    echo "Testando cálculo de níveis:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($testes_xp as $xp) {
        $dados = calcularNivelEProgresso($xp);
        $nivel_avatar = calcularNivelParaAvatares($xp);
        
        echo "XP: {$xp} | ";
        echo "Título: {$dados['titulo_nivel']} | ";
        echo "Nível Avatar: {$nivel_avatar} | ";
        echo "Progresso: {$dados['xp_atual_no_nivel']}/{$dados['xp_para_proximo_nivel']}\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Verificar avatares no banco
    echo "\nVerificando avatares no banco de dados:\n";
    $stmt = $pdo->query("SELECT nome, nivel_requerido, xp_requerido, categoria FROM avatares ORDER BY nivel_requerido, xp_requerido");
    $avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($avatares as $avatar) {
        echo "✅ {$avatar['nome']} - Nível {$avatar['nivel_requerido']} ({$avatar['xp_requerido']} XP) - {$avatar['categoria']}\n";
    }
    
    echo "\n🎉 Sistema funcionando corretamente!\n";
    echo "\nResumo dos níveis:\n";
    echo "- Nível 1 (0-499 XP): Iniciante\n";
    echo "- Nível 2 (500-999 XP): Explorador\n";
    echo "- Nível 3 (1000-1999 XP): Guardião\n";
    echo "- Nível 4 (2000+ XP): Líder\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
