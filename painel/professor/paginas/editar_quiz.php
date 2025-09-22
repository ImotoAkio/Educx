<?php
session_start();
require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_quiz';

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header('Location: ../../../login.php');
    exit;
}

$professor_id = $_SESSION['usuario_id'];
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quiz_id <= 0) {
    redirecionarComMensagem('criar_quiz.php', 'error', 'ID do quiz inválido.');
}

// Verificar se o quiz pertence ao professor
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :quiz_id AND criador_id = :professor_id");
$stmt->execute(['quiz_id' => $quiz_id, 'professor_id' => $professor_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    redirecionarComMensagem('criar_quiz.php', 'error', 'Quiz não encontrado ou você não tem permissão para editá-lo.');
}

// Adicionar uma pergunta
if (isset($_POST['add_pergunta'])) {
    $texto = trim($_POST['texto'] ?? '');
    
    if (empty($texto)) {
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'O texto da pergunta não pode estar vazio.');
    }

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
    $pergunta_id = (int)($_POST['pergunta_id'] ?? 0);
    $texto = trim($_POST['texto'] ?? '');
    $correta = isset($_POST['correta']) ? 1 : 0;

    if ($pergunta_id <= 0 || empty($texto)) {
        redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'Dados da alternativa inválidos.');
    }

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

// Deletar pergunta
if (isset($_POST['delete_pergunta'])) {
    $pergunta_id = (int)($_POST['pergunta_id'] ?? 0);
    
    if ($pergunta_id > 0) {
        try {
            // Deletar alternativas primeiro
            $stmt = $pdo->prepare("DELETE FROM alternativas WHERE pergunta_id = :pergunta_id");
            $stmt->execute(['pergunta_id' => $pergunta_id]);
            
            // Deletar pergunta
            $stmt = $pdo->prepare("DELETE FROM perguntas WHERE id = :pergunta_id AND quiz_id = :quiz_id");
            $stmt->execute(['pergunta_id' => $pergunta_id, 'quiz_id' => $quiz_id]);
            
            redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'success', 'Pergunta deletada com sucesso!');
        } catch (Exception $e) {
            redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'Erro ao deletar pergunta: ' . $e->getMessage());
        }
    }
}

