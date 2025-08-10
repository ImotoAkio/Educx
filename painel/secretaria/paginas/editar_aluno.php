<?php
// Conexão com o banco de dados
session_start();
require '../../../db.php'; // Conexão com o banco

// Variável para a URL base do QR Code
$base_url = 'http:game.echotec.online/aluno.php?id=';

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    header("Location: ../../../login.php");
    exit;
}

// Adicionar aluno
if (isset($_POST['add'])) {
  $nome = $_POST['nome'];
  $moedas = $_POST['moedas'];

  // Insere o aluno sem o QR Code Link inicialmente
  $sql = "INSERT INTO alunos (nome, moedas) VALUES (:nome, :moedas)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
      'nome' => $nome,
      'moedas' => $moedas
  ]);

  // Obtém o ID do aluno recém-inserido
  $id = $pdo->lastInsertId();

  // Gera o QR Code Link com base no ID e atualiza o registro
  $qr_code_link = $base_url . $id;

  $sql = "UPDATE alunos SET qr_code_link = :qr_code_link WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
      'qr_code_link' => $qr_code_link,
      'id' => $id
  ]);
}


// Editar aluno
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $nome = $_POST['nome'];
  $moedas = $_POST['moedas'];

  // Gera o QR Code Link novamente
  $qr_code_link = $base_url . $id;

  $sql = "UPDATE alunos SET nome = :nome, moedas = :moedas, qr_code_link = :qr_code_link WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
      'id' => $id,
      'nome' => $nome,
      'moedas' => $moedas,
      'qr_code_link' => $qr_code_link
  ]);
}


// Função para remover um aluno
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "DELETE FROM alunos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
}

// Consulta todos os alunos
$sql = "SELECT * FROM alunos";
$stmt = $pdo->query($sql);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    color: #000 !important; /* Define a cor do texto como preta */
  }

  .main-panel.d-md-none .navbar-nav .nav-link:hover {
    color: #007bff !important; /* Cor de hover azul */
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
          <li>
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
          <li class="active">
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
          <li>
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
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">Dashboard <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="tables.php">Aprovar Compras</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_professor.php">Editar Professores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_secretaria.php">Editar Secretários</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="editar_aluno.php">Editar Alunos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_loja.php">Editar Loja</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="missoes.php">Aprovar Missões</a>
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
    <!-- Conteúdo principal da página -->

      <div class="content">
        <div class="row">
          
          </div>
          <div class="col-md-16">
    <div class="card card-user">
        <div class="card-header">
            <h5 class="card-title">Adicionar Aluno</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do Aluno</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="mb-3">
                    <label for="moedas" class="form-label">Moedas</label>
                    <input type="number" step="1" class="form-control" id="moedas" name="moedas">
                </div>
                <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
            </form>
        </div>
    </div>
</div>

<!-- Lista de alunos -->
<!-- Lista de alunos -->
<div class="card">
    <div class="card-header">Alunos</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Moedas</th>

                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><?= $aluno['id'] ?></td>
                        <td><?= $aluno['nome'] ?></td>
                        <td><?= $aluno['moedas'] ?></td>

                        <td>
                            <!-- Formulário para editar aluno -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?= $aluno['id'] ?>">
                                <input type="text" name="nome" value="<?= $aluno['nome'] ?>" class="form-control mb-2" required>
                                <input type="number" step="1" name="moedas" value="<?= $aluno['moedas'] ?>" class="form-control mb-2" required>
                                
                                <button type="submit" name="edit" class="btn btn-success">Editar</button>
                            </form>

                            <!-- Botão para excluir aluno -->
                            <a href="?delete=<?= $aluno['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este aluno?');">Excluir</a>

                            <!-- Botão para mostrar QR Code -->
                            <a href="visualizar_qr.php?url=<?= urlencode($aluno['qr_code_link']); ?>&nome=<?= urlencode($aluno['nome']); ?>" 
                                target="_blank" class="btn btn-primary">Ver QR Code</a>


                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


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