<?php
require 'db.php'; // Inclui a conexão com o banco de dados
session_start();

if (!isset($_GET['produto_id'], $_GET['aluno_id'])) {
	die("Produto ou aluno não informado.");
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Confirmação de Troca</title>
	<link rel="stylesheet" href="asset/loja.css">
	<style>
		/* From Uiverse.io by adamgiebl */
		button {
			color: #090909;
			padding: 0.7em 1.7em;
			font-size: 18px;
			border-radius: 0.5em;
			background: #e8e8e8;
			cursor: pointer;
			border: 1px solid #e8e8e8;
			transition: all 0.3s;
			box-shadow: 6px 6px 12px #c5c5c5, -6px -6px 12px #ffffff;
		}

		button:hover {
			border: 1px solid white;
		}

		button:active {
			box-shadow: 4px 4px 12px #c5c5c5, -4px -4px 12px #ffffff;
		}

		.branco {
			color: white;
		}

		.content-image {
			width: 50px;
			height: 50px;
		}

		img {
			width: 200px;
			/* Tamanho da imagem */
			height: auto;
			/* Mantém a proporção da imagem */
			margin-left: 20%;
			/* Espaço abaixo da imagem */
			margin-bottom: 10px;
			margin-top: 20px;
		}

		.branco {
			color: #ffffff;
		}
	</style>
</head>

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
								<h3 class="branco">Você está prestes a trocar <strong><?= htmlspecialchars($produto['moeda']); ?> moedas</strong> pelo produto <strong><?= htmlspecialchars($produto['nome']); ?></strong>.</h3>
								<p class="branco">Confirma?</p> <!-- Descrição  -->
								<div>
									<br>
									<form action="processar_solicitacao.php" method="POST">
										<input type="hidden" name="produto_id" value="<?= $produtoId; ?>">
										<input type="hidden" name="aluno_id" value="<?= $alunoId; ?>">
										<button type="submit">Confirmar Troca</button>
									</form>
									<div class="content-image">
										<img src="<?= $produto['imagem'] ?>" alt="Imagem do produto">
									</div>


								</div>
							</div>
						</div>
					</div>
				</div>



</body>

</html>