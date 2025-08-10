<?php
require 'db.php'; // Inclui a conexão com o banco de dados
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = (int)$_POST['produto_id'];
    $alunoId = (int)$_POST['aluno_id'];

    // Verificar se o produto e o aluno existem
    $stmtProduto = $pdo->prepare("SELECT * FROM produtos WHERE id = :produto_id");
    $stmtProduto->execute([':produto_id' => $produtoId]);
    $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

    $stmtAluno = $pdo->prepare("SELECT * FROM alunos WHERE id = :aluno_id");
    $stmtAluno->execute([':aluno_id' => $alunoId]);
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if (!$produto || !$aluno) {
        die("Produto ou aluno não encontrado.");
    }

    // Registrar a solicitação de troca
    $stmtTroca = $pdo->prepare("INSERT INTO trocas (aluno_id, produto_id, status) VALUES (:aluno_id, :produto_id, 'pendente')");
    $stmtTroca->execute([':aluno_id' => $alunoId, ':produto_id' => $produtoId]);

    // Obter o ID da troca recém-criada
    $trocaId = $pdo->lastInsertId();

    // Redirecionar para a página de instruções, passando o ID da troca
    header("Location: troca_confirmada.php?troca_id=$trocaId&aluno_id=$alunoId&produto_id=$produtoId");
    exit;
}
?>
