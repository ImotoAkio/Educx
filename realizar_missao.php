<?php
require 'db.php';

// Recuperar o ID do aluno e da missão enviados via GET
$aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
$missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;

if (!$aluno_id || !$missao_id) {
    die("Erro: Aluno ou missão não especificados.");
}

// Consultar os dados do aluno no banco de dados
$stmt = $pdo->prepare("SELECT id, nome, moedas, xp_atual, xp_total FROM alunos WHERE id = :id");
$stmt->execute([':id' => $aluno_id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Erro: Aluno não encontrado.");
}

// Consultar os detalhes da missão no banco de dados
$stmt = $pdo->prepare("SELECT id, nome, descricao, xp, moedas, link FROM missoes WHERE id = :id");
$stmt->execute([':id' => $missao_id]);
$missao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$missao) {
    die("Erro: Missão não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Troca</title>
    <link rel="stylesheet" href="asset/louja.css">
    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
                        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
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

        .level {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: #00FFAB;
            border-radius: 50px;
            padding: 5px 15px;
            color: #000;
            font-size: 1em;
            font-weight: bold;
        }

        .range {
            position: relative;
            background-color: #333;
            width: 300px;
            height: 30px;
            margin: 20px auto;
            transform: skew(30deg);
            font-family: 'Orbitron', monospace;
        }

        .range:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #F3E600;
            width: calc(<?= $progresso; ?>%);
            z-index: 0;
        }

        .range:after {
            content: '<?= round($progresso); ?>%';
            color: #000;
            position: absolute;
            left: 5%;
            top: 50%;
            transform: translateY(-50%) skewX(-30deg);
            z-index: 1;
        }

        .range__label {
            transform: skew(-30deg) translateY(-100%);
            line-height: 1.5;
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
.coin-font {
    font-family: "Press Start 2P", cursive; /* Altere a fonte aqui */
    font-size: 1.2em; /* Ajuste o tamanho da fonte */
    color: gold; /* Altere a cor, se necessário */
}
.coin-font.black {
    color: black; /* Cor preta */
}
.xp-font {
    font-family: "Press Start 2P", cursive; /* Altere a fonte aqui */
    font-size: 1.2em; /* Ajuste o tamanho da fonte */
    color: goldenrod; /* Altere a cor, se necessário */
}
.xp-font.black {
    color: black; /* Cor preta */
}
.coins {
            align-items: center;
            font-size: 1.2em;
            padding-right: 20px;
        }
.coins img {
            width: 24px;
        }


    </style>
</head>
<body>
<div id="wrapper">
		<!-- Cabeçalho -->
        <header>
    <div class="header-container">
        <!-- Botão de voltar -->
        <button class="voltar" onclick="window.location.href='aluno.php?id=<?= $aluno['id']; ?>'"><</button>
        
        <!-- Contador de moedas -->
        <div class="coins">
            <img src="asset/img/coin.gif" alt="Moeda">
            <span class="coin-font"><?= htmlspecialchars($aluno['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        
        <div class="coins">
            <img src="asset/img/xp.gif" alt="Moeda">
            <span class="xp-font"><?= htmlspecialchars($aluno['xp_atual'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
        
    <div class="container-fluid">
        <div class="row">
            <div class="range" style="--p:<?= $progresso; ?>">
                <div class="range__label">Missões</div>
            </div>
        </div>
    </div>
    <br>
</header>

		<section>
			<div class="container-fluid">
				<!-- Cartão principal -->
				<div class="row">
					<div class="col-12">
						<div class="hero-card">

							<div class="card-content">
                                <h1>Missão: <?= htmlspecialchars($missao['nome']); ?></h1>
                                <p><?= htmlspecialchars($missao['descricao']); ?></p>
                                    <div class="coins">
                                        <img src="asset/img/xp.gif" alt="Xp">
                                        <span class="xp-font black"><?= htmlspecialchars($missao['xp'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="coins">
                                        <img src="asset/img/coin.gif" alt="Moeda">
                                        <span class="coin-font black"><?= htmlspecialchars($missao['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <p><strong>XP:</strong> <?= htmlspecialchars($missao['xp']); ?> | <strong>Moedas:</strong> <?= htmlspecialchars($missao['moedas']); ?></p>
                                <div>
                                <br>
                                <form method="POST" action="confirmar_missao.php?aluno_id=<?= urlencode($aluno_id); ?>&missao_id=<?= urlencode($missao_id); ?>">
    <button type="submit" class="voltar">Concluir Missão</button>
</form>






                                    
									
								</div>
							</div>
						</div>
					</div>
				</div>

</body>
</html>
