<?php
session_start();
require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'criar_quiz';

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header('Location: ../../../login.php');
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$quiz_id = $_GET['id'];

// Verificar se o quiz pertence ao professor
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :quiz_id AND criador_id = :professor_id");
$stmt->execute(['quiz_id' => $quiz_id, 'professor_id' => $professor_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    redirecionarComMensagem('criar_quiz.php', 'error', 'Quiz não encontrado ou você não tem permissão para editá-lo.');
}

// Adicionar uma pergunta
if (isset($_POST['add_pergunta'])) {
    $texto = $_POST['texto'];

    try {
        $sql = "INSERT INTO perguntas (quiz_id, texto) VALUES (:quiz_id, :texto)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'quiz_id' => $quiz_id,
            'texto' => $texto
        ]);
        
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'success', 'Pergunta adicionada com sucesso!');
    } catch (Exception $e) {
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'Erro ao adicionar pergunta: ' . $e->getMessage());
    }
}

// Adicionar uma alternativa
if (isset($_POST['add_alternativa'])) {
    $pergunta_id = $_POST['pergunta_id'];
    $texto = $_POST['texto'];
    $correta = isset($_POST['correta']) ? 1 : 0;

    try {
        $sql = "INSERT INTO alternativas (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'pergunta_id' => $pergunta_id,
            'texto' => $texto,
            'correta' => $correta
        ]);
        
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'success', 'Alternativa adicionada com sucesso!');
    } catch (Exception $e) {
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'Erro ao adicionar alternativa: ' . $e->getMessage());
    }
}

// Recuperar perguntas e alternativas
$sql = "SELECT p.*, COUNT(a.id) as total_alternativas 
        FROM perguntas p 
        LEFT JOIN alternativas a ON p.id = a.pergunta_id 
        WHERE p.quiz_id = :quiz_id 
        GROUP BY p.id 
        ORDER BY p.id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['quiz_id' => $quiz_id]);
$perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recuperar alternativas para cada pergunta
foreach ($perguntas as &$pergunta) {
    $sql_alt = "SELECT * FROM alternativas WHERE pergunta_id = :pergunta_id ORDER BY id";
    $stmt_alt = $pdo->prepare($sql_alt);
    $stmt_alt->execute(['pergunta_id' => $pergunta['id']]);
    $pergunta['alternativas'] = $stmt_alt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    <i class="fa fa-edit"></i> Editar Quiz - Painel do Professor
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <style>
    .pergunta-card {
      border: 2px solid #e9ecef;
      border-radius: 10px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .pergunta-card:hover {
      border-color: #007bff;
      box-shadow: 0 4px 8px rgba(0,123,255,0.1);
    }
    
    .pergunta-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px;
      border-radius: 8px 8px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .alternativa-item {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }
    
    .alternativa-item:hover {
      background: #e9ecef;
      border-color: #007bff;
    }
    
    .alternativa-correta {
      background: #d4edda !important;
      border-color: #28a745 !important;
    }
    
    .btn-remove {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-remove:hover {
      background: #c82333;
      transform: scale(1.1);
    }
    
    .progress {
      height: 25px;
      border-radius: 15px;
    }
    
    .progress-bar {
      border-radius: 15px;
      font-weight: bold;
    }
  </style>
</head>

<body class="">
<?php include 'include/navbar.php'; ?>
  <div class="main-panel">
    <div class="content">
      <!-- Exibir mensagens de feedback -->
      <?php exibirMensagemSessao(); ?>
      
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="fa fa-edit"></i> Editar Quiz: <?= htmlspecialchars($quiz['nome']); ?>
              </h5>
            </div>
            <div class="card-body">
              <!-- Barra de Progresso -->
              <div class="progress mb-4" style="height: 25px;">
                <div class="progress-bar" id="progress-bar" role="progressbar" 
                     style="width: <?= count($perguntas) > 0 ? (count($perguntas) * 10) : 0; ?>%;" 
                     aria-valuenow="<?= count($perguntas); ?>" aria-valuemin="0" aria-valuemax="10">
                  <span id="progress-text"><?= count($perguntas); ?> pergunta(s) criada(s)</span>
                </div>
              </div>
              
              <!-- Informações do Quiz -->
              <div class="alert alert-info">
                <h6><i class="fa fa-info-circle"></i> Informações do Quiz</h6>
                <p><strong>Nome:</strong> <?= htmlspecialchars($quiz['nome']); ?></p>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($quiz['descricao']); ?></p>
                <p><strong>Data de Criação:</strong> <?= date('d/m/Y H:i', strtotime($quiz['data_criacao'])); ?></p>
              </div>
              
              <!-- Adicionar Nova Pergunta -->
              <div class="card mb-4">
                <div class="card-header">
                  <h6><i class="fa fa-plus"></i> Adicionar Nova Pergunta</h6>
                </div>
                <div class="card-body">
                  <form method="POST" class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="texto">Texto da Pergunta:</label>
                        <input type="text" class="form-control" id="texto" name="texto" 
                               placeholder="Digite a pergunta aqui..." required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="add_pergunta" class="btn btn-primary btn-block">
                          <i class="fa fa-plus"></i> Adicionar Pergunta
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              
              <!-- Lista de Perguntas -->
              <div class="card">
                <div class="card-header">
                  <h6><i class="fa fa-list"></i> Perguntas do Quiz</h6>
                </div>
                <div class="card-body">
                  <?php if (empty($perguntas)): ?>
                    <div class="text-center py-4">
                      <i class="fa fa-question-circle fa-3x text-muted mb-3"></i>
                      <h5 class="text-muted">Nenhuma pergunta criada ainda</h5>
                      <p class="text-muted">Adicione a primeira pergunta usando o formulário acima.</p>
                    </div>
                  <?php else: ?>
                    <?php foreach ($perguntas as $index => $pergunta): ?>
                      <div class="pergunta-card">
                        <div class="pergunta-header">
                          <h6 class="mb-0">
                            <i class="fa fa-question-circle"></i> 
                            Pergunta <?= $index + 1; ?>
                          </h6>
                          <span class="badge badge-light">
                            <?= count($pergunta['alternativas']); ?> alternativa(s)
                          </span>
                        </div>
                        <div class="card-body">
                          <p class="mb-3"><strong><?= htmlspecialchars($pergunta['texto']); ?></strong></p>
                          
                          <!-- Lista de Alternativas -->
                          <?php if (!empty($pergunta['alternativas'])): ?>
                            <div class="mb-3">
                              <h6><i class="fa fa-list-ul"></i> Alternativas:</h6>
                              <?php foreach ($pergunta['alternativas'] as $alt): ?>
                                <div class="alternativa-item <?= $alt['correta'] ? 'alternativa-correta' : ''; ?>">
                                  <div class="d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($alt['texto']); ?></span>
                                    <?php if ($alt['correta']): ?>
                                      <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Correta
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          <?php endif; ?>
                          
                          <!-- Adicionar Alternativa -->
                          <form method="POST" class="row">
                            <input type="hidden" name="pergunta_id" value="<?= $pergunta['id']; ?>">
                            <div class="col-md-6">
                              <div class="form-group">
                                <input type="text" class="form-control" name="texto" 
                                       placeholder="Digite a alternativa..." required>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                  <input type="checkbox" class="custom-control-input" id="correta_<?= $pergunta['id']; ?>" name="correta">
                                  <label class="custom-control-label" for="correta_<?= $pergunta['id']; ?>">
                                    Alternativa Correta
                                  </label>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <button type="submit" name="add_alternativa" class="btn btn-success btn-block">
                                  <i class="fa fa-plus"></i> Adicionar
                                </button>
                              </div>
                            </div>
                          </form>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
              
              <!-- Botões de Ação -->
              <div class="text-center mt-4">
                <a href="criar_quiz.php" class="btn btn-secondary">
                  <i class="fa fa-arrow-left"></i> Voltar para Criar Quiz
                </a>
                <a href="dashboard.php" class="btn btn-primary">
                  <i class="fa fa-home"></i> Ir para Dashboard
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>