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
      AND (m.turma_id = :turma_id OR m.turma_id IS NULL OR m.turma_id = 0 OR m.turma_id = '')
      AND NOT EXISTS (
          SELECT 1 FROM solicitacoes_missoes sm
          WHERE sm.aluno_id = :aluno_id AND sm.missao_id = m.id
      )
");
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

        /* ===== ESTILOS PARA MISSÕES MELHORADAS ===== */
        
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
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .missao-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white;
        }
        
        .missao-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            pointer-events: none;
        }
        
        .missao-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .missao-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .missao-icon {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: #FFD700;
        }
        
        .missao-title h4 {
            margin: 0 0 10px 0;
            font-size: 1.3em;
            font-weight: 600;
            color: white;
        }
        
        .missao-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-xp {
            background: rgba(255, 193, 7, 0.9);
            color: #000;
        }
        
        .badge-coins {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }
        
        .missao-content {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .missao-descricao {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .missao-link {
            margin-top: 10px;
        }
        
        .link-externo {
            color: #FFD700;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }
        
        .link-externo:hover {
            color: #FFF;
            text-decoration: none;
        }
        
        .missao-footer {
            position: relative;
            z-index: 1;
        }
        
        .btn-realizar-missao {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            color: #000;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-realizar-missao:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
            color: #000;
        }
        
        .btn-realizar-missao:active {
            transform: translateY(0);
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
            }
            
            .missao-card {
                padding: 15px;
            }
            
            .missao-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .missao-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .missao-badges {
                justify-content: center;
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
        
        .missao-card {
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
                                    <div class="missao-card" data-aos="fade-up" data-aos-delay="<?= $index * 100; ?>">
                                        <div class="missao-header">
                                            <div class="missao-icon">
                                                <i class="fa fa-trophy"></i>
                                            </div>
                                            <div class="missao-title">
                                                <h4><?= htmlspecialchars($missao['nome']); ?></h4>
                                                <div class="missao-badges">
                                                    <span class="badge badge-xp">
                                                        <i class="fa fa-star"></i> <?= $missao['xp']; ?> XP
                                                    </span>
                                                    <span class="badge badge-coins">
                                                        <i class="fa fa-coins"></i> <?= $missao['moedas']; ?> moedas
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="missao-content">
                                            <p class="missao-descricao"><?= htmlspecialchars($missao['descricao']); ?></p>
                                            
                                            <?php if (!empty($missao['link'])): ?>
                                                <div class="missao-link">
                                                    <a href="<?= htmlspecialchars($missao['link']); ?>" target="_blank" class="link-externo">
                                                        <i class="fa fa-external-link"></i> Ver detalhes
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="missao-footer">
                                            <button class="btn-realizar-missao" onclick="window.location.href='realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>';">
                                                <i class="fa fa-play"></i>
                                                <span>Realizar Missão</span>
                                            </button>
                                        </div>
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