// Deletar alternativa
if (isset($_POST['delete_alternativa'])) {
    $alternativa_id = (int)($_POST['alternativa_id'] ?? 0);
    
    if ($alternativa_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM alternativas WHERE id = :alternativa_id");
            $stmt->execute(['alternativa_id' => $alternativa_id]);
            
            redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'success', 'Alternativa deletada com sucesso!');
        } catch (Exception $e) {
            redirecionarComMensagem("editar_quiz.php?id=$quiz_id", 'error', 'Erro ao deletar alternativa: ' . $e->getMessage());
        }
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
    <title>Editar Quiz - Painel do Professor</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    
    <!-- CSS Files -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <link href="../assets/demo/demo.css" rel="stylesheet" />
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Mobile Header CSS -->
    <link rel="stylesheet" href="../assets/css/mobile-header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="">
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" data-color="white" data-active-color="danger">
            <div class="logo">
                <a href="dashboard.php" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <i class="nc-icon nc-bank"></i>
                    </div>
                </a>
                <a href="dashboard.php" class="simple-text logo-normal">
                    Painel Professor
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="<?= $pagina_ativa === 'dashboard' ? 'active' : '' ?>">
                        <a href="./dashboard.php">
                            <i class="nc-icon nc-bank"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="<?= $pagina_ativa === 'missoes' ? 'active' : '' ?>">
                        <a href="./missoes.php">
                            <i class="nc-icon nc-diamond"></i>
                            <p>Missões</p>
                        </a>
                    </li>
                    <li class="<?= $pagina_ativa === 'criar_quiz' ? 'active' : '' ?>">
                        <a href="./criar_quiz.php">
                            <i class="nc-icon nc-paper"></i>
                            <p>Criar Quiz</p>
                        </a>
                    </li>
                    <li class="<?= $pagina_ativa === 'editar_quiz' ? 'active' : '' ?>">
                        <a href="./editar_quiz.php">
                            <i class="nc-icon nc-single-copy-04"></i>
                            <p>Editar Quiz</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Mobile Header -->
        <?php include 'include/mobile-header.php'; ?>

        <!-- Main Panel -->
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="javascript:;">Editar Quiz</a>
                    </div>
                </div>
            </nav>

            <!-- End Navbar -->
            <div class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fa fa-edit"></i> Editar Quiz: <?= htmlspecialchars($quiz['nome']) ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <!-- Exibir mensagens de feedback -->
                                <?php
                                if (isset($_SESSION['mensagem_sucesso'])) {
                                    exibirSucesso($_SESSION['mensagem_sucesso']);
                                    unset($_SESSION['mensagem_sucesso']);
                                }
                                if (isset($_SESSION['mensagem_erro'])) {
                                    exibirErro($_SESSION['mensagem_erro']);
                                    unset($_SESSION['mensagem_erro']);
                                }
                                ?>

                                <!-- Formulário para adicionar pergunta -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5><i class="fa fa-plus"></i> Adicionar Nova Pergunta</h5>
                                        <form method="POST" class="form-inline">
                                            <div class="form-group mr-3 flex-grow-1">
                                                <input type="text" name="texto" class="form-control" placeholder="Digite a pergunta..." required style="width: 100%;">
                                            </div>
                                            <button type="submit" name="add_pergunta" class="btn btn-success">
                                                <i class="fa fa-plus"></i> Adicionar Pergunta
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Lista de perguntas existentes -->
                                <div class="row">
                                    <div class="col-12">
                                        <h5><i class="fa fa-list"></i> Perguntas do Quiz</h5>
                                        
                                        <?php if (empty($perguntas)): ?>
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i> Nenhuma pergunta cadastrada ainda. Adicione a primeira pergunta acima.
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($perguntas as $pergunta): ?>
                                                <div class="card mb-3">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">
                                                            <i class="fa fa-question-circle"></i> 
                                                            <?= htmlspecialchars($pergunta['texto']) ?>
                                                        </h6>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja deletar esta pergunta?')">
                                                            <input type="hidden" name="pergunta_id" value="<?= $pergunta['id'] ?>">
                                                            <button type="submit" name="delete_pergunta" class="btn btn-sm btn-danger">
                                                                <i class="fa fa-trash"></i> Deletar
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="card-body">
                                                        <!-- Formulário para adicionar alternativa -->
                                                        <form method="POST" class="form-inline mb-3">
                                                            <input type="hidden" name="pergunta_id" value="<?= $pergunta['id'] ?>">
                                                            <div class="form-group mr-2 flex-grow-1">
                                                                <input type="text" name="texto" class="form-control" placeholder="Digite a alternativa..." required>
                                                            </div>
                                                            <div class="form-group mr-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" name="correta" class="form-check-input" id="correta_<?= $pergunta['id'] ?>">
                                                                    <label class="form-check-label" for="correta_<?= $pergunta['id'] ?>">
                                                                        Correta
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <button type="submit" name="add_alternativa" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-plus"></i> Adicionar
                                                            </button>
                                                        </form>

                                                        <!-- Lista de alternativas -->
                                                        <h6>Alternativas:</h6>
                                                        <?php if (empty($pergunta['alternativas'])): ?>
                                                            <p class="text-muted">Nenhuma alternativa cadastrada ainda.</p>
                                                        <?php else: ?>
                                                            <div class="list-group">
                                                                <?php foreach ($pergunta['alternativas'] as $alternativa): ?>
                                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <span>
                                                                            <?= htmlspecialchars($alternativa['texto']) ?>
                                                                            <?php if ($alternativa['correta']): ?>
                                                                                <span class="badge badge-success ml-2">Correta</span>
                                                                            <?php endif; ?>
                                                                        </span>
                                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja deletar esta alternativa?')">
                                                                            <input type="hidden" name="alternativa_id" value="<?= $alternativa['id'] ?>">
                                                                            <button type="submit" name="delete_alternativa" class="btn btn-sm btn-outline-danger">
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
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
    </div>

    <!-- Scripts -->
    <script src="../assets/js/core/jquery.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
    <script src="../assets/js/plugins/chartjs.min.js"></script>
    <script src="../assets/js/plugins/bootstrap-notify.js"></script>
    <script src="../assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>