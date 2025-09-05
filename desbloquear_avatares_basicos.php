<?php
require 'db.php';

echo "=== DESBLOQUEANDO AVATARES BÁSICOS PARA ALUNOS EXISTENTES ===\n\n";

try {
    // Buscar todos os alunos
    $stmt = $pdo->query("SELECT id FROM alunos");
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar avatares básicos (nível 1)
    $stmt = $pdo->query("SELECT id FROM avatares WHERE nivel_requerido = 1");
    $avatares_basicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados " . count($alunos) . " alunos e " . count($avatares_basicos) . " avatares básicos.\n\n";
    
    $total_desbloqueios = 0;
    
    foreach ($alunos as $aluno) {
        foreach ($avatares_basicos as $avatar) {
            // Verificar se já está desbloqueado
            $stmt = $pdo->prepare("SELECT id FROM avatares_alunos WHERE aluno_id = ? AND avatar_id = ?");
            $stmt->execute([$aluno['id'], $avatar['id']]);
            
            if ($stmt->rowCount() == 0) {
                // Desbloquear avatar
                $stmt = $pdo->prepare("INSERT INTO avatares_alunos (aluno_id, avatar_id, metodo_desbloqueio) VALUES (?, ?, 'nivel')");
                $stmt->execute([$aluno['id'], $avatar['id']]);
                $total_desbloqueios++;
            }
        }
    }
    
    echo "✅ Processo concluído!\n";
    echo "Total de desbloqueios realizados: $total_desbloqueios\n";
    echo "\nAgora todos os alunos têm acesso aos avatares básicos!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
