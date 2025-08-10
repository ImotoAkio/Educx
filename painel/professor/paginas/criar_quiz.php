<?php
session_start();
require '../../../db.php';

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header('Location: ../../login.php');
    exit;
}

$professor_id = $_SESSION['professor_id'];

// Adicionar um novo quiz com perguntas e alternativas
if (isset($_POST['add_quiz'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $turma_id = $_POST['turma_id'];

    // Inserir o quiz
    $sql = "INSERT INTO quizzes (nome, descricao, criador_id, turma_id, data_criacao) 
            VALUES (:nome, :descricao, :criador_id, :turma_id, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'descricao' => $descricao,
        'criador_id' => $professor_id,
        'turma_id' => $turma_id
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

    // Redireciona para editar o quiz
    header("Location: editar_quiz.php?id=$quiz_id");
    exit;
}

// Recuperar turmas do professor
$sql = "SELECT t.id, t.nome FROM turmas_professores tp JOIN turmas t ON tp.turma_id = t.id WHERE tp.professor_id = :professor_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professor_id' => $professor_id]);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Criar Quiz
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link href="../assets/demo/demo.css" rel="stylesheet" />
  <script>
        // Função para adicionar perguntas e alternativas dinamicamente
        function adicionarPergunta() {
            let container = document.getElementById("perguntas-container");
            let perguntaIndex = container.children.length;

            // Criando uma nova pergunta
            let perguntaDiv = document.createElement("div");
            perguntaDiv.classList.add("form-group", "pergunta");

            // Campo para a pergunta
            perguntaDiv.innerHTML = `
                <label class="form-label" for="pergunta[${perguntaIndex}]">Pergunta:</label>
                <input class="form-control" type="text" id="pergunta[${perguntaIndex}]" name="pergunta[${perguntaIndex}]" required>
                <br>
                <label class="form-label">Alternativas:</label>
                <div id="alternativas[${perguntaIndex}]">
                    <div class="form-group d-flex align-items-center">
                        <div class="col-md-8">
                            <input class="form-control me-2" type="text" name="alternativa[${perguntaIndex}][]" required>
                        </div>
                        <div class="col-md-3">
                        <input class="form-check-input" type="radio" name="alternativa_correta[${perguntaIndex}]" value="0">
                        <label class="form-check-label ms-2">Correta?</label>
                        </div>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <div class="col-md-8">
                            <input class="form-control me-2" type="text" name="alternativa[${perguntaIndex}][]" required>
                        </div>
                        <div class="col-md-3">
                        <input class="form-check-input" type="radio" name="alternativa_correta[${perguntaIndex}]" value="0">
                        <label class="form-check-label ms-2">Correta?</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mt-2" onclick="adicionarAlternativa(${perguntaIndex})">Adicionar Alternativa</button>
                <br><br>
            `;

            container.appendChild(perguntaDiv);
        }

        // Função para adicionar alternativas dinamicamente
        function adicionarAlternativa(perguntaIndex) {
            let alternativasDiv = document.getElementById(`alternativas[${perguntaIndex}]`);
            let novaAlternativa = document.createElement("div");

            novaAlternativa.innerHTML = `
                    <div class="form-group d-flex align-items-center">
                        <div class="col-md-8">
                            <input class="form-control me-2" type="text" name="alternativa[${perguntaIndex}][]" required>
                        </div>
                        <div class="col-md-3">
                        <input class="form-check-input" type="radio" name="alternativa_correta[${perguntaIndex}]" value="0">
                        <label class="form-check-label ms-2">Correta?</label>
                        </div>
                    </div>
            `;
            alternativasDiv.appendChild(novaAlternativa);
        }
    </script>
    <style>
    </style>
</head>
<?php
include 'include/navbar.php';
?>
      <div class="content">
        <div class="row">

          <div class="col-md-12">
            <div class="card card-user">
              <div class="card-header">
                <h5 class="card-title">Criar Quiz</h5>
              </div>
              <div class="card-body">
              <form method="POST">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                      <label for="nome">Nome do Quiz:</label>
                        <input class="form-control" type="text" id="nome" name="nome" required>
                      </div>
                    </div>

                  </div>
                  <div class="row">
                    <div class="col-md-6 pr-1">
                      <div class="form-group">
                      <label for="descricao">Descrição:</label>
                        <textarea class="form-control" id="descricao" name="descricao" required></textarea>

                      </div>
                    </div>
                    <div class="col-md-6 ">
                      <div class="form-group">
                        <label for="turma_id">Turma:</label>
                        <select class="form-control" id="turma_id" name="turma_id" required>
                          <option value="" disabled selected>Selecione uma turma</option>
                          <?php foreach ($turmas as $turma): ?>
                            <option value="<?= $turma['id']; ?>"><?= htmlspecialchars($turma['nome']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <div id="perguntas-container">
                          <!-- As perguntas serão adicionadas dinamicamente aqui -->
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="adicionarPergunta()">Adicionar Pergunta</button>
                        <br><br>
                        <button type="submit" class="btn btn-primary" name="add_quiz">Criar Quiz</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
     
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!--  Google Maps Plugin    -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
  <!-- Chart JS -->
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <!--  Notifications Plugin    -->
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>
  <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script><!-- Paper Dashboard DEMO methods, don't include it in your project! -->
  <script src="../assets/demo/demo.js"></script>
</body>

</html>