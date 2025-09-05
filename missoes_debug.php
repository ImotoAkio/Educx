<?php
require 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o ID do aluno foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do aluno não fornecido ou inválido.");
}

$aluno_id = (int) $_GET['id'];

// Consulta TODAS as missões sem restrições (para debug)
$stmt = $pdo->query("SELECT * FROM missoes ORDER BY id DESC");
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: mostrar informações
echo "<!-- DEBUG: Total de missões encontradas: " . count($missoes) . " -->";
echo "<!-- DEBUG: Aluno ID: " . $aluno_id . " -->";

// Consulta os dados do aluno
$stmt = $pdo->prepare("
    SELECT id, nome, moedas, avatar, xp_atual, xp_total, nivel 
    FROM alunos 
    WHERE id = :id
");
$stmt->execute([':id' => $aluno_id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado.");
}

// --- Lógica de nível e progresso igual ao aluno.php ---
function calcularNivelEProgresso($xp_total)
{
    $nivel = 1;
    $xp_para_proximo_nivel = 499; // XP inicial necessário para o nível 1
    $titulo_nivel = "Iniciante"; // Título padrão

    // Determina o título do nível com base no XP total
    if ($xp_total >= 2000) {
        $titulo_nivel = "Líder";
        $xp_para_proximo_nivel = 0; // Sem próximo nível definido
    } elseif ($xp_total >= 1000) {
        $titulo_nivel = "Guardião";
        $xp_para_proximo_nivel = 1999;
    } elseif ($xp_total >= 500) {
        $titulo_nivel = "Explorador";
        $xp_para_proximo_nivel = 999;
    }

    // Calcula o progresso dentro do nível atual
    $xp_atual_no_nivel = $xp_para_proximo_nivel > 0 ? $xp_total % ($xp_para_proximo_nivel + 1) : $xp_total;

    return [
        'nivel' => $nivel,
        'titulo_nivel' => $titulo_nivel,
        'xp_atual_no_nivel' => $xp_atual_no_nivel,
        'xp_para_proximo_nivel' => $xp_para_proximo_nivel
    ];
}

$xp_total = (int) $aluno['xp_total'];
$dados = calcularNivelEProgresso($xp_total);
$nivel_atual = $dados['nivel'];
$titulo_nivel = $dados['titulo_nivel'];
$xp_atual_no_nivel = $dados['xp_atual_no_nivel'];
$xp_para_proximo_nivel = $dados['xp_para_proximo_nivel'];
$progresso = ($xp_para_proximo_nivel > 0) ? ($xp_atual_no_nivel / $xp_para_proximo_nivel) : 1;
$progresso_percentual = round($progresso * 100, 2);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="asset/louja.css">
    <link rel="stylesheet" href="asset/button.css">
    <title>Missões - DEBUG</title>
    <style>
                @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
                @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
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
.xp-font {
    font-family: "Press Start 2P", cursive; /* Altere a fonte aqui */
    font-size: 1.2em; /* Ajuste o tamanho da fonte */
    color: yellow; /* Altere a cor, se necessário */
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
<!-- Please ❤ this if you like it! -->
<!-- Follow Me https://codepen.io/designfenix -->

<div id="wrapper">
    
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

<!-- DEBUG INFO -->
<div style="background: #f8f9fa; padding: 15px; margin: 10px; border-radius: 5px; border-left: 4px solid #007bff;">
    <h4>🔍 DEBUG - Informações do Sistema</h4>
    <p><strong>Total de missões no banco:</strong> <?= count($missoes); ?></p>
    <p><strong>Aluno:</strong> <?= htmlspecialchars($aluno['nome']); ?> (ID: <?= $aluno['id']; ?>)</p>
    <p><strong>Status:</strong> <?= count($missoes) > 0 ? '✅ Missões encontradas' : '❌ Nenhuma missão encontrada'; ?></p>
</div>

<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h3 class="segment-title left">TODAS as Missões (DEBUG)</h3>
                <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                    <i class="fa fa-info-circle"></i> Esta é a versão de debug que mostra TODAS as missões sem restrições.
                </p>
                
                <?php if (!empty($missoes)): ?>
                    <?php foreach ($missoes as $missao): ?>
                        <article class="hero-card">
                            <div class="card-content">
                                <h3><?= htmlspecialchars($missao['nome']); ?></h3>
                                <p><?= htmlspecialchars($missao['descricao']); ?></p>
                                <div style="margin: 10px 0;">
                                    <span style="background: #ffc107; padding: 5px 10px; border-radius: 15px; margin-right: 10px;">
                                        <i class="fa fa-star"></i> <?= $missao['xp']; ?> XP
                                    </span>
                                    <span style="background: #28a745; padding: 5px 10px; border-radius: 15px; margin-right: 10px;">
                                        <i class="fa fa-coins"></i> <?= $missao['moedas']; ?> moedas
                                    </span>
                                    <span style="background: #17a2b8; padding: 5px 10px; border-radius: 15px;">
                                        <i class="fa fa-info"></i> Status: <?= $missao['status'] ? $missao['status'] : 'NULL'; ?>
                                    </span>
                                </div>
                                <button class="gradient-button" onclick="window.location.href='realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>';">
                                    <span>Realizar Missão</span>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="background: #f8d7da; padding: 20px; border-radius: 5px; text-align: center;">
                        <h4>❌ Nenhuma missão encontrada!</h4>
                        <p>Verifique se:</p>
                        <ul style="text-align: left; display: inline-block;">
                            <li>A tabela 'missoes' existe</li>
                            <li>Há dados na tabela 'missoes'</li>
                            <li>A conexão com o banco está funcionando</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

</div>
</body>
</html>
