<?php
require 'db.php';

echo "=== ATUALIZANDO SISTEMA DE AVATARES PARA USAR MÉTRICAS DE ALUNO.PHP ===\n\n";

try {
    // Atualizar avatares com as novas métricas de nível
    echo "Atualizando requisitos de nível dos avatares...\n";
    
    $avatares_atualizados = [
        // Nível 1 (0-499 XP) - Iniciante
        ['Default', 1, 0],
        ['Gatito', 1, 0],
        
        // Nível 2 (500-999 XP) - Explorador
        ['Ratatui', 2, 500],
        ['Abeia', 2, 500],
        
        // Nível 3 (1000-1999 XP) - Guardião
        ['Robo Estudante', 3, 1000],
        ['Cachorrão', 3, 1000],
        
        // Nível 4 (2000+ XP) - Líder
        ['Robo Legal', 4, 2000],
        ['Rodolfo', 4, 2000],
        ['Robozão', 4, 2000]
    ];
    
    $stmt = $pdo->prepare("UPDATE avatares SET nivel_requerido = ?, xp_requerido = ? WHERE nome = ?");
    
    foreach ($avatares_atualizados as $avatar) {
        $stmt->execute([$avatar[1], $avatar[2], $avatar[0]]);
        echo "✅ Avatar '{$avatar[0]}' atualizado (Nível {$avatar[1]}, {$avatar[2]} XP)\n";
    }
    
    echo "\n🎉 Sistema de avatares atualizado com sucesso!\n";
    echo "\nNovos níveis de desbloqueio:\n";
    echo "- Nível 1 (0 XP): Default, Gatito (Iniciante)\n";
    echo "- Nível 2 (500 XP): Ratatui, Abeia (Explorador)\n";
    echo "- Nível 3 (1000 XP): Robo Estudante, Cachorrão (Guardião)\n";
    echo "- Nível 4 (2000 XP): Robo Legal, Rodolfo, Robozão (Líder)\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
