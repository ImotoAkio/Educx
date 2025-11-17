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

// Verificar se a coluna data_limite existe
try {
    $checkColumn = $pdo->query("SHOW COLUMNS FROM missoes LIKE 'data_limite'");
    $columnExists = $checkColumn->rowCount() > 0;
} catch (PDOException $e) {
    $columnExists = false;
}

// Consulta apenas as missões que o aluno ainda não realizou e que ainda estão válidas
if ($columnExists) {
    $stmt = $pdo->prepare("
        SELECT m.*
        FROM missoes m
        WHERE m.status = 'ativa'
          AND (m.turma_id = :turma_id OR m.turma_id IS NULL OR m.turma_id = 0 OR m.turma_id = '')
          AND (m.data_limite IS NULL OR m.data_limite >= CURDATE())
          AND NOT EXISTS (
              SELECT 1 FROM solicitacoes_missoes sm
              WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
          )
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT m.*
        FROM missoes m
        WHERE m.status = 'ativa'
          AND (m.turma_id = :turma_id OR m.turma_id IS NULL OR m.turma_id = 0 OR m.turma_id = '')
          AND NOT EXISTS (
              SELECT 1 FROM solicitacoes_missoes sm
              WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
          )
    ");
}
$stmt->execute([':turma_id' => $turma_id, ':aluno_id' => $aluno_id]);
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta os quizzes disponíveis para a turma do aluno (que ainda não foram realizados)
$stmt_quizzes = $pdo->prepare("
    SELECT q.* 
    FROM quizzes q
    WHERE q.turma_id = :turma_id
      AND NOT EXISTS (
          SELECT 1 FROM quizzes_finalizados qf
          WHERE qf.aluno_id = :aluno_id AND qf.quiz_id = q.id
      )
");
$stmt_quizzes->execute([':turma_id' => $turma_id, ':aluno_id' => $aluno_id]);
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
    <link rel="stylesheet" href="asset/loja.css">
    <link rel="stylesheet" href="asset/button.css">
    <title>Missões</title>
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
        .header-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
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
        .xp-font {
            font-family: "Press Start 2P", cursive;
            font-size: 1.2em;
            color: yellow;
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

        /* ===== ESTILOS PARA MISSÕES - NOVO DESIGN ===== */
        
        .section-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .section-subtitle {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
            margin-bottom: 0;
        }
        
        .missoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        
        /* From Uiverse.io by andrew-demchenk0 */ 
        .card {
            --main-color: #000;
            --bg-color: #EBD18D;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            width: 100%;
            max-width: 100%;
            padding: 25px;
            background: var(--bg-color);
            border-radius: 20px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card__wrapper {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        
        .card___wrapper-acounts {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: center;
            z-index: 1;
            cursor: pointer;
        }
        
        .card___wrapper-acounts > div:nth-child(2) {
            position: absolute;
            left: 25px;
            z-index: -1;
        }
        
        .card___wrapper-acounts > div:nth-child(3) {
            position: absolute;
            left: 50px;
            z-index: -2;
        }
        
        .card__score {
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 500;
            font-size: 16px;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 100%;
            background: var(--main-color);
        }
        
        .card__acounts {
            width: 42px;
            height: 42px;
        }
        
        .card__acounts svg {
            width: 100%;
            height: 100%;
        }
        
        .card__menu {
            width: 40px;
            height: 40px;
            background: #F6DB96;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .card__menu:hover {
            background: #f0d080;
        }
        
        .card__title {
            margin-top: 50px;
            font-weight: 900;
            font-size: 25px;
            color: var(--main-color);
        }
        
        .card__subtitle {
            margin-top: 15px;
            font-weight: 400;
            font-size: 15px;
            color: var(--main-color);
            line-height: 1.5;
        }
        
        .card__indicator {
            margin-top: 50px;
            font-weight: 500;
            font-size: 14px;
            color: var(--main-color);
        }
        
        .card__progress {
            margin-top: 10px;
        }
        
        .card__progress progress {
            width: 100%;
            height: 4px;
            border-radius: 100px;
        }
        
        .card__progress progress::-webkit-progress-bar {
            background-color: #00000030;
            border-radius: 100px;
        }
        
        .card__progress progress::-webkit-progress-value {
            background-color: var(--main-color);
            border-radius: 100px;
        }
        
        .card__progress progress::-moz-progress-bar {
            background-color: var(--main-color);
            border-radius: 100px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-icon {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.1em;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .missoes-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 0 10px;
            }
            
            .card {
                padding: 20px;
                max-width: 100%;
            }
            
            .header-container {
                padding: 0 10px;
            }
            
            .container-fluid {
                padding: 0 10px;
            }
        }
        
        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease-out;
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
                <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                    <i class="fa fa-info-circle"></i> Apenas quizzes que você ainda não realizou são exibidos aqui.
                </p>
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

<!-- Seção de Quizzes Realizados -->
<?php
// Consulta os quizzes já realizados pelo aluno
$stmt_quizzes_realizados = $pdo->prepare("
    SELECT q.nome, q.descricao, qf.data_finalizacao
    FROM quizzes_finalizados qf
    JOIN quizzes q ON qf.quiz_id = q.id
    WHERE qf.aluno_id = :aluno_id
    ORDER BY qf.data_finalizacao DESC
");
$stmt_quizzes_realizados->execute([':aluno_id' => $aluno_id]);
$quizzes_realizados = $stmt_quizzes_realizados->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($quizzes_realizados)): ?>
<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h3 class="segment-title left">Quizzes Realizados</h3>
                <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                    <i class="fa fa-check-circle"></i> Histórico dos quizzes que você já completou.
                </p>
                <?php foreach ($quizzes_realizados as $quiz): ?>
                    <article class="hero-card" style="opacity: 0.8; background: #f8f9fa;">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($quiz['nome']); ?></h3>
                            <p><?= htmlspecialchars($quiz['descricao']); ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <span style="color: #28a745; font-weight: bold;">
                                    <i class="fa fa-check-circle"></i> Concluído
                                </span>
                                <span style="color: #6c757d; font-size: 0.9em;">
                                    <i class="fa fa-calendar"></i> <?= date('d/m/Y', strtotime($quiz['data_finalizacao'])); ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
        <!-- Seção de Missões Disponíveis -->
        <section>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="section-header">
                            <h3 class="segment-title left">
                                <i class="fa fa-rocket"></i> Missões Disponíveis
                            </h3>
                            <p class="section-subtitle">
                                <i class="fa fa-info-circle"></i> Complete as missões para ganhar XP e moedas!
                            </p>
                        </div>
                        
                        <?php if (is_array($missoes) && count($missoes) > 0): ?>
                            <div class="missoes-grid">
                                <?php foreach ($missoes as $index => $missao): ?>
                                    <div class="card" onclick="window.location.href='realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>';">
                                        <div class="card__wrapper">
                                            <div class="card___wrapper-acounts">
                                                <div class="card__score">+<?= $missao['xp']; ?></div>
                                                <div class="card__acounts"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"><circle r="60" fill="#ffd8c9" cy="64" cx="64"></circle><circle r="48" opacity=".3" fill="#fff" cy="64" cx="64"></circle><path fill="#393c54" d="m64 14a31 31 0 0 1 31 31v41.07a9.93 9.93 0 0 1 -9.93 9.93h-42.14a9.93 9.93 0 0 1 -9.93-9.93v-41.07a31 31 0 0 1 31-31z"></path><circle r="7" fill="#fbc0aa" cy="60" cx="89"></circle><path fill="#00adfe" d="m64 124a59.7 59.7 0 0 0 34.7-11.07l-3.33-10.29a10 10 0 0 0 -9.37-6.64h-43.95a10 10 0 0 0 -9.42 6.64l-3.33 10.29a59.7 59.7 0 0 0 34.7 11.07z"></path><path fill="#ff8475" d="m46.54 121.45a59.93 59.93 0 0 0 34.92 0l-2.46-25.45h-30z"></path><path fill="#f85565" d="m48.13 105h31.74l-.39-4h-30.96z"></path><path fill="#ffd8c9" d="m76 96a12 12 0 0 1 -24 0z"></path><path stroke-width="14" stroke-linejoin="round" stroke-linecap="round" stroke="#fbc0aa" fill="none" d="m64 83v12"></path><circle r="7" fill="#fbc0aa" cy="60" cx="39"></circle><path fill="#ffd8c9" d="m64 90a25 25 0 0 1 -25-25v-16.48a25 25 0 1 1 50 0v16.48a25 25 0 0 1 -25 25z"></path><path stroke-width="5" stroke-linejoin="round" stroke-linecap="round" stroke="#fbc0aa" fill="none" d="m64 64.75v6.5"></path><path fill="#515570" d="m64.83 18.35a27.51 27.51 0 0 0 -28.32 27.47v4.76a2 2 0 0 0 2 2h.58a1 1 0 0 0 .86-.49l4.05-7.09 2.48 4.13a1 1 0 0 0 1.71 0l2.48-4.13 2.47 4.13a1 1 0 0 0 1.72 0l2.47-4.13 2.48 4.13a1 1 0 0 0 1.71 0l2.48-4.13 2.48 4.13a1 1 0 0 0 1.71 0l2.48-4.13 2.47 4.13a1 1 0 0 0 1.72 0l2.47-4.13 2.48 4.13a1 1 0 0 0 1.71 0l2.48-4.13 4 7.09a1 1 0 0 0 .86.49h.58a2 2 0 0 0 2-2v-4.18c.05-14.95-11.66-27.61-26.61-28.05z"></path><path fill="#f85565" d="m47.35 113h33.29l-.38-4h-32.52z"></path><path fill="#f85565" d="m46.58 121h34.84l-.39-4h-34.06z"></path><path opacity=".7" fill="#ff8475" d="m58.52 79.39c0-.84 11-.84 11 0 0 1.79-2.45 3.25-5.48 3.25s-5.52-1.46-5.52-3.25z"></path><path opacity=".7" fill="#f85565" d="m69.48 79.29c0 .78-11 .78-11 0 .04-1.79 2.52-3.29 5.52-3.29s5.48 1.5 5.48 3.29z"></path><circle r="3" fill="#515570" cy="58.75" cx="76.25"></circle><path stroke-linejoin="round" stroke-linecap="round" stroke="#515570" fill="none" d="m70.75 59.84a6.61 6.61 0 0 1 11.5-1.31"></path><path style="fill:none;stroke-linecap:round;stroke-linejoin:round;stroke:#515570;stroke-width:2;opacity:.2" d="m72.11 51.46 5.68-.4a4.62 4.62 0 0 1 4.21 2.1l.77 1.21"></path><circle r="3" fill="#515570" cy="58.75" cx="51.75"></circle><g stroke-linecap="round" fill="none"><path stroke-linejoin="round" stroke="#515570" d="m57.25 59.84a6.61 6.61 0 0 0 -11.5-1.31"></path><path stroke-width="2" stroke-linejoin="round" stroke="#515570" opacity=".2" d="m55.89 51.45-5.68-.39a4.59 4.59 0 0 0 -4.21 2.11l-.77 1.21"></path><path stroke-miterlimit="10" stroke="#f85565" d="m57.25 78.76a17.4 17.4 0 0 0 6.75 1.12 17.4 17.4 0 0 0 6.75-1.12"></path></g></svg></div>
                                                <div class="card__acounts"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"><circle r="60" fill="#ff8475" cy="64" cx="64"></circle><circle r="48" opacity=".4" fill="#f85565" cy="64" cx="64"></circle><path fill="#7f3838" d="m64 14a32 32 0 0 1 32 32v41a6 6 0 0 1 -6 6h-52a6 6 0 0 1 -6-6v-41a32 32 0 0 1 32-32z"></path><path opacity=".4" fill="#393c54" d="m62.73 22h2.54a23.73 23.73 0 0 1 23.73 23.73v42.82a4.45 4.45 0 0 1 -4.45 4.45h-41.1a4.45 4.45 0 0 1 -4.45-4.45v-42.82a23.73 23.73 0 0 1 23.73-23.73z"></path><circle r="7" fill="#fbc0aa" cy="65" cx="89"></circle><path fill="#4bc190" d="m64 124a59.67 59.67 0 0 0 34.69-11.06l-3.32-9.3a10 10 0 0 0 -9.37-6.64h-43.95a10 10 0 0 0 -9.42 6.64l-3.32 9.3a59.67 59.67 0 0 0 34.69 11.06z"></path><path opacity=".3" fill="#356cb6" d="m45 110 5.55 2.92-2.55 8.92a60.14 60.14 0 0 0 9 1.74v-27.08l-12.38 10.25a2 2 0 0 0 .38 3.25z"></path><path opacity=".3" fill="#356cb6" d="m71 96.5v27.09a60.14 60.14 0 0 0 9-1.74l-2.54-8.93 5.54-2.92a2 2 0 0 0 .41-3.25z"></path><path fill="#fff" d="m57 123.68a58.54 58.54 0 0 0 14 0v-25.68h-14z"></path><path stroke-width="14" stroke-linejoin="round" stroke-linecap="round" stroke="#fbc0aa" fill="none" d="m64 88.75v9.75"></path><circle r="7" fill="#fbc0aa" cy="65" cx="39"></circle><path fill="#ffd8c9" d="m64 91a25 25 0 0 1 -25-25v-16.48a25 25 0 1 1 50 0v16.48a25 25 0 0 1 -25 25z"></path><path fill="#bc5b57" d="m91.49 51.12v-4.72c0-14.95-11.71-27.61-26.66-28a27.51 27.51 0 0 0 -28.32 27.42v5.33a2 2 0 0 0 2 2h6.81a8 8 0 0 0 6.5-3.33l4.94-6.88a18.45 18.45 0 0 1 1.37 1.63 22.84 22.84 0 0 0 17.87 8.58h13.45a2 2 0 0 0 2.04-2.03z"></path><path style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1" d="m62.76 36.94c4.24 8.74 10.71 10.21 16.09 10.21h5"></path><path style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1" d="m71 35c2.52 5.22 6.39 6.09 9.6 6.09h3"></path><circle r="3" fill="#515570" cy="62.28" cx="76"></circle><circle r="3" fill="#515570" cy="62.28" cx="52"></circle><ellipse ry="2.98" rx="4.58" opacity=".1" fill="#f85565" cy="69.67" cx="50.42"></ellipse><ellipse ry="2.98" rx="4.58" opacity=".1" fill="#f85565" cy="69.67" cx="77.58"></ellipse><g stroke-linejoin="round" stroke-linecap="round" fill="none"><path stroke-width="4" stroke="#fbc0aa" d="m64 67v4"></path><path stroke-width="2" stroke="#515570" opacity=".2" d="m55 56h-9.25"></path><path stroke-width="2" stroke="#515570" opacity=".2" d="m82 56h-9.25"></path></g><path opacity=".4" fill="#f85565" d="m64 84c5 0 7-3 7-3h-14s2 3 7 3z"></path><path fill="#f85565" d="m65.07 78.93-.55.55a.73.73 0 0 1 -1 0l-.55-.55c-1.14-1.14-2.93-.93-4.27.47l-1.7 1.6h14l-1.66-1.6c-1.34-1.4-3.13-1.61-4.27-.47z"></path></svg></div>
                                            </div>
                                            <div class="card__menu" onclick="event.stopPropagation(); window.location.href='realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>';"><svg xmlns="http://www.w3.org/2000/svg" width="4" viewBox="0 0 4 20" height="20" fill="none"><g fill="#000"><path d="m2 4c1.10457 0 2-.89543 2-2s-.89543-2-2-2-2 .89543-2 2 .89543 2 2 2z"></path><path d="m2 12c1.10457 0 2-.8954 2-2 0-1.10457-.89543-2-2-2s-2 .89543-2 2c0 1.1046.89543 2 2 2z"></path><path d="m2 20c1.10457 0 2-.8954 2-2s-.89543-2-2-2-2 .8954-2 2 .89543 2 2 2z"></path></g></svg></div>
                                        </div>
                                        <div class="card__title"><?= htmlspecialchars($missao['nome']); ?></div>
                                        <div class="card__subtitle"><?= htmlspecialchars($missao['descricao']); ?></div>
                                        <div class="card__indicator"><span class="card__indicator-amount"><?= $missao['moedas']; ?> moedas</span> / <span class="card__indicator-percentage"><?= $missao['xp']; ?> XP</span></div>
                                        <?php if (!empty($missao['data_limite'])): 
                                            $data_limite = new DateTime($missao['data_limite']);
                                            $hoje = new DateTime();
                                            $diferenca = $hoje->diff($data_limite);
                                            $dias_restantes = $diferenca->days;
                                            $cor_badge = ($data_limite < $hoje) ? 'danger' : (($dias_restantes <= 3) ? 'warning' : 'info');
                                        ?>
                                        <div style="margin-top: 10px; font-size: 0.85em;">
                                            <span class="badge badge-<?= $cor_badge; ?>" style="padding: 5px 10px;">
                                                <i class="fa fa-calendar"></i> 
                                                <?php if ($data_limite < $hoje): ?>
                                                    Expirada em <?= $data_limite->format('d/m/Y'); ?>
                                                <?php else: ?>
                                                    Expira em <?= $dias_restantes; ?> dia(s) - <?= $data_limite->format('d/m/Y'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="card__progress"><progress max="100" value="0"></progress></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fa fa-inbox"></i>
                                </div>
                                <h4>Nenhuma missão disponível</h4>
                                <p>No momento não há missões disponíveis para sua turma. Volte mais tarde!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
