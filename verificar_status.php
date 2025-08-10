<?php
require 'db.php'; // Inclui a conexão com o banco de dados

if (!isset($_GET['troca_id'])) {
    die("Erro: ID da troca não foi fornecido.");
}

$trocaId = (int)$_GET['troca_id'];

// Consulta o status da troca
$stmt = $pdo->prepare("SELECT status FROM Trocas WHERE id = :id");
$stmt->execute([':id' => $trocaId]);
$troca = $stmt->fetch(PDO::FETCH_ASSOC);

if ($troca) {
    echo $troca['status']; // Retorna o status da troca
} else {
    echo "pendente"; // Caso a troca não seja encontrada
}
?>
