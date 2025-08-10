<?php
require 'db.php';

// Verifica se o aluno e o quiz foram passados na URL
if (!isset($_POST['aluno_id']) || !isset($_POST['quiz_id']) || !isset($_POST['respostas'])) {
    die("Dados insuficientes para processar o quiz.");
}

$aluno_id = (int) $_POST['aluno_id'];
$quiz_id = (int) $_POST['quiz_id'];
$respostas = $_POST['respostas'];

// Verifica se o quiz existe
$stmt_quiz = $pdo->prepare("SELECT id FROM quizzes WHERE id = :quiz_id");
$stmt_quiz->execute([':quiz_id' => $quiz_id]);
if ($stmt_quiz->rowCount() === 0) {
    die("Quiz não encontrado.");
}

// Processa as respostas
$acertos = 0;
$total_perguntas = count($respostas);

foreach ($respostas as $pergunta_id => $alternativa_id) {
    // Verifica se a alternativa é correta
    $stmt = $pdo->prepare("
        SELECT correta 
        FROM alternativas 
        WHERE id = :alternativa_id AND pergunta_id = :pergunta_id
    ");
    $stmt->execute([
        ':alternativa_id' => $alternativa_id,
        ':pergunta_id' => $pergunta_id
    ]);
    $correta = $stmt->fetchColumn();

    if ($correta) {
        $acertos++;
    }
}

// Calcula a pontuação do aluno
$pontuacao = $acertos * 10; // Exemplo: 10 pontos por resposta correta

// Atualiza o XP do aluno
$stmt = $pdo->prepare("UPDATE alunos SET xp_total = xp_total + :pontuacao WHERE id = :aluno_id");
$stmt->execute([
    ':pontuacao' => $pontuacao,
    ':aluno_id' => $aluno_id
]);

// Marca o quiz como finalizado para o aluno
$stmt = $pdo->prepare("
    INSERT INTO quizzes_finalizados (aluno_id, quiz_id, data_finalizacao) 
    VALUES (:aluno_id, :quiz_id, NOW())
");
$stmt->execute([
    ':aluno_id' => $aluno_id,
    ':quiz_id' => $quiz_id
]);

// Redireciona para uma página de resultado
header("Location: resultado_quiz.php?aluno_id=$aluno_id&quiz_id=$quiz_id&acertos=$acertos&total=$total_perguntas");
exit;
?>