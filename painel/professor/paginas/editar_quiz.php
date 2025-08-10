<?php
session_start();
require '../../../db.php';

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header('Location: ../../login.php');
    exit;
}

$professor_id = $_SESSION['professor_id'];
$quiz_id = $_GET['id'];

// Adicionar uma pergunta
if (isset($_POST['add_pergunta'])) {
    $texto = $_POST['texto'];

    $sql = "INSERT INTO perguntas (quiz_id, texto) VALUES (:quiz_id, :texto)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'quiz_id' => $quiz_id,
        'texto' => $texto
    ]);
}

// Adicionar uma alternativa
if (isset($_POST['add_alternativa'])) {
    $pergunta_id = $_POST['pergunta_id'];
    $texto = $_POST['texto'];
    $correta = isset($_POST['correta']) ? 1 : 0;

    $sql = "INSERT INTO alternativas (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'pergunta_id' => $pergunta_id,
        'texto' => $texto,
        'correta' => $correta
    ]);
}

// Recuperar perguntas e alternativas
$sql = "SELECT * FROM perguntas WHERE quiz_id = :quiz_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['quiz_id' => $quiz_id]);
$perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Quiz</title>
</head>
<body>
    <h1>Editar Quiz</h1>
    <form method="POST">
        <label for="texto">Adicionar Pergunta:</label>
        <input type="text" id="texto" name="texto" required>
        <button type="submit" name="add_pergunta">Adicionar</button>
    </form>

    <h2>Perguntas</h2>
    <?php foreach ($perguntas as $pergunta): ?>
        <div>
            <h3><?= htmlspecialchars($pergunta['texto']); ?></h3>
            <form method="POST">
                <input type="hidden" name="pergunta_id" value="<?= $pergunta['id']; ?>">
                <label for="texto">Adicionar Alternativa:</label>
                <input type="text" id="texto" name="texto" required>
                <label for="correta">Correta:</label>
                <input type="checkbox" id="correta" name="correta">
                <button type="submit" name="add_alternativa">Adicionar</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>