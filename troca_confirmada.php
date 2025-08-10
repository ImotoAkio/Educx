<?php
require 'db.php'; // Inclui a conexão com o banco de dados
session_start();

if (!isset($_GET['troca_id'])) {
    die("Erro: ID da solicitação de troca não foi fornecido.");
}


$produtoId = (int)$_GET['produto_id'];
$alunoId = (int)$_GET['aluno_id'];

// Recuperar informações do produto e do aluno
$stmtProduto = $pdo->prepare("SELECT * FROM produtos WHERE id = :produto_id");
$stmtProduto->execute([':produto_id' => $produtoId]);
$produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

$stmtAluno = $pdo->prepare("SELECT * FROM alunos WHERE id = :aluno_id");
$stmtAluno->execute([':aluno_id' => $alunoId]);
$aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

if (!$produto || !$aluno) {
    die("Produto ou aluno não encontrado.");
}

$trocaId = (int)$_GET['troca_id'];

// Verificar se a troca existe no banco de dados
$stmt = $pdo->prepare("SELECT * FROM trocas WHERE id = :id");
$stmt->execute([':id' => $trocaId]);
$troca = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$troca) {
    die("Erro: Solicitação de troca não encontrada.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troca Confirmada</title>
    <link rel="stylesheet" href="asset/loja.css">
    <script>
        async function verificarStatusTroca() {
            const response = await fetch(`verificar_status.php?troca_id=<?= $trocaId; ?>`);
            const status = await response.text();

            if (status === 'aprovado') {
                window.location.href = 'aluno.php';
            } else if (status === 'rejeitado') {
                alert('Sua troca foi rejeitada.');
                window.location.href = 'aluno.php';
            }
        }

        setInterval(verificarStatusTroca, 500);
    </script>
</head>
<body>
<body>
<div id="wrapper">
		<!-- Cabeçalho -->
		<header>
			<div class="container-fluid">
				<div class="row">
				<a href="aluno.php?id=<?= $aluno['id']; ?>" class="btn" style="color: #ffffff;">
    			<i class="fas fa-chevron-left"></i>
				</a>

					<div class="col-4-sm center">
						<h1 class="page-title">Confirmação de troca</h1> <!-- Título  -->
					</div>


				</div>
			</div>
		</header>

		<section>
			<div class="container-fluid">
				<!-- Cartão principal -->
				<div class="row">
					<div class="col-12">
						<div class="hero-card1">

							<div class="card-content">
								<h3 class="branco">Parabéns! Você acabou de ganhar um <strong><?= htmlspecialchars($produto['nome']); ?></strong>.</h3> 
								<p class="branco">Vá até a secretaria para retirar seu prêmio! 🤑</p> <!-- Descrição  -->
                                <div>
                                <br>

                                <div class="content-image1">
								<img src="<?= $produto['imagem'] ?>" alt="Imagem do produto">
							</div>
                                    
									
								</div>
							</div>
						</div>
					</div>
				</div>


</body>
</html>
