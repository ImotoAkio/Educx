<?php
session_start();
require '../../../db.php';

$quiz_id = $_GET['id'];

// Recuperar perguntas e alternativas
$sql = "SELECT p.id AS pergunta_id, p.texto AS pergunta_texto, a.id AS alternativa_id, a.texto AS alternativa_texto, a.correta
        FROM perguntas p
        LEFT JOIN alternativas a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['quiz_id' => $quiz_id]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar perguntas e alternativas
$quiz = [];
foreach ($dados as $dado) {
    $quiz[$dado['pergunta_id']]['texto'] = $dado['pergunta_texto'];
    $quiz[$dado['pergunta_id']]['alternativas'][] = [
        'id' => $dado['alternativa_id'],
        'texto' => $dado['alternativa_texto'],
        'correta' => $dado['correta']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
</head>
<body>
    <h1>Quiz</h1>
    <form method="POST" action="resultado_quiz.php">
        <?php foreach ($quiz as $pergunta_id => $pergunta): ?>
            <div>
                <h3><?= htmlspecialchars($pergunta['texto']); ?></h3>
                <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                    <label>
                        <input type="radio" name="respostas[<?= $pergunta_id; ?>]" value="<?= $alternativa['id']; ?>">
                        <?= htmlspecialchars($alternativa['texto']); ?>
                    </label>
                    <br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>