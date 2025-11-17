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

// Calcular progresso do aluno (similar ao missoes.php)
function calcularNivelEProgresso($xp_total)
{
    $nivel = 1;
    $xp_para_proximo_nivel = 499;
    $titulo_nivel = "Iniciante";

    if ($xp_total >= 2000) {
        $titulo_nivel = "Líder";
        $xp_para_proximo_nivel = 0;
    } elseif ($xp_total >= 1000) {
        $titulo_nivel = "Guardião";
        $xp_para_proximo_nivel = 1999;
    } elseif ($xp_total >= 500) {
        $titulo_nivel = "Explorador";
        $xp_para_proximo_nivel = 999;
    }

    $xp_atual_no_nivel = $xp_para_proximo_nivel > 0 ? $xp_total % ($xp_para_proximo_nivel + 1) : $xp_total;
    $progresso = ($xp_para_proximo_nivel > 0) ? ($xp_atual_no_nivel / $xp_para_proximo_nivel) : 1;
    $progresso_percentual = round($progresso * 100, 2);

    return [
        'nivel' => $nivel,
        'titulo_nivel' => $titulo_nivel,
        'xp_atual_no_nivel' => $xp_atual_no_nivel,
        'xp_para_proximo_nivel' => $xp_para_proximo_nivel,
        'progresso_percentual' => $progresso_percentual
    ];
}

$xp_total = (int) $aluno['xp_total'];
$dados = calcularNivelEProgresso($xp_total);
$progresso_percentual = $dados['progresso_percentual'];
$titulo_nivel = $dados['titulo_nivel'];
$xp_atual_no_nivel = $dados['xp_atual_no_nivel'];
$xp_para_proximo_nivel = $dados['xp_para_proximo_nivel'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Missão</title>
    <link rel="stylesheet" href="asset/loja.css">
    <link rel="stylesheet" href="asset/button.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
        }
        
        #wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .container-fluid {
            width: 100%;
            max-width: 100%;
            padding: 0 15px;
            box-sizing: border-box;
        }
        
        .header-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
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

        .range-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #F3E600;
            width: calc(<?= $progresso_percentual; ?>%);
            z-index: 0;
        }

        .range__label {
            transform: skew(-30deg) translateY(-100%);
            line-height: 1.5;
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
        
        .coin-font {
            font-family: "Press Start 2P", cursive;
            font-size: 1.2em;
            color: gold;
        }
        
        .coin-font.black {
            color: black;
        }
        
        .xp-font {
            font-family: "Press Start 2P", cursive;
            font-size: 1.2em;
            color: yellow;
        }
        
        .xp-font.black {
            color: black;
        }
        
        .coins {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 1.2em;
            padding-right: 20px;
        }
        
        .coins img {
            width: 24px;
            height: 24px;
        }

        .hero-card {
            min-height: auto !important;
            height: auto !important;
        }

        .hero-card .card-content {
            padding: 25px;
            min-height: auto;
        }

        .hero-card .card-content h1 {
            margin-bottom: 15px;
            word-wrap: break-word;
        }

        .hero-card .card-content p {
            margin-bottom: 15px;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .hero-card .card-content .coins {
            margin: 10px 0;
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
            <img src="asset/img/xp.gif" alt="XP">
            <span class="xp-font"><?= htmlspecialchars($aluno['xp_total'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
        
    <div class="container-fluid">
        <div class="row">
            <div class="range">
                <div class="range-bar" style="width: <?= $progresso_percentual; ?>%;"></div>
                <div class="range__label">
                    <p><?= htmlspecialchars($titulo_nivel); ?> (<?= $xp_atual_no_nivel; ?> / <?= $xp_para_proximo_nivel; ?> XP)</p>
                </div>
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
		</section>
	</div>
</body>
</html>
