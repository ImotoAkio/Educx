<?php
require 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o ID do aluno foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do aluno não fornecido ou inválido.");
}

$aluno_id = (int) $_GET['id'];

// Recupera a turma do aluno
$stmt_turma = $pdo->prepare("
    SELECT t.id AS turma_id
    FROM alunos_turmas at
    JOIN turmas t ON at.turma_id = t.id
    WHERE at.aluno_id = :aluno_id
");
$stmt_turma->execute([':aluno_id' => $aluno_id]);
$turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    die("O aluno não está associado a nenhuma turma.");
}

$turma_id = $turma['turma_id'];

// Consulta apenas as missões que o aluno ainda não realizou
$stmt = $pdo->prepare("
    SELECT m.*
    FROM missoes m
    WHERE m.status = 'ativa'
      AND (m.turma_id = :turma_id OR m.turma_id IS NULL)
      AND NOT EXISTS (
          SELECT 1 FROM solicitacoes_missoes sm
          WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
      )
");
$stmt->execute([':turma_id' => $turma_id, ':aluno_id' => $aluno_id]);
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta os quizzes disponíveis para a turma do aluno
$stmt_quizzes = $pdo->prepare("
    SELECT * 
    FROM quizzes 
    WHERE turma_id = :turma_id
");
$stmt_quizzes->execute([':turma_id' => $turma_id]);
$quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Missões</title>
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


<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h3 class="segment-title left">Quizzes disponíveis</h3>
                <?php if (!empty($quizzes)): ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <article class="hero-card">
                            <div class="card-content">
                                <h3><?= htmlspecialchars($quiz['nome']); ?></h3>
                                <p><?= htmlspecialchars($quiz['descricao']); ?></p>
                                <button class="gradient-button" onclick="window.location.href='quiz.php?aluno_id=<?= $aluno_id; ?>&quiz_id=<?= $quiz['id']; ?>';">
                                    <span>Responder Quiz</span>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum quiz disponível no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
        <section>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h3 class="segment-title left">Missões disponíveis</h3>
                        <?php if (!empty($missoes)): ?>
                            <?php foreach ($missoes as $missao): ?>
                                <article class="hero-card">
                                    <div class="card-content">
                                        <h3><?= htmlspecialchars($missao['nome']); ?></h3>
                                        <p><?= htmlspecialchars($missao['descricao']); ?></p>
                                        <button class="gradient-button" onclick="window.location.href='realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>';">
                                            <span>Realizar Missão</span>
                                        </button>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Nenhuma missão disponível no momento.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
