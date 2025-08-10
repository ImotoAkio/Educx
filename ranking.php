<?php
// Inclui o arquivo de conexão com o banco de dados
include 'db.php';

try {
    // Consulta para buscar todos os alunos ordenados por XP total
    $query = "SELECT id, nome, xp_total, avatar, GREATEST(FLOOR(xp_total / 1000), 1) AS nivel FROM alunos ORDER BY xp_total DESC";
    $stmt = $pdo->query($query);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}

// Verifica se o ID do aluno foi passado na URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$alunoEncontrado = null;
$posicaoAluno = null;

// Determina a posição do aluno no ranking, se o ID foi passado
if ($id) {
    foreach ($alunos as $index => $aluno) {
        if ($aluno['id'] == $id) {
            $alunoEncontrado = $aluno;
            $posicaoAluno = $index + 1; // Posição no ranking (índice começa em 0, então somamos 1)
            break;
        }
    }
}
$current_avatar = $alunoEncontrado['avatar'] ?? 'asset/img/default.gif';
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking de Alunos</title>
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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking de Alunos</title>
    <link rel="stylesheet" href="asset/louja.css">
</head>
<body>
<!-- Please ❤ this if you like it! -->
<!-- Follow Me https://codepen.io/designfenix -->

<div id="wrapper">
	<header>
		<div class="container-fluid">
			<div class="row">
				<div class="col-4-sm"><button class="voltar" onclick="window.location.href='aluno.php?id=<?= $aluno['id']; ?>'"><</button></div>
				<div class="col-4-sm center">
					<h3 class="page-title">Ranking</h3>
				</div>
				<div class="col-4-sm right">
					<div class="profiale"></div>
				</div>
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
                                <img src="<?= htmlspecialchars($current_avatar); ?>" alt="">
                            </div>
						<div class="card-content">
							<h3>Olá <?= htmlspecialchars($aluno['nome']); ?>,</h3>
                            <p>você está na <?= $posicaoAluno; ?>ª </p>
                            <p> posição do ranking!</p>

						</div>
					</div>
				</div>
			</div>

			<!-- Category title -->
			<div class="row margin-vertical">
				<div class="col-6-sm">
					<h3 class="segment-title left">TOP Jogadores</h3>
				</div>

			</div>
			<!-- Feature Product -->
			<div class="row">
				<div class="col-12">
 <!-- Exibição do ranking -->
 <?php if (!empty($alunos)): ?>
    <?php foreach ($alunos as $index => $aluno): ?>
    <div class="featured-product">
        <div class="content-img">
            <img src="<?= htmlspecialchars($aluno['avatar'] ?: 'asset/img/default.gif'); ?>" alt="Foto do Aluno">
        </div>
        <div class="product-detail">
            <h4 class="product-name"><?= htmlspecialchars($aluno['nome']); ?></h4>
            <p class="price">XP Total: <?= number_format($aluno['xp_total'], 0, ',', '.'); ?></p>
        </div>
        <div class="star">
            <img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Estrelas">
            <span class="review"><?= ($index + 1); ?>° lugar</span>
        </div>
    </div>
<?php endforeach; ?>
<?php else: ?>
    <p>Nenhum aluno encontrado no ranking.</p>
<?php endif; ?>

				</div>
			</div>
		</div>
	</section>
</div>

</body>
</html>

</body>
</html>
