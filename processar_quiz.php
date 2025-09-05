<?php
require 'db.php';

// Verifica se o aluno e o quiz foram passados na URL
if (!isset($_POST['aluno_id']) || !isset($_POST['quiz_id']) || !isset($_POST['respostas'])) {
    die("Dados insuficientes para processar o quiz.");
}

$aluno_id = (int) $_POST['aluno_id'];
$quiz_id = (int) $_POST['quiz_id'];
$respostas = $_POST['respostas'];

try {
    // Inicia transação
    $pdo->beginTransaction();
    
    // Verifica se o quiz existe
    $stmt_quiz = $pdo->prepare("SELECT id, nome, moedas_recompensa FROM quizzes WHERE id = :quiz_id");
    $stmt_quiz->execute([':quiz_id' => $quiz_id]);
    $quiz_info = $stmt_quiz->fetch(PDO::FETCH_ASSOC);
    
    if (!$quiz_info) {
        throw new Exception("Quiz não encontrado.");
    }
    
    // Verifica se o aluno existe
    $stmt_aluno = $pdo->prepare("SELECT id, nome FROM alunos WHERE id = :aluno_id");
    $stmt_aluno->execute([':aluno_id' => $aluno_id]);
    $aluno_info = $stmt_aluno->fetch(PDO::FETCH_ASSOC);
    
    if (!$aluno_info) {
        throw new Exception("Aluno não encontrado.");
    }
    
    // Verifica se o aluno já finalizou este quiz
    $stmt_check = $pdo->prepare("SELECT id FROM quizzes_finalizados WHERE aluno_id = :aluno_id AND quiz_id = :quiz_id");
    $stmt_check->execute([':aluno_id' => $aluno_id, ':quiz_id' => $quiz_id]);
    
    if ($stmt_check->rowCount() > 0) {
        throw new Exception("Você já finalizou este quiz anteriormente.");
    }
    
    // Processa as respostas
    $acertos = 0;
    $total_perguntas = count($respostas);
    $respostas_detalhadas = [];
    
    foreach ($respostas as $pergunta_id => $alternativa_id) {
        // Verifica se a alternativa é correta
        $stmt = $pdo->prepare("
            SELECT a.correta, a.texto as alternativa_texto, p.texto as pergunta_texto
            FROM alternativas a
            JOIN perguntas p ON a.pergunta_id = p.id
            WHERE a.id = :alternativa_id AND a.pergunta_id = :pergunta_id
        ");
        $stmt->execute([
            ':alternativa_id' => $alternativa_id,
            ':pergunta_id' => $pergunta_id
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resultado) {
            throw new Exception("Alternativa inválida encontrada.");
        }
        
        $respostas_detalhadas[] = [
            'pergunta_id' => $pergunta_id,
            'alternativa_id' => $alternativa_id,
            'pergunta_texto' => $resultado['pergunta_texto'],
            'alternativa_texto' => $resultado['alternativa_texto'],
            'correta' => $resultado['correta']
        ];
        
        if ($resultado['correta']) {
            $acertos++;
        }
    }
    
    // Usa as moedas definidas no quiz (não calcula baseado em acertos)
    $moedas_recompensa = $quiz_info['moedas_recompensa'];
    
    // Calcula a porcentagem de acerto
    $porcentagem_acerto = ($total_perguntas > 0) ? round(($acertos / $total_perguntas) * 100, 1) : 0;
    
    // Verifica se o aluno atingiu 70% para receber moedas
    $recebe_moedas = ($porcentagem_acerto >= 70);
    
    // Atualiza o XP do aluno (mantém o cálculo baseado em acertos para XP)
    $pontuacao_xp = $acertos * 10;
    $stmt = $pdo->prepare("UPDATE alunos SET xp_total = xp_total + :pontuacao WHERE id = :aluno_id");
    $stmt->execute([
        ':pontuacao' => $pontuacao_xp,
        ':aluno_id' => $aluno_id
    ]);
    
    // Atualiza as moedas do aluno apenas se atingiu 70%
    if ($recebe_moedas) {
        $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas + :moedas WHERE id = :aluno_id");
        $stmt->execute([
            ':moedas' => $moedas_recompensa,
            ':aluno_id' => $aluno_id
        ]);
        
        // Registra a transação de moedas no histórico
        $stmt = $pdo->prepare("
            INSERT INTO historico_moedas (aluno_id, quantidade, tipo, descricao, data_transacao) 
            VALUES (:aluno_id, :quantidade, 'ganho', :descricao, NOW())
        ");
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':quantidade' => $moedas_recompensa,
            ':descricao' => "Recompensa do quiz: {$quiz_info['nome']} ({$porcentagem_acerto}% de acerto)"
        ]);
    }
    
    // Marca o quiz como finalizado para o aluno
    $stmt = $pdo->prepare("
        INSERT INTO quizzes_finalizados (aluno_id, quiz_id, data_finalizacao, pontuacao, acertos, total_perguntas) 
        VALUES (:aluno_id, :quiz_id, NOW(), :pontuacao, :acertos, :total_perguntas)
    ");
    $stmt->execute([
        ':aluno_id' => $aluno_id,
        ':quiz_id' => $quiz_id,
        ':pontuacao' => $pontuacao_xp,
        ':acertos' => $acertos,
        ':total_perguntas' => $total_perguntas
    ]);
    
    // Salva as respostas individuais (se a tabela existir)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO respostas_quiz (aluno_id, quiz_id, pergunta_id, alternativa_id, correta, data_resposta)
            VALUES (:aluno_id, :quiz_id, :pergunta_id, :alternativa_id, :correta, NOW())
        ");
        
        foreach ($respostas_detalhadas as $resposta) {
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':quiz_id' => $quiz_id,
                ':pergunta_id' => $resposta['pergunta_id'],
                ':alternativa_id' => $resposta['alternativa_id'],
                ':correta' => $resposta['correta']
            ]);
        }
    } catch (Exception $e) {
        // Se a tabela respostas_quiz não existir, ignora o erro
        // echo "Aviso: Não foi possível salvar as respostas individuais: " . $e->getMessage();
    }
    
    // Confirma a transação
    $pdo->commit();
    
    // Redireciona para uma página de resultado
    $moedas_param = $recebe_moedas ? $moedas_recompensa : 0;
    header("Location: resultado_quiz.php?aluno_id=$aluno_id&quiz_id=$quiz_id&acertos=$acertos&total=$total_perguntas&pontuacao=$pontuacao_xp&moedas=$moedas_param&porcentagem=$porcentagem_acerto");
    exit;
    
} catch (Exception $e) {
    // Reverte a transação em caso de erro
    $pdo->rollBack();
    
    // Redireciona com mensagem de erro
    $erro = urlencode($e->getMessage());
    header("Location: quiz.php?aluno_id=$aluno_id&quiz_id=$quiz_id&erro=$erro");
    exit;
}
?>