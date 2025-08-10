<?php
require '../../db.php'; // Inclui a conexão com o banco de dados
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trocaId = (int)$_POST['troca_id'];
    $acao = $_POST['acao']; // 'aprovar' ou 'rejeitar'

    // Verificar se a troca existe
    $stmtTroca = $pdo->prepare("SELECT * FROM trocas WHERE id = :id");
    $stmtTroca->execute([':id' => $trocaId]);
    $troca = $stmtTroca->fetch(PDO::FETCH_ASSOC);

    if (!$troca) {
        die("Erro: Solicitação de troca não encontrada.");
    }

    $alunoId = $troca['aluno_id'];
    $produtoId = $troca['produto_id'];

    // Obter informações do produto e do aluno
    $stmtProduto = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmtProduto->execute([':id' => $produtoId]);
    $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

    $stmtAluno = $pdo->prepare("SELECT * FROM alunos WHERE id = :id");
    $stmtAluno->execute([':id' => $alunoId]);
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if (!$produto || !$aluno) {
        die("Erro: Produto ou aluno não encontrado.");
    }

    if ($acao === 'aprovar') {
        $status = 'aprovado';

        // Verificar se o aluno tem moedas suficientes
        if ($aluno['moedas'] < $produto['moeda']) {
            die("Erro: O aluno não tem moedas suficientes para esta troca.");
        }

        // Descontar as moedas do aluno
        $novoSaldo = $aluno['moedas'] - $produto['moeda'];
        $stmtAtualizarMoedas = $pdo->prepare("UPDATE alunos SET moedas = :novo_saldo WHERE id = :id");
        $stmtAtualizarMoedas->execute([':novo_saldo' => $novoSaldo, ':id' => $alunoId]);

    } elseif ($acao === 'rejeitar') {
        $status = 'rejeitado';
    } else {
        die("Ação inválida.");
    }

    // Atualizar o status da troca
    $stmtAtualizarTroca = $pdo->prepare("UPDATE trocas SET status = :status WHERE id = :id");
    $stmtAtualizarTroca->execute([':status' => $status, ':id' => $trocaId]);

    // Redirecionar de volta ao dashboard
    header("Location: paginas/tables.php");
    exit;
}
?>
