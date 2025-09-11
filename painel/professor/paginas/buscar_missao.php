<?php
session_start();
require '../../../db.php';

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da missão não fornecido']);
    exit;
}

$missao_id = (int) $_GET['id'];
$professor_id = $_SESSION['usuario_id'];

try {
    // Buscar a missão apenas se ela pertencer ao professor logado
    $stmt = $pdo->prepare("
        SELECT id, nome, descricao, xp, moedas, link, turma_id 
        FROM missoes 
        WHERE id = :id AND criador_id = :criador_id
    ");
    $stmt->execute([':id' => $missao_id, ':criador_id' => $professor_id]);
    $missao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($missao) {
        echo json_encode(['success' => true, 'missao' => $missao]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missão não encontrada ou você não tem permissão para editá-la']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar missão: ' . $e->getMessage()]);
}
?>
