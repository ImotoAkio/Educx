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

// Adicionar um novo quiz com perguntas e alternativas
if (isset($_POST['add_quiz'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $turma_id = $_POST['turma_id'];
    $moedas_recompensa = isset($_POST['moedas_recompensa']) ? (int) $_POST['moedas_recompensa'] : 10;

    // Validar quantidade de moedas (máximo 50)
    if ($moedas_recompensa > 50) {
        redirecionarComMensagem('criar_quiz.php', 'error', 'A quantidade máxima de moedas por quiz é 50.');
    }

    if ($moedas_recompensa < 0) {
        redirecionarComMensagem('criar_quiz.php', 'error', 'A quantidade de moedas não pode ser negativa.');
    }

    try {
        // Inicia transação
        $pdo->beginTransaction();

        // Inserir o quiz
        $sql = "INSERT INTO quizzes (nome, descricao, criador_id, turma_id, moedas_recompensa, data_criacao) 
                VALUES (:nome, :descricao, :criador_id, :turma_id, :moedas_recompensa, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'criador_id' => $professor_id,
            'turma_id' => $turma_id,
            'moedas_recompensa' => $moedas_recompensa
        ]);

        $quiz_id = $pdo->lastInsertId();

        // Inserir as perguntas e alternativas
        if (!empty($_POST['pergunta'])) {
            foreach ($_POST['pergunta'] as $index => $pergunta_texto) {
                if (!empty($pergunta_texto)) {
                    // Inserir a pergunta
                    $sql_pergunta = "INSERT INTO perguntas (quiz_id, texto) VALUES (:quiz_id, :texto)";
                    $stmt_pergunta = $pdo->prepare($sql_pergunta);
                    $stmt_pergunta->execute([
                        'quiz_id' => $quiz_id,
                        'texto' => $pergunta_texto
                    ]);

                    $pergunta_id = $pdo->lastInsertId();

                    // Inserir alternativas para a pergunta
                    if (!empty($_POST['alternativa'][$index])) {
                        foreach ($_POST['alternativa'][$index] as $key => $alternativa_texto) {
                            if (!empty($alternativa_texto)) {
                                $is_correct = (isset($_POST['alternativa_correta'][$index]) && $_POST['alternativa_correta'][$index] == $key) ? 1 : 0;

                                // Inserir alternativa
                                $sql_alternativa = "INSERT INTO alternativas (pergunta_id, texto, correta) 
                                                    VALUES (:pergunta_id, :texto, :correta)";
                                $stmt_alternativa = $pdo->prepare($sql_alternativa);
                                $stmt_alternativa->execute([
                                    'pergunta_id' => $pergunta_id,
                                    'texto' => $alternativa_texto,
                                    'correta' => $is_correct
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Confirma a transação
        $pdo->commit();
        
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'success', "Quiz '$nome' criado com sucesso!");
        
    } catch (Exception $e) {
        // Reverte a transação em caso de erro
        $pdo->rollBack();
        redirecionarComMensagem('criar_quiz.php', 'error', 'Erro ao criar quiz: ' . $e->getMessage());
    }
}

// Recuperar turmas do professor
$sql = "SELECT t.id, t.nome FROM turmas_professores tp JOIN turmas t ON tp.turma_id = t.id WHERE tp.professor_id = :professor_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professor_id' => $professor_id]);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    <i class="fa fa-question-circle"></i> Criar Quiz - Painel do Professor
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <style>
    .navbar-toggler-bar {
      display: none;
    }
    
    .main-panel.d-md-none .navbar-nav .nav-link {
      color: #000 !important;
    }

    .main-panel.d-md-none .navbar-nav .nav-link:hover {
      color: #007bff !important;
    }
    
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
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .btn-remove:hover {
      background: #c82333;
      transform: scale(1.1);
    }
    
    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .progress-bar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
  </style>
</head>
      <script>
        let perguntaCount = 0;
        let alternativaCount = {};

        // Função para adicionar perguntas dinamicamente
        function adicionarPergunta() {
            perguntaCount++;
            alternativaCount[perguntaCount] = 2; // Começa com 2 alternativas
            
            let container = document.getElementById("perguntas-container");
            
            let perguntaDiv = document.createElement("div");
            perguntaDiv.className = "pergunta-card";
            perguntaDiv.id = `pergunta-${perguntaCount}`;
            
            perguntaDiv.innerHTML = `
                <div class="pergunta-header">
                    <h6 class="mb-0">
                        <i class="fa fa-question-circle"></i> Pergunta ${perguntaCount}
                    </h6>
                    <button type="button" class="btn-remove" onclick="removerPergunta(${perguntaCount})" title="Remover pergunta">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="pergunta-${perguntaCount}-texto" class="form-label">
                            <i class="fa fa-edit"></i> Texto da Pergunta <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="pergunta-${perguntaCount}-texto" 
                                  name="pergunta[${perguntaCount}]" rows="3" required
                                  placeholder="Digite a pergunta aqui..."></textarea>
                    </div>
                    
                    <div class="alternativas-container" id="alternativas-${perguntaCount}">
                        <h6 class="mt-3 mb-2">
                            <i class="fa fa-list"></i> Alternativas
                        </h6>
                        <div class="alternativa-item">
                            <div class="row">
                                <div class="col-md-9">
                                    <input class="form-control" type="text" 
                                           name="alternativa[${perguntaCount}][]" 
                                           placeholder="Alternativa A" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="alternativa_correta[${perguntaCount}]" 
                                               value="0" required>
                                        <label class="form-check-label">
                                            <i class="fa fa-check text-success"></i> Correta
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alternativa-item">
                            <div class="row">
                                <div class="col-md-9">
                                    <input class="form-control" type="text" 
                                           name="alternativa[${perguntaCount}][]" 
                                           placeholder="Alternativa B" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="alternativa_correta[${perguntaCount}]" 
                                               value="1" required>
                                        <label class="form-check-label">
                                            <i class="fa fa-check text-success"></i> Correta
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-info btn-sm" 
                                onclick="adicionarAlternativa(${perguntaCount})">
                            <i class="fa fa-plus"></i> Adicionar Alternativa
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(perguntaDiv);
            atualizarProgresso();
        }

        // Função para adicionar alternativas dinamicamente
        function adicionarAlternativa(perguntaIndex) {
            alternativaCount[perguntaIndex]++;
            let alternativasContainer = document.getElementById(`alternativas-${perguntaIndex}`);
            
            let alternativaDiv = document.createElement("div");
            alternativaDiv.className = "alternativa-item";
            
            let alternativaLabel = String.fromCharCode(65 + alternativaCount[perguntaIndex] - 1); // A, B, C, D...
            
            alternativaDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-9">
                        <input class="form-control" type="text" 
                               name="alternativa[${perguntaIndex}][]" 
                               placeholder="Alternativa ${alternativaLabel}" required>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="alternativa_correta[${perguntaIndex}]" 
                                   value="${alternativaCount[perguntaIndex] - 1}" required>
                            <label class="form-check-label">
                                <i class="fa fa-check text-success"></i> Correta
                            </label>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-2">
                    <button type="button" class="btn btn-danger btn-sm" 
                            onclick="removerAlternativa(this)">
                        <i class="fa fa-trash"></i> Remover
                    </button>
                </div>
            `;
            
            alternativasContainer.appendChild(alternativaDiv);
        }

        // Função para remover pergunta
        function removerPergunta(perguntaIndex) {
            if (confirm('Tem certeza que deseja remover esta pergunta?')) {
                document.getElementById(`pergunta-${perguntaIndex}`).remove();
                atualizarProgresso();
            }
        }

        // Função para remover alternativa
        function removerAlternativa(button) {
            if (confirm('Tem certeza que deseja remover esta alternativa?')) {
                button.closest('.alternativa-item').remove();
            }
        }

        // Função para atualizar progresso
        function atualizarProgresso() {
            let perguntas = document.querySelectorAll('.pergunta-card');
            let progress = (perguntas.length / 10) * 100; // Considera 10 perguntas como 100%
            
            document.getElementById('progress-bar').style.width = Math.min(progress, 100) + '%';
            document.getElementById('progress-text').textContent = `${perguntas.length} pergunta(s) criada(s)`;
        }

        // Função para validar formulário
        function validarFormulario() {
            let nome = document.getElementById('nome').value.trim();
            let descricao = document.getElementById('descricao').value.trim();
            let turma = document.getElementById('turma_id').value;
            let moedas = document.getElementById('moedas_recompensa').value;
            let perguntas = document.querySelectorAll('.pergunta-card');
            
            if (!nome) {
                showToast('error', 'Erro', 'Por favor, insira o nome do quiz.');
                return false;
            }
            
            if (!descricao) {
                showToast('error', 'Erro', 'Por favor, insira a descrição do quiz.');
                return false;
            }
            
            if (!turma) {
                showToast('error', 'Erro', 'Por favor, selecione uma turma.');
                return false;
            }
            
            if (!moedas || moedas < 0 || moedas > 50) {
                showToast('error', 'Erro', 'Por favor, insira uma quantidade válida de moedas (0-50).');
                return false;
            }
            
            if (perguntas.length === 0) {
                showToast('error', 'Erro', 'Adicione pelo menos uma pergunta ao quiz.');
                return false;
            }
            
            // Validar se cada pergunta tem pelo menos uma alternativa correta
            for (let i = 0; i < perguntas.length; i++) {
                let radios = perguntas[i].querySelectorAll('input[type="radio"]');
                let checked = false;
                
                for (let radio of radios) {
                    if (radio.checked) {
                        checked = true;
                        break;
                    }
                }
                
                if (!checked) {
                    showToast('error', 'Erro', `Selecione a alternativa correta para a pergunta ${i + 1}.`);
                    return false;
                }
            }
            
            return true;
        }

        // Função para mostrar toast notifications
        function showToast(type, title, message) {
            var icon = '';
            var bgClass = '';
            
            switch(type) {
                case 'success':
                    icon = 'fa-check-circle';
                    bgClass = 'bg-success';
                    break;
                case 'error':
                    icon = 'fa-exclamation-circle';
                    bgClass = 'bg-danger';
                    break;
                case 'warning':
                    icon = 'fa-exclamation-triangle';
                    bgClass = 'bg-warning';
                    break;
                case 'info':
                    icon = 'fa-info-circle';
                    bgClass = 'bg-info';
                    break;
            }
            
            var toast = `
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
                    <div class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class="fa ${icon} me-2"></i>
                            <strong class="me-auto">${title}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(toast);
            $('.toast').toast('show');
            
            setTimeout(function() {
                $('.toast').remove();
            }, 3000);
        }

        // Event listeners quando o documento carrega
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar primeira pergunta automaticamente
            adicionarPergunta();
            
            // Validação do formulário
            document.getElementById('quiz-form').addEventListener('submit', function(e) {
                if (!validarFormulario()) {
                    e.preventDefault();
                }
            });
            
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Função para limpar formulário
        function limparFormulario() {
            if (confirm('Tem certeza que deseja limpar todo o formulário? Esta ação não pode ser desfeita.')) {
                document.getElementById('quiz-form').reset();
                document.getElementById('perguntas-container').innerHTML = '';
                perguntaCount = 0;
                alternativaCount = {};
                adicionarPergunta(); // Adiciona primeira pergunta novamente
                showToast('info', 'Informação', 'Formulário limpo com sucesso.');
            }
        }
    </script>
    <style>
    </style>
</head>
<?php
include 'include/navbar.php';
?>
      <div class="content">
        <!-- Exibir mensagens de feedback -->
        <?php exibirMensagemSessao(); ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">
                  <i class="fa fa-question-circle"></i> Criar Novo Quiz
                </h5>
              </div>
              <div class="card-body">
                <!-- Barra de Progresso -->
                <div class="progress mb-4" style="height: 25px;">
                  <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 0%;" 
                       aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span id="progress-text">0 pergunta(s) criada(s)</span>
                  </div>
                </div>
                
                <form method="POST" id="quiz-form">
                  <!-- Informações Básicas do Quiz -->
                  <div class="row mb-4">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="nome" class="form-label">
                          <i class="fa fa-tag"></i> Nome do Quiz <span class="text-danger">*</span>
                        </label>
                        <input class="form-control" type="text" id="nome" name="nome" required
                               placeholder="Ex: Quiz de Matemática - Capítulo 1">
                        <small class="form-text text-muted">
                          <i class="fa fa-info-circle"></i> Escolha um nome descritivo para o quiz
                        </small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="turma_id" class="form-label">
                          <i class="fa fa-users"></i> Turma <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="turma_id" name="turma_id" required>
                          <option value="">Selecione uma turma</option>
                          <?php foreach ($turmas as $turma): ?>
                            <option value="<?= $turma['id']; ?>"><?= htmlspecialchars($turma['nome']); ?></option>
                          <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                          <i class="fa fa-info-circle"></i> Selecione a turma que fará o quiz
                        </small>
                      </div>
                    </div>
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="moedas_recompensa" class="form-label">
                          <i class="fa fa-coins"></i> Moedas de Recompensa <span class="text-danger">*</span>
                        </label>
                        <input class="form-control" type="number" id="moedas_recompensa" name="moedas_recompensa" 
                               min="0" max="50" value="10" required
                               placeholder="0-50 moedas">
                        <small class="form-text text-muted">
                          <i class="fa fa-info-circle"></i> Quantidade de moedas que o aluno receberá (máx. 50)
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group mb-4">
                    <label for="descricao" class="form-label">
                      <i class="fa fa-align-left"></i> Descrição do Quiz <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required
                              placeholder="Descreva o conteúdo e objetivos do quiz..."></textarea>
                    <small class="form-text text-muted">
                      <i class="fa fa-info-circle"></i> Forneça uma descrição clara sobre o que será avaliado
                    </small>
                  </div>
                  
                  <!-- Seção de Perguntas -->
                  <div class="perguntas-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">
                        <i class="fa fa-list"></i> Perguntas do Quiz
                      </h6>
                      <button type="button" class="btn btn-success btn-sm" onclick="adicionarPergunta()">
                        <i class="fa fa-plus"></i> Adicionar Pergunta
                      </button>
                    </div>
                    
                    <div id="perguntas-container">
                      <!-- As perguntas serão adicionadas dinamicamente aqui -->
                    </div>
                    
                    <div class="text-center mt-4" id="empty-state" style="display: none;">
                      <i class="fa fa-question-circle fa-3x text-muted mb-3"></i>
                      <h6 class="text-muted">Nenhuma pergunta criada</h6>
                      <p class="text-muted">Clique em "Adicionar Pergunta" para começar</p>
                    </div>
                  </div>
                  
                  <!-- Botões de Ação -->
                  <div class="text-center mt-5">
                    <button type="submit" class="btn btn-primary btn-lg" name="add_quiz">
                      <i class="fa fa-save"></i> Criar Quiz
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="limparFormulario()">
                      <i class="fa fa-eraser"></i> Limpar
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg ml-2">
                      <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
     
    </div>
  </div>
  <?php include 'include/footer.php'; ?>
</body>

</html>