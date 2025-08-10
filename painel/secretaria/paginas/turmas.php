<?php
// Conexão com o banco de dados
session_start();
require '../../../db.php'; // Conexão com o banco

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
  header("Location: ../../../login.php");
  exit;
}

// Adicionar Turma
if (isset($_POST['add_turma'])) {
  $nome = $_POST['nome'];
  $descricao = $_POST['descricao'];
  $ano_letivo = $_POST['ano_letivo'];

  $sql = "INSERT INTO turmas (nome, descricao, ano_letivo) VALUES (:nome, :descricao, :ano_letivo)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['nome' => $nome, 'descricao' => $descricao, 'ano_letivo' => $ano_letivo]);
}


// Função para remover uma turma
if (isset($_GET['delete_turma'])) {
  $turma_id = $_GET['delete_turma'];

  // Remove associações antes de excluir a turma
  $sql = "DELETE FROM alunos_turmas WHERE turma_id = :turma_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['turma_id' => $turma_id]);

  $sql = "DELETE FROM turmas_professores WHERE turma_id = :turma_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['turma_id' => $turma_id]);

  $sql = "DELETE FROM turmas WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['id' => $turma_id]);
}

// Consulta todas as turmas
$sql = "SELECT id, nome FROM turmas";
$stmt = $pdo->query($sql);
$turmas2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta inicial para alunos (nenhuma turma selecionada)
$alunos = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];

    // Consulta os alunos vinculados à turma selecionada
    $sql = "
        SELECT a.id AS aluno_id, a.nome AS aluno_nome, a.moedas
        FROM alunos a
        JOIN alunos_turmas at ON a.id = at.aluno_id
        WHERE at.turma_id = :turma_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['turma_id' => $turma_id]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta todas as turmas com os professores associados
$sql = "
    SELECT 
        t.id AS turma_id, 
        t.nome AS turma_nome, 
        GROUP_CONCAT(p.nome SEPARATOR ', ') AS professores_associados,
        t.descricao, 
        t.ano_letivo
    FROM turmas t
    LEFT JOIN turmas_professores tp ON t.id = tp.turma_id
    LEFT JOIN professores p ON tp.professor_id = p.id
    GROUP BY t.id
";
$stmt = $pdo->query($sql);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!--
=========================================================
* Paper Dashboard 2 - v2.0.1
=========================================================

* Product Page: https://www.creative-tim.com/product/paper-dashboard-2
* Copyright 2020 Creative Tim (https://www.creative-tim.com)

Coded by www.creative-tim.com

 =========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Painel Secretaria
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
      /* Define a cor do texto como preta */
    }

    .main-panel.d-md-none .navbar-nav .nav-link:hover {
      color: #007bff !important;
      /* Cor de hover azul */
    }
  </style>

</head>

