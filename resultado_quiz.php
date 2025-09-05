<?php
require 'db.php';

// Verifica se os parâmetros foram passados
if (!isset($_GET['aluno_id']) || !isset($_GET['quiz_id']) || !isset($_GET['acertos']) || !isset($_GET['total'])) {
    die("Parâmetros insuficientes para exibir o resultado.");
}

$aluno_id = (int) $_GET['aluno_id'];
$quiz_id = (int) $_GET['quiz_id'];
$acertos = (int) $_GET['acertos'];
$total = (int) $_GET['total'];
$pontuacao = isset($_GET['pontuacao']) ? (int) $_GET['pontuacao'] : ($acertos * 10);
$moedas = isset($_GET['moedas']) ? (int) $_GET['moedas'] : 0;
$porcentagem = isset($_GET['porcentagem']) ? (float) $_GET['porcentagem'] : (($total > 0) ? round(($acertos / $total) * 100, 1) : 0);

// Busca informações do aluno e do quiz
$stmt = $pdo->prepare("SELECT nome FROM alunos WHERE id = :aluno_id");
$stmt->execute([':aluno_id' => $aluno_id]);
$aluno_nome = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT nome FROM quizzes WHERE id = :quiz_id");
$stmt->execute([':quiz_id' => $quiz_id]);
$quiz_nome = $stmt->fetchColumn();

// Calcula a porcentagem de acerto
$porcentagem = ($total > 0) ? round(($acertos / $total) * 100, 1) : 0;

// Define a mensagem baseada na performance
if ($porcentagem >= 90) {
    $mensagem = "Excelente! Você se saiu muito bem!";
    $cor_mensagem = "#28a745";
    $icone = "fas fa-star";
} elseif ($porcentagem >= 70) {
    $mensagem = "Muito bom! Você atingiu a meta para receber moedas!";
    $cor_mensagem = "#17a2b8";
    $icone = "fas fa-thumbs-up";
} elseif ($porcentagem >= 50) {
    $mensagem = "Bom trabalho! Você pode melhorar ainda mais!";
    $cor_mensagem = "#ffc107";
    $icone = "fas fa-check";
} else {
    $mensagem = "Não desanime! Continue estudando!";
    $cor_mensagem = "#dc3545";
    $icone = "fas fa-heart";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado do Quiz - <?= htmlspecialchars($quiz_nome); ?></title>
    <link rel="stylesheet" href="asset/loja.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .resultado-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
        }
        
        .resultado-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
        }
        
        .resultado-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .resultado-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .resultado-content {
            padding: 40px 30px;
        }
        
        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            position: relative;
            background: conic-gradient(from 0deg, <?= $cor_mensagem ?> 0deg, <?= $cor_mensagem ?> <?= ($porcentagem / 100) * 360 ?>deg, #e9ecef <?= ($porcentagem / 100) * 360 ?>deg, #e9ecef 360deg);
        }
        
        .score-circle::before {
            content: '';
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: white;
        }
        
        .score-text {
            position: relative;
            z-index: 1;
        }
        
        .mensagem {
            font-size: 1.3rem;
            color: <?= $cor_mensagem ?>;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            border-left: 5px solid #667eea;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-voltar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-voltar:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .xp-gain {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .resultado-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .resultado-header {
                padding: 30px 20px;
            }
            
            .resultado-header h1 {
                font-size: 2rem;
            }
            
            .resultado-content {
                padding: 30px 20px;
            }
            
            .score-circle {
                width: 150px;
                height: 150px;
                font-size: 2.5rem;
            }
            
            .score-circle::before {
                width: 120px;
                height: 120px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="resultado-container">
        <div class="resultado-header">
            <h1><i class="fas fa-trophy"></i> Resultado</h1>
            <p><?= htmlspecialchars($quiz_nome); ?></p>
        </div>
        
        <div class="resultado-content">
            <div class="score-circle">
                <div class="score-text"><?= $porcentagem ?>%</div>
            </div>
            
            <div class="mensagem">
                <i class="<?= $icone ?>"></i> <?= $mensagem ?>
            </div>
            
            <div class="xp-gain">
                <i class="fas fa-star"></i> +<?= $pontuacao ?> XP ganhos!
            </div>
            
            <?php if ($moedas > 0): ?>
            <div class="moedas-gain" style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #333; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-coins"></i> +<?= $moedas ?> Moedas ganhas!
                <br><small style="font-size: 0.8em; opacity: 0.8;">Parabéns! Você atingiu 70% ou mais de acerto!</small>
            </div>
            <?php else: ?>
            <div class="moedas-info" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-info-circle"></i> Nenhuma moeda ganha
                <br><small style="font-size: 0.8em; opacity: 0.8;">Você precisa acertar 70% ou mais para receber moedas. Tente novamente!</small>
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $acertos ?></h3>
                    <p>Acertos</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total - $acertos ?></h3>
                    <p>Erros</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total ?></h3>
                    <p>Total</p>
                </div>
                <div class="stat-card">
                    <h3><?= $pontuacao ?></h3>
                    <p>Pontos</p>
                </div>
            </div>
            
            <a href="aluno.php?id=<?= $aluno_id; ?>" class="btn-voltar">
                <i class="fas fa-home"></i> Voltar ao Perfil
            </a>
        </div>
    </div>
    
    <script>
        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.resultado-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
