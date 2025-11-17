<?php
require 'db.php';

// Verificar se os IDs foram fornecidos
$aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
$missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;

if (!$aluno_id || !$missao_id) {
    die("Erro: ID do aluno ou da missão não foi fornecido.");
}

try {
    // Consulta os dados do aluno
    $stmt = $pdo->prepare("SELECT id, nome, moedas, xp_atual, xp_total FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("Erro: Aluno não encontrado.");
    }

    // Consulta os dados da missão
    $stmt = $pdo->prepare("SELECT id, nome, descricao, xp, moedas, link FROM missoes WHERE id = :id");
    $stmt->execute([':id' => $missao_id]);
    $missao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$missao) {
        die("Erro: Missão não encontrada.");
    }
} catch (PDOException $e) {
    die("Erro ao consultar o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
    $missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;

    if (!$aluno_id || !$missao_id) {
        die("Erro: Dados inválidos.");
    }

    // Verificar se o aluno já realizou ou solicitou essa missão
    $stmt = $pdo->prepare("
        SELECT * 
        FROM solicitacoes_missoes 
        WHERE aluno_id = :aluno_id 
          AND missao_id = :missao_id 
          AND status IN ('pendente', 'aprovado')
    ");
    $stmt->execute([':aluno_id' => $aluno_id, ':missao_id' => $missao_id]);
    $solicitacao_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redireciona para blok.php caso já exista uma solicitação
    if ($solicitacao_existente) {
        header("Location: blok.php?aluno_id=$aluno_id&missao_id=$missao_id");
        exit;
    }

    // Insere a nova solicitação
    $stmt = $pdo->prepare("INSERT INTO solicitacoes_missoes (aluno_id, missao_id, data_solicitacao) VALUES (:aluno_id, :missao_id, NOW())");
    $stmt->execute([':aluno_id' => $aluno_id, ':missao_id' => $missao_id]);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Missão</title>
    <link rel="stylesheet" href="asset/loja.css">
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
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

        .hero-card {
            min-height: auto !important;
            height: auto !important;
        }

        .hero-card .card-content {
            padding: 30px;
            min-height: auto;
            text-align: center;
        }

        .hero-card .card-content h1 {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.2em;
            color: #17171f;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .hero-card .card-content h3 {
            font-family: 'Press Start 2P', cursive;
            font-size: 1em;
            color: #17171f;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .hero-card .card-content p {
            font-size: 16px;
            color: #17171f;
            margin-bottom: 20px;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .success-icon {
            font-size: 80px;
            color: #4ade80;
            margin: 20px 0;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .mission-image {
            width: 200px;
            height: auto;
            margin: 20px auto;
            display: block;
            border-radius: 15px;
        }

        .reward-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 25px 0;
            flex-wrap: wrap;
        }

        .reward-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(23, 23, 31, 0.1);
            padding: 15px 20px;
            border-radius: 12px;
        }

        .reward-item img {
            width: 32px;
            height: 32px;
        }

        .reward-item span {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.1em;
            color: #17171f;
        }

        .btn-voltar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-family: 'Orbitron', monospace;
        }

        .btn-voltar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-voltar:active {
            transform: translateY(0);
        }

        .alert-box {
            background: rgba(74, 222, 128, 0.1);
            border: 2px solid #4ade80;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            color: #17171f;
        }

        .alert-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert-box i {
            color: #4ade80;
            margin-right: 10px;
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
                    <a href="aluno.php?id=<?= $aluno['id']; ?>" class="btn" style="color: #ffffff;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </div>
                <div class="col-4-sm center">
                    <h1 class="page-title">Confirmação</h1>
                </div>
                <div class="col-4-sm right"></div>
            </div>
        </div>
    </header>

    <section>
        <div class="container-fluid">
            <!-- Cartão principal -->
            <div class="row">
                <div class="col-12">
                    <div class="hero-card">
                        <div class="card-content">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            
                            <h3>Parabéns!</h3>
                            
                            <p>Você acabou de realizar a missão:</p>
                            
                            <h1><?= htmlspecialchars($missao['nome']); ?></h1>
                            
                            <img src="asset/img/professor.png" alt="Professor" class="mission-image">
                            
                            <div class="reward-info">
                                <div class="reward-item">
                                    <img src="asset/img/xp.gif" alt="XP">
                                    <span>+<?= htmlspecialchars($missao['xp']); ?> XP</span>
                                </div>
                                <div class="reward-item">
                                    <img src="asset/img/coin.gif" alt="Moedas">
                                    <span>+<?= htmlspecialchars($missao['moedas']); ?> moedas</span>
                                </div>
                            </div>
                            
                            <div class="alert-box">
                                <p>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Importante:</strong> Vá até a secretaria ou professor responsável e apresente a comprovação da sua missão para receber suas recompensas!
                                </p>
                            </div>
                            
                            <form action="aluno.php?id=<?= $aluno_id; ?>" method="GET">
                                <input type="hidden" name="id" value="<?= $aluno_id; ?>">
                                <button type="submit" class="btn-voltar">
                                    <i class="fas fa-home"></i> Voltar para Início
                                </button>
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
