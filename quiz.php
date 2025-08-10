<?php
require 'db.php';

// Verifica se o aluno e o quiz foram passados na URL
if (!isset($_GET['aluno_id']) || !isset($_GET['quiz_id'])) {
    die("Aluno ou Quiz não especificado.");
}

$aluno_id = (int) $_GET['aluno_id'];
$quiz_id = (int) $_GET['quiz_id'];

// Consulta o quiz e suas perguntas/alternativas
$stmt_quiz = $pdo->prepare("
    SELECT q.nome AS quiz_nome, q.descricao AS quiz_descricao, p.id AS pergunta_id, p.texto AS pergunta_texto, 
           a.id AS alternativa_id, a.texto AS alternativa_texto
    FROM quizzes q
    JOIN perguntas p ON q.id = p.quiz_id
    JOIN alternativas a ON p.id = a.pergunta_id
    WHERE q.id = :quiz_id
");
$stmt_quiz->execute([':quiz_id' => $quiz_id]);
$dados_quiz = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

if (empty($dados_quiz)) {
    die("Quiz não encontrado ou sem perguntas.");
}

// Organiza as perguntas e alternativas
$quiz = [];
foreach ($dados_quiz as $dado) {
    $quiz['nome'] = $dado['quiz_nome'];
    $quiz['descricao'] = $dado['quiz_descricao'];
    $quiz['perguntas'][$dado['pergunta_id']]['texto'] = $dado['pergunta_texto'];
    $quiz['perguntas'][$dado['pergunta_id']]['alternativas'][] = [
        'id' => $dado['alternativa_id'],
        'texto' => $dado['alternativa_texto']
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Quiz</title>
    <link rel="stylesheet" href="asset/loja.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.0.0/css/fontawesome.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.0.0/css/all.min.css" rel="stylesheet">
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

        .quiz-container {
            margin: 20px auto;
            max-width: 800px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .quiz-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .quiz-header p {
            font-size: 1.2rem;
            color: #666;
        }

        .pergunta {
            margin-bottom: 20px;
        }

        .pergunta h3 {
            font-size: 1.5rem;
            color: #333;
        }

        .alternativa {
            margin-left: 20px;
            margin-bottom: 10px;
        }

        .btn-submit {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 1.2rem;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?= htmlspecialchars($quiz['nome']); ?></h1>
            <p><?= htmlspecialchars($quiz['descricao']); ?></p>
        </div>
        <form method="POST" action="processar_quiz.php">
            <input type="hidden" name="aluno_id" value="<?= $aluno_id; ?>">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id; ?>">
            <?php foreach ($quiz['perguntas'] as $pergunta_id => $pergunta): ?>
                <div class="pergunta">
                    <h3><?= htmlspecialchars($pergunta['texto']); ?></h3>
                    <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                        <div class="alternativa">
                            <input type="radio" id="alternativa_<?= $alternativa['id']; ?>" name="respostas[<?= $pergunta_id; ?>]" value="<?= $alternativa['id']; ?>" required>
                            <label for="alternativa_<?= $alternativa['id']; ?>"><?= htmlspecialchars($alternativa['texto']); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn-submit">Enviar Respostas</button>
        </form>
    </div>
</body>
</html>
