<?php
require 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o ID do aluno foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do aluno não fornecido.");
}

$aluno_id = (int) $_GET['id'];

// Consulta todas as missões disponíveis
$stmt = $pdo->prepare("SELECT * FROM missoes WHERE status = 'ativa'");
$stmt->execute();
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="asset/louja.css">
    <title>Missões</title>
    <style>
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
    </style>
</head>
<body>
    <h1>Missões Disponíveis</h1>

    <?php if (!empty($missoes)): ?>
        <?php foreach ($missoes as $missao): ?>
            <div class="missao">
                <h3><?= htmlspecialchars($missao['nome']); ?></h3>
                <p><?= htmlspecialchars($missao['descricao']); ?></p>
                <p><strong>XP:</strong> <?= htmlspecialchars($missao['xp']); ?> | <strong>Moedas:</strong> <?= htmlspecialchars($missao['moedas']); ?></p>
                <a href="realizar_missao.php?aluno_id=<?= $aluno_id; ?>&missao_id=<?= $missao['id']; ?>" class="btn">Realizar Missão</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma missão disponível no momento.</p>
    <?php endif; ?>
</body>
</html>
