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

// Consulta os produtos disponíveis na loja
$produtosStmt = $pdo->query("SELECT * FROM produtos");
$produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Loja</title>
	<!-- Link para o arquivo CSS -->
	<link rel="stylesheet" href="asset/loja.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.0.0/css/fontawesome.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.0.0/css/all.min.css" rel="stylesheet">

	<style>
		        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
				@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
				.coin-font {
    font-family: "Press Start 2P", cursive; /* Altere a fonte aqui */
    font-size: 1.5em; /* Ajuste o tamanho da fonte */
    color: gold; /* Altere a cor, se necessário */
}		
		.coins {
            display: flex;
            align-items: center;
            font-size: 1.2em;
        }

        .coins img {
            width: 24px;
            margin-right: 10px;
        }
		
.voltar {
	margin-bottom: 15px;
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

	</style>

</head>

<body>
	<div id="wrapper">
		<!-- Cabeçalho -->
		<header>
		<div class="container-fluid">
    <div class="row align-items-center">
        <!-- Botão voltar -->
        <div class="col-auto">
            <button class="voltar" onclick="window.location.href='aluno.php?id=<?= $aluno['id']; ?>'"><</button>
        </div>


        <!-- Moedas -->
        <div class="col-auto text-end">
            <div class="coins">
                <img src="asset/img/coin.gif" alt="Moeda">
                <span class="coin-font"><?= htmlspecialchars($aluno['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </div>
</div>


		</header>

		<section>
			<div class="container-fluid">
				<!-- Cartão principal -->
				<div class="row">
					<div class="col-12">
						<div class="hero-card">
							<div class="content-image">
								<img src="https://design-fenix.com.ar/codepen/ui-store/speaker.png" alt="Imagem do produto principal">
							</div>
							<div class="card-content">
								<h3>Olá, <?= htmlspecialchars($aluno['nome']); ?>.</h3> 
								<p>Explore seus prêmios</p> <!-- Descrição  -->
								<div class="content-input">
									<i class="gg-search"></i>
									<input type="text" placeholder="Pesquisar"> <!-- Placeholder  -->
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Título da categoria -->
				<div class="row margin-vertical">
					<div class="col-6-sm">
						<h3 class="segment-title left">Populares</h3> <!-- Categoria  -->
					</div>

				</div>

				<!-- Grade de produtos -->
				
				<div class="row">
				<?php foreach ($produtos as $produto): ?>
					<div class="col-6-sm">
						<div class="product">
						<img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="<?= htmlspecialchars($produto['nome']); ?>">
							<div class="detail">
								<h4 class="name"><?= htmlspecialchars($produto['nome']); ?></h4> <!-- Nome do produto  -->
								<div class="detail-footer">
									<div class="price left"><?= htmlspecialchars($produto['descricao']); ?></div> <!-- Preço formatado -->
									<div class="review right"><img src="asset/img/coin.gif" alt="Moedas"><?= number_format($produto['moeda']); ?> moedas</div>
								</div>
							</div>
							<div class="star">
    <a href="confirmacao.php?produto_id=<?= $produto['id']; ?>&aluno_id=<?= $aluno['id']; ?>">
        <img src="asset/img/carrinho.png" alt="Carrinho">
    </a>
</div>

						</div>
					</div>
				<?php endforeach; ?>
				

</body>

</html>
