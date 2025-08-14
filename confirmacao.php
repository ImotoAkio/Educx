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
	<div class="checkout-wrapper">
		<div class="checkout-card">
			<div class="checkout-success">
				<img src="asset/img/coin.gif" alt="Sucesso" class="center-img-success">
			</div>
			<h2>Confirmação de Compra</h2>
			<div class="checkout-product">
				<img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="Imagem do produto" class="center-img-produto">
				<h3><?= htmlspecialchars($produto['nome']); ?></h3>
				<p><?= htmlspecialchars($produto['descricao']); ?></p>
			</div>
			<div class="checkout-info">
				<p><strong>Valor:</strong> <span style="color:gold; font-size:1.2em;"><?= htmlspecialchars($produto['moeda']); ?> moedas</span></p>
				<p><strong>Seu saldo:</strong> <span style="color:gold; font-size:1.2em;"><?= htmlspecialchars($aluno['moedas']); ?> moedas</span></p>
			</div>
			<form action="processar_solicitacao.php" method="POST" style="margin-top:18px;">
				<input type="hidden" name="produto_id" value="<?= $produtoId; ?>">
				<input type="hidden" name="aluno_id" value="<?= $alunoId; ?>">
				<button type="submit" class="gradient-button" style="width:100%;">Confirmar Compra</button>
			</form>
			<div style="display:flex; gap:10px; margin-top:18px;">
				<a href="loja.php?id=<?= $alunoId; ?>" class="gradient-button" style="flex:1; text-align:center;">Voltar para Loja</a>
				<a href="missoes.php?id=<?= $alunoId; ?>" class="gradient-button" style="flex:1; text-align:center;">Ver Missões</a>
			</div>
		</div>
	</div>
	<style>
		.center-img-success {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 60px;
			margin-bottom: 12px;
		}
		.center-img-produto {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 120px;
			border-radius: 12px;
			margin-bottom: 10px;
		}
		.checkout-wrapper {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #17171f;
		}
		.checkout-card {
			background: #23233a;
			border-radius: 18px;
			box-shadow: 0 4px 24px #0008;
			padding: 32px 24px;
			text-align: center;
			color: #fff;
			font-family: 'Orbitron', Arial, sans-serif;
			max-width: 350px;
			width: 100%;
		}
		.checkout-success img {
			animation: bounce 1s infinite alternate;
		}
		@keyframes bounce {
			0% { transform: translateY(0); }
			100% { transform: translateY(-10px); }
		}
		.gradient-button {
			background: linear-gradient(90deg, #f3e600 0%, #ffd700 100%);
			color: #23233a;
			border: none;
			border-radius: 8px;
			padding: 12px 0;
			font-weight: bold;
			font-size: 1em;
			cursor: pointer;
			box-shadow: 0 2px 8px #0002;
			transition: background 0.2s;
			text-decoration: none;
			display: block;
		}
		.gradient-button:hover {
			background: linear-gradient(90deg, #ffd700 0%, #f3e600 100%);
		}
		.checkout-product img {
			box-shadow: 0 2px 8px #0004;
		}
		.checkout-info {
			margin: 18px 0 0 0;
		}
	</style>
</body>
</html>