<body>
  <div class="wrapper">
    <div class="sidebar" data-color="white" data-active-color="danger">
      <div class="logo">
        <a href="dashboard.php" class="simple-text logo-mini">
          <div class="logo-image-small">
            <img src="../assets/img/logo-small.png" alt="Logo">
          </div>
        </a>
        <a href="dashboard.php" class="simple-text logo-normal">
          Painel
        </a>
      </div>
      <div class="sidebar-wrapper">
        <ul class="nav">
          <li>
            <a href="./dashboard.php">
              <i class="nc-icon nc-bank"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li >
            <a href="./editar_professor.php">
              <i class="nc-icon nc-glasses-2"></i>
              <p>Editar Professores</p>
            </a>
          </li>
          <li>
            <a href="./editar_loja.php">
              <i class="nc-icon nc-basket"></i>
              <p>Editar Loja</p>
            </a>
          </li>
          <li>
            <a href="./tables.php">
              <i class="nc-icon nc-lock-circle-open"></i>
              <p>Aprovar compra</p>
            </a>
          </li>
          <li>
            <a href="./editar_aluno.php">
              <i class="nc-icon nc-single-02"></i>
              <p>Editar Aluno</p>
            </a>
          </li>
          <li>
            <a href="./editar_secretaria.php">
              <i class="nc-icon nc-badge"></i>
              <p>Editar Secretários</p>
            </a>
          </li>
          <li>
            <a href="./missoes.php">
              <i class="nc-icon nc-user-run"></i>
              <p>Aprovar Missões</p>
            </a>
          </li>
          <li>
            <a href="./editar_missoes.php">
              <i class="nc-icon nc-controller-modern"></i>
              <p>Editar Missões</p>
            </a>
          </li>
          <li class="active">
            <a href="./turmas.php">
              <i class="nc-icon nc-controller-modern"></i>
              <p>Turmas</p>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Painel principal para dispositivos móveis -->
    <div class="main-panel d-md-none"> <!-- Visível apenas em dispositivos móveis -->
      <!-- Aqui vai o conteúdo do painel principal -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="navbar-toggler-icon" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item ">
              <a class="nav-link" href="dashboard.php">Dashboard <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="tables.php">Aprovar Compras</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="editar_professor.php">Editar Professores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_secretaria.php">Editar Secretários</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_aluno.php">Editar Alunos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_loja.php">Editar Loja</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="missoes.php">Aprovar Missões</a>
            </li>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="turmas.php">Turmas</a>
            </li>
          </ul>
        </div>
      </nav>
    </div>

    <!-- Painel principal para telas grandes -->
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
        <div class="container-fluid">
          <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
              <li class="nav-item btn-rotate dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="nc-icon nc-bell-55"></i>
                  <p>
                    <span class="d-lg-none d-md-block">Some Actions</span>
                  </p>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                  <a class="dropdown-item" href="#">Action</a>
                  <a class="dropdown-item" href="#">Another action</a>
                  <a class="dropdown-item" href="#">Something else here</a>
                </div>
              </li>
              <li class="nav-item">
                <a class="nav-link btn-rotate" href="javascript:;" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                  <i class="nc-icon nc-settings-gear-65"></i>
                  <p>
                    <span class="d-lg-none d-md-block">Account</span>
                  </p>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <div class="content">

        <div class="col-md-16">
          <div class="card card-user">
            <!-- Formulário para adicionar turma -->
            <div class="card mb-4">
              <div class="card-header">Adicionar Nova Turma</div>
              <div class="card-body">
                <form method="post">
                  <div class="mb-3">
                    <label for="nome" class="form-label">Nome da Turma:</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                  </div>
                  <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <textarea class="form-control" id="descricao" name="descricao"></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="ano_letivo" class="form-label">Ano Letivo:</label>
                    <input type="number" class="form-control" id="ano_letivo" name="ano_letivo" required>
                  </div>
                  <button type="submit" name="add_turma" class="btn btn-primary">Adicionar Turma</button>
                </form>
              </div>
            </div>

          </div>
        </div>


        <!-- Lista de alunos -->
        <div class="card">
          <div class="card-header">Turmas Cadastradas</div>
          <div class="card-body">
            <?php if (empty($turmas)): ?>
              <p>Nenhuma turma encontrada.</p>
            <?php else: ?>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Professor</th>
                    <th>Ano Letivo</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($turmas as $turma): ?>
    <tr>
        <td><?= htmlspecialchars($turma['turma_id']); ?></td>
        <td><?= htmlspecialchars($turma['turma_nome']); ?></td>
        <td><?= htmlspecialchars($turma['professores_associados'] ?? 'Sem professor associado'); ?></td>
        <td><?= htmlspecialchars($turma['ano_letivo']); ?></td>
        <td>
            <a href="?delete_turma=<?= $turma['turma_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta turma?');">Excluir</a>
        </td>
    </tr>
