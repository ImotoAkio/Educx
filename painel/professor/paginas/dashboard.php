<?php
require '../../../db.php';
session_start();

// Verificação de sessão e tipo de usuário
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
  header("Location: ../../../login.php");
  exit;
}

try {
  // Conexão ao banco de dados
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Consulta ao número de alunos
  $stmtAlunos = $pdo->query("SELECT COUNT(*) as total_alunos FROM alunos");
  $resultAlunos = $stmtAlunos->fetch(PDO::FETCH_ASSOC);
  $total_alunos = $resultAlunos['total_alunos'];

  // Consulta ao número de produtos
  $stmtProdutos = $pdo->query("SELECT COUNT(*) as total_produtos FROM produtos");
  $resultProdutos = $stmtProdutos->fetch(PDO::FETCH_ASSOC);
  $total_produtos = $resultProdutos['total_produtos'];

  // Consulta às trocas pendentes
  $stmtTrocas = $pdo->query("
        SELECT t.id, a.nome AS aluno_nome, p.nome AS produto_nome 
        FROM trocas t
        JOIN alunos a ON t.aluno_id = a.id
        JOIN produtos p ON t.produto_id = p.id
        WHERE t.status = 'pendente'
    ");
  $trocas = $stmtTrocas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
$stmt = $pdo->query("
    SELECT 
        s.id AS solicitacao_id,
        a.nome AS aluno_nome,
        m.nome AS missao_nome,
        m.descricao,
        m.xp,
        m.moedas
    FROM solicitacoes_missoes s
    JOIN alunos a ON s.aluno_id = a.id
    JOIN missoes m ON s.missao_id = m.id
    WHERE s.status = 'pendente'
");
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>Painel Secretaria</title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!-- Fonts and icons -->
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
<?php
include 'include/navbar.php';
?>

      <div class="content">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-hat-3 text-warning"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Alunos</p>
                      <p class="card-title"><strong><?= $total_alunos; ?></strong></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i> Atualizado agora
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-box text-primary"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Produtos</p>
                      <p class="card-title"><strong><?= $total_produtos; ?></strong></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i> Atualizado agora
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabela de trocas pendentes -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Trocas Pendentes</h4>
              </div>
              <div class="card-body">
                <table class="table table-striped">
                  <thead class="text-primary">
                    <tr>
                      <th>ID</th>
                      <th>Aluno</th>
                      <th>Produto</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($trocas as $troca) : ?>
                      <tr>
                        <td><?= $troca['id']; ?></td>
                        <td><?= $troca['aluno_nome']; ?></td>
                        <td><?= $troca['produto_nome']; ?></td>
                        <td><span class="badge badge-warning">Pendente</span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Missões Pendentes</h4>
              </div>
              <div class="card-body">
                <table class="table table-striped">
                  <thead class="text-primary">
                    <tr>
                      <th>Aluno</th>
                      <th>Missão</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                      <tr>
                        <td><?= htmlspecialchars($solicitacao['aluno_nome']); ?></td>
                        <td><?= htmlspecialchars($solicitacao['missao_nome']); ?></td>
                        <td>
                          <form method="POST" action="processar_aprovacao.php">
                            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['solicitacao_id']; ?>">
                            <button class="btn btn-success btn-sm" type="submit" name="acao" value="aprovar">Aprovar</button>
                            <button class="btn btn-danger btn-sm" type="submit" name="acao" value="rejeitar">Rejeitar</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <div class="credits ml-auto">
                <span class="copyright">
                  © <script>
                    document.write(new Date().getFullYear())
                  </script>, feito com <i class="fa fa-heart heart"></i> pela Creative Tim
                </span>
              </div>
            </nav>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- JS Files -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>
  <script src="../assets/js/paper-dashboard.min.js?v=2.0.1"></script>
  <script src="../assets/demo/demo.js"></script>
  <script>
    $(document).ready(function() {
      $(".navbar-toggler").click(function() {
        $(this).find(".navbar-toggler-bar").toggle();
      });

      demo.initChartsPages();
    });
  </script>
</body>

</html>