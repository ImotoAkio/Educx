<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
  // Redireciona para a página de login se o professor não estiver logado
  header('Location: ../../login.php');
  exit;
}

// Obtém o ID do professor logado
$professor_id = $_SESSION['professor_id'];



// Função para adicionar uma missão
if (isset($_POST['add'])) {
  $nome = $_POST['nome'];
  $descricao = $_POST['descricao'];
  $xp = $_POST['xp'];
  $moedas = $_POST['moedas'];
  $link = $_POST['link'];
  $criador_id = $_SESSION['professor_id'];
  $turma_id = $_POST['turma_id']; // Turma selecionada pelo professor

  $sql = "INSERT INTO missoes (nome, descricao, xp, moedas, link, criador_id, turma_id) 
          VALUES (:nome, :descricao, :xp, :moedas, :link, :criador_id, :turma_id)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
      'nome' => $nome,
      'descricao' => $descricao,
      'xp' => $xp,
      'moedas' => $moedas,
      'link' => $link,
      'criador_id' => $criador_id,
      'turma_id' => $turma_id
  ]);
}

// Função para editar uma missão
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $nome = $_POST['nome'];
  $descricao = $_POST['descricao'];
  $xp = $_POST['xp'];
  $moedas = $_POST['moedas'];
  $link = $_POST['link'];
  $turma_id = $_POST['turma_id']; // Nova turma selecionada pelo professor

  $professor_id = $_SESSION['professor_id'];

  $sql = "UPDATE missoes 
          SET nome = :nome, descricao = :descricao, xp = :xp, moedas = :moedas, link = :link, turma_id = :turma_id 
          WHERE id = :id AND criador_id = :criador_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
      'id' => $id,
      'nome' => $nome,
      'descricao' => $descricao,
      'xp' => $xp,
      'moedas' => $moedas,
      'link' => $link,
      'turma_id' => $turma_id,
      'criador_id' => $professor_id
  ]);

  if ($stmt->rowCount() === 0) {
      echo "Erro: Você não tem permissão para editar esta missão.";
  }
}

// Função para excluir uma missão
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];

  // Verifica se a missão pertence ao professor logado
  $professor_id = $_SESSION['professor_id'];
  $sql = "DELETE FROM missoes WHERE id = :id AND criador_id = :criador_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['id' => $id, 'criador_id' => $professor_id]);

  if ($stmt->rowCount() === 0) {
      echo "Erro: Você não tem permissão para excluir esta missão.";
  }
}


// Recupera apenas as missões criadas pelo professor logado
$professor_id = $_SESSION['professor_id'];
$sql = "SELECT * FROM missoes WHERE criador_id = :criador_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['criador_id' => $professor_id]);
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <style>
    form .form-control {
        width: 100% !important; /* Garante que os inputs ocupem toda a largura */
    }
</style>

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
          <li  >
            <a href="./missoes.php">
              <i class="nc-icon nc-user-run"></i>
              <p>Aprovar Missões</p>
            </a>
          </li>
          <li class="active">
            <a href="./editar_missoes.php">
              <i class="nc-icon nc-controller-modern"></i>
              <p>Editar Missões</p>
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
            <li class="nav-item">
              <a class="nav-link" href="editar_professor.php">Editar Professores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_secretaria.php">Editar Secretários</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editar_aluno.php">Editar Alunos</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="editar_loja.php">Editar Loja</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="missoes.php">Aprovar Missões</a>
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
        <div class="row">
          
          </div>
 <!-- Adicionar Missão -->
 <div class="card mb-4">
            <div class="card-header">Adicionar Nova Missão</div>
            <div class="card-body">
              
                <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($missao['id'] ?? ''); ?>">
    <div>
        <label>Turma:</label>
        <select class="form-select" aria-label="Default select example" name="turma_id" required>

            <?php
            $professor_id = $_SESSION['professor_id'];
            $sql = "SELECT t.id, t.nome 
                    FROM turmas_professores tp 
                    JOIN turmas t ON tp.turma_id = t.id 
                    WHERE tp.professor_id = :professor_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['professor_id' => $professor_id]);
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($turmas as $turma): ?>
                <option value="<?= $turma['id']; ?>" <?= isset($missao) && $missao['turma_id'] == $turma['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($turma['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
                <input type="hidden" name="id" value="<?= htmlspecialchars($missao['id'] ?? ''); ?>">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="xp" class="form-label">XP</label>
                        <input type="number" class="form-control" id="xp" name="xp" required>
                    </div>
                    <div class="mb-3">
                        <label for="moedas" class="form-label">Moedas</label>
                        <input type="number" class="form-control" id="moedas" name="moedas" required>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link</label>
                        <input type="url" class="form-control" id="link" name="link" >
                    </div>
                    <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
                </form>
            </div>
        </div>


    <!-- Listar Missões -->
    <div class="card">
        <div class="card-header">Missões Existentes</div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Nome</th>
                        <th style="width: 20%;">Descrição</th>
                        <th style="width: 5%;">XP</th>
                        <th style="width: 5%;">Moedas</th>
                        <th style="width: 5%;">Link</th>
                        <th style="width: 10%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($missoes as $missao): ?>
        <tr>
            <td><?= $missao['id']; ?></td>
            <td>
                <input type="text" name="nome" value="<?= htmlspecialchars($missao['nome']); ?>" class="form-control form-control-sm" style="width: 100%;" required>
            </td>
            <td>
                <textarea name="descricao" class="form-control form-control-sm" style="width: 100%;" required><?= htmlspecialchars($missao['descricao']); ?></textarea>
            </td>
            <td>
                <input type="number" name="xp" value="<?= $missao['xp']; ?>" class="form-control form-control-sm" style="width: 100%;" required>
            </td>
            <td>
                <input type="number" name="moedas" value="<?= $missao['moedas']; ?>" class="form-control form-control-sm" style="width: 100%;" required>
            </td>
            <td>
                <input type="url" name="link" value="<?= htmlspecialchars($missao['link']); ?>" class="form-control form-control-sm" style="width: 100%;" required>
            </td>
            <td>
                <form method="POST" class="d-inline-block">
                    <input type="hidden" name="id" value="<?= $missao['id']; ?>">
                    <button type="submit" name="edit" class="btn btn-success btn-sm">Salvar</button>
                </form>
                <a href="?delete=<?= $missao['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta missão?');">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
            <?php if (empty($missoes)): ?>
                <p class="text-center">Nenhuma missão encontrada.</p>
            <?php endif; ?>
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