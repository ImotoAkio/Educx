<?php
session_start();
require '../../../db.php';

$respostas = $_POST['respostas'];
$corretas = 0;

// Verificar respostas
foreach ($respostas as $pergunta_id => $alternativa_id) {
    $sql = "SELECT correta FROM alternativas WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $alternativa_id]);
    $correta = $stmt->fetchColumn();

    if ($correta) {
        $corretas++;
    }
}

echo "Você acertou $corretas perguntas!";
?>