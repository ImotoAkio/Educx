<?php
require 'db.php';

// Atualizar a senha para o e-mail fornecido
$email = 'seuemail@dominio.com';
$nova_senha = 'senha123'; // Senha original
$senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE professores SET senha = :senha WHERE email = :email");
$stmt->execute([':senha' => $senha_hash, ':email' => $email]);

echo "Senha atualizada com sucesso!";
?>
