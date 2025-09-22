<?php
require 'db.php';
session_start();

if (!isset($_GET['troca_id'])) {
    echo 'erro';
    exit;
}

$trocaId = (int)$_GET['troca_id'];

try {
    // Buscar o status da troca
    $stmt = $pdo->prepare("SELECT status FROM trocas WHERE id = :troca_id");
    $stmt->execute([':troca_id' => $trocaId]);
    $troca = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($troca) {
        echo $troca['status'];
    } else {
        echo 'erro';
    }
} catch (Exception $e) {
    echo 'erro';
}
?>
