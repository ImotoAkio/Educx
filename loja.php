<?php
require 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o ID do aluno foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
	die("ID do aluno não fornecido.");
}

$id = (int) $_GET['id'];

// Consulta as informações do aluno
$stmt = $pdo->prepare("SELECT * FROM alunos WHERE id = :id");
$stmt->execute([':id' => $id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
	die("Aluno não encontrado.");
}

// Verificar se existe coluna 'tipo' na tabela produtos
try {
	$stmt = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'tipo'");
	$temColunaTipo = $stmt->rowCount() > 0;
} catch (PDOException $e) {
	$temColunaTipo = false;
}

// Consulta os produtos disponíveis na loja
if ($temColunaTipo) {
	// Se existe coluna tipo, separar produtos e powercards
	$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id ASC");
	$todosProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$produtos = [];
	$powercards = [];
	
	foreach ($todosProdutos as $produto) {
		$tipo = strtolower(trim($produto['tipo'] ?? 'produto'));
		if ($tipo === 'powercard' || $tipo === 'power_card') {
			$powercards[] = $produto;
		} else {
			$produtos[] = $produto;
		}
	}
} else {
	// Se não existe coluna tipo, assumir que são todos produtos normais
	$produtosStmt = $pdo->query("SELECT * FROM produtos ORDER BY id ASC");
	$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);
	$powercards = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Loja</title>
	<link rel="stylesheet" href="asset/loja.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="icon" href="assets/img/favicon.png" type="image/png">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<style>
		@import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
		@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

		.coin-font {
			font-family: "Press Start 2P", cursive;
			font-size: 1.2em;
			color: gold;
		}

		.coins {
			display: flex;
			align-items: center;
			gap: 5px;
			font-size: 1.2em;
		}

		.coins img {
			width: 24px;
			height: 24px;
		}

		.voltar {
			margin-bottom: 0;
			font: inherit;
			background-color: #f0f0f0;
			border: 0;
			color: #242424;
			border-radius: 0.5em;
			font-size: 1.35rem;
			padding: 0.375em 1em;
			font-weight: 600;
			text-shadow: 0 0.0625em 0 #fff;
			box-shadow: inset 0 0.0625em 0 0 #f4f4f4, 0 0.0625em 0 0 #efefef,
				0 0.125em 0 0 #ececec, 0 0.25em 0 0 #e0e0e0, 0 0.3125em 0 0 #dedede,
				0 0.375em 0 0 #dcdcdc, 0 0.425em 0 0 #cacaca, 0 0.425em 0.5em 0 #cecece;
			transition: 0.15s ease;
			cursor: pointer;
		}

		.voltar:active {
			translate: 0 0.225em;
			box-shadow: inset 0 0.03em 0 0 #f4f4f4, 0 0.03em 0 0 #efefef,
				0 0.0625em 0 0 #ececec, 0 0.125em 0 0 #e0e0e0, 0 0.125em 0 0 #dedede,
				0 0.2em 0 0 #dcdcdc, 0 0.225em 0 0 #cacaca, 0 0.225em 0.375em 0 #cecece;
		}

		.header-container {
			display: flex;
			align-items: center;
			gap: 15px;
			flex-wrap: wrap;
		}

		.header-left {
			display: flex;
			align-items: center;
			gap: 15px;
		}

		.header-right {
			margin-left: auto;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.review img {
			width: 12px;
			height: 12px;
		}
	</style>
</head>

<body>
	<div id="wrapper">
		<!-- Cabeçalho -->
		<header>
			<div class="container-fluid">
				<div class="row">
					<div class="col-4-sm">
						<div class="header-container">
							<div class="header-left">
								<button class="voltar" onclick="window.location.href='aluno.php?id=<?= $aluno['id']; ?>'">
									<i class="fas fa-chevron-left"></i>
								</button>
								<div class="coins">
									<img src="asset/img/coin.gif" alt="Moeda">
									<span class="coin-font"><?= htmlspecialchars($aluno['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>
								</div>
							</div>
							<div class="header-right">
								<a href="#" class="btn"><i class="fas fa-th"></i></a>
							</div>
						</div>
					</div>
					<div class="col-4-sm center">
						<h1 class="page-title">Loja</h1>
					</div>
					<div class="col-4-sm right"></div>
				</div>
			</div>
		</header>

		<section>
			<div class="container-fluid">
				<!-- HERO CARD -->
				<div class="row">
					<div class="col-12">
						<div class="hero-card">
							<div class="content-image">
								<img src="https://design-fenix.com.ar/codepen/ui-store/speaker.png" alt="">
							</div>
							<div class="card-content">
								<h3>Olá, <?= htmlspecialchars($aluno['nome']); ?>!</h3>
								<p>Explore seus prêmios</p>
								<div class="content-input">
									<i class="fas fa-search"></i>
									<input type="text" placeholder="Pesquisar" id="searchInput">
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Produtos (Grid) -->
				<?php if (!empty($produtos)): ?>
					<div class="row margin-vertical">
						<div class="col-6-sm">
							<h3 class="segment-title left">Produtos</h3>
						</div>

					</div>
					<!-- Products grid -->
					<div class="row" id="productsGrid">
						<?php foreach ($produtos as $produto): ?>
							<div class="col-6-sm product-item" data-name="<?= strtolower(htmlspecialchars($produto['nome'])); ?>">
								<div class="product">
									<img src="<?= htmlspecialchars($produto['imagem'] ?? ''); ?>" alt="<?= htmlspecialchars($produto['nome']); ?>">
									<div class="detail">
										<h4 class="name"><?= htmlspecialchars($produto['nome']); ?></h4>
										<div class="detail-footer">
											<div class="price left"><?= htmlspecialchars($produto['descricao'] ?? ''); ?></div>
											<div class="review right">
												<img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="">
												<?= number_format($produto['moeda']); ?>
											</div>
										</div>
									</div>
									<div class="star">
										<a href="confirmacao.php?produto_id=<?= $produto['id']; ?>&aluno_id=<?= $aluno['id']; ?>">
											<img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="">
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- PowerCards (Featured) -->
				<?php if (!empty($powercards)): ?>
					<div class="row margin-vertical">
						<div class="col-6-sm">
							<h3 class="segment-title left">PowerCards</h3>
						</div>

					</div>

					<!-- Feature Products (PowerCards) -->
					<?php foreach ($powercards as $powercard): ?>
						<div class="row">
							<div class="col-12">
								<div class="featured-product">
									<div class="content-img">
										<img src="<?= htmlspecialchars($powercard['imagem'] ?? ''); ?>" alt="<?= htmlspecialchars($powercard['nome']); ?>">
									</div>
									<div class="product-detail">
										<h4 class="product-name"><?= htmlspecialchars($powercard['nome']); ?></h4>
										<p class="price">
											<img src="asset/img/coin.gif" alt="Moedas" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle;">
											<?= number_format($powercard['moeda']); ?> moedas
										</p>
									</div>
									<div class="star">
										<a href="confirmacao.php?produto_id=<?= $powercard['id']; ?>&aluno_id=<?= $aluno['id']; ?>">
											<img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="">
											<span class="review">Comprar</span>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>
	</div>

	<script>
		// Prevenir comportamento padrão dos links
		$("a").on("click", function (e) {
			// Apenas prevenir se não tiver href válido ou se for um link de ação
			if ($(this).attr('href') === '#' || $(this).attr('href') === '') {
				e.preventDefault();
			}
		});

		// Funcionalidade de busca
		$('#searchInput').on('keyup', function() {
			var searchText = $(this).val().toLowerCase();
			
			$('.product-item').each(function() {
				var productName = $(this).data('name');
				
				if (productName.indexOf(searchText) === -1) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		});
	</script>
</body>

</html>