<?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-header">Gerenciar Alunos por Turma</div>
          <div class="card-body">

          <form method="post" id="turmaForm">
            <div class="mb-3">
                <label for="turma_id" class="form-label">Selecione uma Turma:</label>
                <select name="turma_id" id="turma_id" class="form-select" required>
                    <option value="">Selecione uma turma</option>
                    <?php foreach ($turmas2 as $turma2): ?>
                        <option value="<?= htmlspecialchars($turma2['id']); ?>">
                            <?= htmlspecialchars($turma2['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Mostrar Alunos</button>
        </form>

        <?php if (empty($alunos)): ?>
                <p>Nenhum aluno vinculado à turma selecionada.</p>
            <?php else: ?>
              <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Moedas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td><?= htmlspecialchars($aluno['aluno_id']); ?></td>
                                <td><?= htmlspecialchars($aluno['aluno_nome']); ?></td>
                                <td><?= htmlspecialchars($aluno['moedas']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
          </div>
        </div>

        <form method="post">
          <label for="turma_id">Turma:</label>
          <select name="turma_id" id="turma_id" class="form-select" required>
            <?php
            $stmt = $pdo->query("SELECT id, nome FROM turmas");
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($turmas as $turma): ?>
              <option value="<?= htmlspecialchars($turma['id']); ?>">
                <?= htmlspecialchars($turma['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="professor_id">Professor:</label>
          <select name="professor_id" id="professor_id" class="form-select" required>
            <?php
            $stmt = $pdo->query("SELECT id, nome FROM professores");
            $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($professores as $professor): ?>
              <option value="<?= htmlspecialchars($professor['id']); ?>">
                <?= htmlspecialchars($professor['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button type="submit" name="add_professor_turma" class="btn btn-success mt-3">Associar Professor à Turma</button>
        </form>

        <?php
        if (isset($_POST['add_professor_turma'])) {
          $turma_id = $_POST['turma_id'];
          $professor_id = $_POST['professor_id'];

          $sql = "INSERT INTO turmas_professores (turma_id, professor_id) VALUES (:turma_id, :professor_id)";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['turma_id' => $turma_id, 'professor_id' => $professor_id]);

          echo "<div class='alert alert-success'>Professor associado à turma com sucesso!</div>";
        }
        ?>

        <form method="post">
          <label for="turma_id">Turma:</label>
          <select name="turma_id" id="turma_id" class="form-select" required>
            <?php
            $stmt = $pdo->query("SELECT id, nome FROM turmas");
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($turmas as $turma): ?>
              <option value="<?= htmlspecialchars($turma['id']); ?>">
                <?= htmlspecialchars($turma['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="aluno_id">Aluno:</label>
          <select name="aluno_id" id="aluno_id" class="form-select" required>
            <?php
            $stmt = $pdo->query("SELECT id, nome FROM alunos");
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($alunos as $aluno): ?>
              <option value="<?= htmlspecialchars($aluno['id']); ?>">
                <?= htmlspecialchars($aluno['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button type="submit" name="add_aluno_turma" class="btn btn-success mt-3">Associar Aluno à Turma</button>
        </form>

        <?php
        if (isset($_POST['add_aluno_turma'])) {
          $turma_id = $_POST['turma_id'];
          $aluno_id = $_POST['aluno_id'];

          $sql = "INSERT INTO alunos_turmas (aluno_id, turma_id) VALUES (:aluno_id, :turma_id)";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['aluno_id' => $aluno_id, 'turma_id' => $turma_id]);

          echo "<div class='alert alert-success'>Aluno associado à turma com sucesso!</div>";
        }
        ?>
      </div> <!-- Fim conteudo -->
      


    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  </div>
  </div>
  <footer class="footer footer-black  footer-white ">
    <div class="container-fluid">
      <div class="row">
        <nav class="footer-nav">
          <ul>
            <li><a href="https://www.creative-tim.com" target="_blank">Creative Tim</a></li>
            <li><a href="https://www.creative-tim.com/blog" target="_blank">Blog</a></li>
            <li><a href="https://www.creative-tim.com/license" target="_blank">Licenses</a></li>
          </ul>
        </nav>
        <div class="credits ml-auto">
          <span class="copyright">
            © <script>
              document.write(new Date().getFullYear())
            </script>, made with <i class="fa fa-heart heart"></i> by Creative Tim
          </span>
        </div>
      </div>
    </div>
  </footer>
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