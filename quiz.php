<?php
require 'db.php';

// Verifica se o aluno e o quiz foram passados na URL
if (!isset($_GET['aluno_id']) || !isset($_GET['quiz_id'])) {
    die("Aluno ou Quiz não especificado.");
}

$aluno_id = (int) $_GET['aluno_id'];
$quiz_id = (int) $_GET['quiz_id'];

// Primeiro, verifica se o quiz existe
$stmt_check = $pdo->prepare("SELECT id, nome, descricao, moedas_recompensa FROM quizzes WHERE id = :quiz_id");
$stmt_check->execute([':quiz_id' => $quiz_id]);


$quiz_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$quiz_info) {
    die("Quiz não encontrado.");
}

// Verifica se o quiz tem perguntas
$stmt_perguntas = $pdo->prepare("SELECT COUNT(*) as total FROM perguntas WHERE quiz_id = :quiz_id");
$stmt_perguntas->execute([':quiz_id' => $quiz_id]);
$total_perguntas = $stmt_perguntas->fetch(PDO::FETCH_ASSOC)['total'];

if ($total_perguntas == 0) {
    die("Este quiz não possui perguntas.");
}

// Consulta o quiz e suas perguntas/alternativas
$stmt_quiz = $pdo->prepare("
    SELECT q.nome AS quiz_nome, q.descricao AS quiz_descricao, 
           p.id AS pergunta_id, p.texto AS pergunta_texto, 
           a.id AS alternativa_id, a.texto AS alternativa_texto, a.correta
    FROM quizzes q
    JOIN perguntas p ON q.id = p.quiz_id
    LEFT JOIN alternativas a ON p.id = a.pergunta_id
    WHERE q.id = :quiz_id
    ORDER BY p.id, a.id
");
$stmt_quiz->execute([':quiz_id' => $quiz_id]);
$dados_quiz = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

if (empty($dados_quiz)) {
    die("Erro ao carregar perguntas do quiz.");
}

// Organiza as perguntas e alternativas
$quiz = [
    'nome' => $quiz_info['nome'],
    'descricao' => $quiz_info['descricao'],
    'moedas_recompensa' => $quiz_info['moedas_recompensa'],
    'perguntas' => []
];

foreach ($dados_quiz as $dado) {
    $pergunta_id = $dado['pergunta_id'];
    
    // Adiciona a pergunta se ainda não existe
    if (!isset($quiz['perguntas'][$pergunta_id])) {
        $quiz['perguntas'][$pergunta_id] = [
            'texto' => $dado['pergunta_texto'],
            'alternativas' => []
        ];
    }
    
    // Adiciona a alternativa se existe
    if ($dado['alternativa_id']) {
        $quiz['perguntas'][$pergunta_id]['alternativas'][] = [
            'id' => $dado['alternativa_id'],
            'texto' => $dado['alternativa_texto'],
            'correta' => $dado['correta']
        ];
    }
}

// Verifica se todas as perguntas têm alternativas
foreach ($quiz['perguntas'] as $pergunta_id => $pergunta) {
    if (empty($pergunta['alternativas'])) {
        die("A pergunta ID {$pergunta_id} não possui alternativas.");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['nome']); ?> - Quiz</title>
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
        }
        
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .quiz-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .quiz-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .quiz-content {
            padding: 30px;
        }
        
        .pergunta {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #667eea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .pergunta h3 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .alternativas-container {
            display: grid;
            gap: 12px;
        }
        
        .alternativa {
            position: relative;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .alternativa:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .alternativa input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.2);
            accent-color: #667eea;
        }
        
        .alternativa label {
            cursor: pointer;
            font-size: 1rem;
            color: #333;
            flex: 1;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(-1px);
        }
        
        .progress-bar {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .quiz-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .voltar {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .voltar:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .quiz-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .quiz-header {
                padding: 20px;
            }
            
            .quiz-header h1 {
                font-size: 2rem;
            }
            
            .quiz-content {
                padding: 20px;
            }
            
            .pergunta {
                padding: 20px;
            }
            
            .alternativa {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <a href="aluno.php?id=<?= $aluno_id; ?>" class="voltar">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
    
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><i class="fas fa-question-circle"></i> <?= htmlspecialchars($quiz['nome']); ?></h1>
            <p><?= htmlspecialchars($quiz['descricao']); ?></p>
        </div>
        
        <div class="quiz-content">
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 5px solid #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars(urldecode($_GET['erro'])); ?>
                </div>
            <?php endif; ?>
            
            <!-- Instruções do Quiz -->
            <div class="instrucoes-quiz" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3; border-radius: 15px; padding: 20px; margin-bottom: 25px;">
                <h4 style="color: #1976d2; margin-bottom: 15px; display: flex; align-items: center;">
                    <i class="fas fa-info-circle" style="margin-right: 10px;"></i>
                    Instruções do Quiz
                </h4>
                <div style="color: #333; line-height: 1.6;">
                    <p><strong>📝 Como funciona:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Responda todas as perguntas do quiz</li>
                        <li>Você receberá <strong><?= $quiz['moedas_recompensa'] ?? 10; ?> moedas</strong> se acertar <strong>70% ou mais</strong></li>
                        <li>Você sempre ganha <strong>XP</strong> baseado no número de acertos (10 XP por acerto)</li>
                        <li>Você só pode fazer este quiz <strong>uma vez</strong></li>
                    </ul>
                    <p style="margin-top: 15px; font-weight: 600; color: #1976d2;">
                        <i class="fas fa-trophy"></i> Meta: 70% de acerto para ganhar moedas!
                    </p>
                </div>
            </div>
            
            <div class="quiz-info">
                <span><i class="fas fa-list"></i> <?= count($quiz['perguntas']); ?> perguntas</span>
                <span><i class="fas fa-clock"></i> Tempo ilimitado</span>
                <span><i class="fas fa-coins"></i> <?= $quiz['moedas_recompensa'] ?? 10; ?> moedas (se 70%+)</span>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%"></div>
            </div>
            
            <form method="POST" action="processar_quiz.php" id="quizForm">
                <input type="hidden" name="aluno_id" value="<?= $aluno_id; ?>">
                <input type="hidden" name="quiz_id" value="<?= $quiz_id; ?>">
                
                <?php 
                $pergunta_num = 1;
                foreach ($quiz['perguntas'] as $pergunta_id => $pergunta): 
                ?>
                    <div class="pergunta" data-pergunta="<?= $pergunta_num; ?>">
                        <h3>
                            <span class="pergunta-numero"><?= $pergunta_num; ?>.</span>
                            <?= htmlspecialchars($pergunta['texto']); ?>
                        </h3>
                        
                        <div class="alternativas-container">
                            <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                                <div class="alternativa">
                                    <input type="radio" 
                                           id="alternativa_<?= $alternativa['id']; ?>" 
                                           name="respostas[<?= $pergunta_id; ?>]" 
                                           value="<?= $alternativa['id']; ?>" 
                                           required>
                                    <label for="alternativa_<?= $alternativa['id']; ?>">
                                        <?= htmlspecialchars($alternativa['texto']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                $pergunta_num++;
                endforeach; 
                ?>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Enviar Respostas
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Atualizar barra de progresso conforme o usuário responde
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('quizForm');
            const progressFill = document.querySelector('.progress-fill');
            const totalPerguntas = <?= count($quiz['perguntas']); ?>;
            
            function updateProgress() {
                const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
                const progress = (answeredQuestions / totalPerguntas) * 100;
                progressFill.style.width = progress + '%';
            }
            
            // Atualizar progresso quando uma alternativa é selecionada
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', updateProgress);
            });
            
            // Validação do formulário
            form.addEventListener('submit', function(e) {
                const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
                
                if (answeredQuestions < totalPerguntas) {
                    e.preventDefault();
                    alert('Por favor, responda todas as perguntas antes de enviar.');
                    return false;
                }
                
                // Mostrar loading
                const submitBtn = document.querySelector('.btn-submit');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>
