<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'dashboard';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    // Redireciona para a página de login se a secretaria não estiver logada
    header('Location: ../../../login.php');
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
  <title>Painel da Secretaria</title>
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
    color: #000 !important; /* Define a cor do texto como preta */
  }

  .main-panel.d-md-none .navbar-nav .nav-link:hover {
    color: #007bff !important; /* Cor de hover azul */
  }
  </style>
</head>

<body>
<?php
include 'include/navbar.php';
?>
      <div class="content">
        <!-- Exibir mensagens de feedback -->
        <?php exibirMensagemSessao(); ?>
        
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
                      <p class="card-category">Total de Alunos</p>
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
                      <p class="card-category">Produtos na Loja</p>
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

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-lock-circle-open text-warning"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Trocas Pendentes</p>
                      <p class="card-title"><strong><?= count($trocas); ?></strong></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-clock-o"></i> Aguardando aprovação
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
                      <i class="nc-icon nc-user-run text-success"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Missões Pendentes</p>
                      <p class="card-title"><strong><?= count($solicitacoes); ?></strong></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-clock-o"></i> Aguardando aprovação
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
                <h4 class="card-title">
                  <i class="fa fa-shopping-cart text-warning"></i> Trocas Pendentes
                  <span class="badge badge-warning ml-2"><?= count($trocas); ?></span>
                </h4>
                <div class="card-tools">
                  <input type="text" class="form-control form-control-sm" id="searchTrocas" placeholder="Buscar trocas..." style="width: 250px;">
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaTrocas">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaTrocas')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaTrocas')">
                          <i class="fa fa-user"></i> Aluno <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(2, 'tabelaTrocas')">
                          <i class="fa fa-box"></i> Produto <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaTrocas')">
                          <i class="fa fa-calendar"></i> Data <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($trocas as $troca) : ?>
                        <tr class="troca-row" data-aluno="<?= strtolower($troca['aluno_nome']); ?>" data-produto="<?= strtolower($troca['produto_nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $troca['id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-user text-primary mr-2"></i>
                              <strong><?= htmlspecialchars($troca['aluno_nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-box text-warning mr-2"></i>
                              <span><?= htmlspecialchars($troca['produto_nome']); ?></span>
                            </div>
                          </td>
                          <td>
                            <span class="text-muted">
                              <i class="fa fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($troca['data_troca'])); ?>
                            </span>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <a href="tables.php" class="btn btn-success btn-sm" data-toggle="tooltip" title="Ver detalhes">
                                <i class="fa fa-eye"></i>
                              </a>
                              <button class="btn btn-info btn-sm" onclick="verDetalhesTroca(<?= $troca['id']; ?>)" data-toggle="tooltip" title="Ver detalhes">
                                <i class="fa fa-info-circle"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($trocas)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-check-circle fa-4x text-success mb-4"></i>
                      <h5 class="text-success">Nenhuma troca pendente!</h5>
                      <p class="text-muted">Todas as solicitações foram processadas.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">
                  <i class="fa fa-tasks text-success"></i> Missões Pendentes
                  <span class="badge badge-success ml-2"><?= count($solicitacoes); ?></span>
                </h4>
                <div class="card-tools">
                  <input type="text" class="form-control form-control-sm" id="searchMissoes" placeholder="Buscar missões..." style="width: 250px;">
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaMissoes">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaMissoes')">
                          <i class="fa fa-user"></i> Aluno <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaMissoes')">
                          <i class="fa fa-star"></i> Missão <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaMissoes')">
                          <i class="fa fa-star"></i> XP <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaMissoes')">
                          <i class="fa fa-coins"></i> Moedas <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($solicitacoes as $solicitacao): ?>
                        <tr class="missao-row" data-aluno="<?= strtolower($solicitacao['aluno_nome']); ?>" data-missao="<?= strtolower($solicitacao['missao_nome']); ?>">
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-user text-primary mr-2"></i>
                              <strong><?= htmlspecialchars($solicitacao['aluno_nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-star text-warning mr-2"></i>
                              <strong><?= htmlspecialchars($solicitacao['missao_nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="descricao-container">
                              <span class="text-muted" data-toggle="tooltip" title="<?= htmlspecialchars($solicitacao['descricao']); ?>">
                                <?= strlen($solicitacao['descricao']) > 80 ? substr($solicitacao['descricao'], 0, 80) . '...' : $solicitacao['descricao']; ?>
                              </span>
                              <button class="btn btn-link btn-sm p-0 ml-2" onclick="expandirDescricao(this)" data-descricao="<?= htmlspecialchars($solicitacao['descricao']); ?>">
                                <i class="fa fa-expand"></i>
                              </button>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-success">
                              <i class="fa fa-star"></i> <?= $solicitacao['xp']; ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-warning">
                              <i class="fa fa-coins"></i> <?= $solicitacao['moedas']; ?>
                            </span>
                          </td>
                          <td>
                            <form method="POST" action="processar_aprovacao.php" class="d-inline-block">
                              <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['solicitacao_id']; ?>">
                              <div class="btn-group" role="group">
                                <button class="btn btn-success btn-sm" type="submit" name="acao" value="aprovar" 
                                        data-toggle="tooltip" title="Aprovar missão">
                                  <i class="fa fa-check"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" type="submit" name="acao" value="rejeitar" 
                                        data-toggle="tooltip" title="Rejeitar missão">
                                  <i class="fa fa-times"></i>
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="verDetalhesMissao(<?= $solicitacao['solicitacao_id']; ?>)" 
                                        data-toggle="tooltip" title="Ver detalhes">
                                  <i class="fa fa-eye"></i>
                                </button>
                              </div>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($solicitacoes)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-check-circle fa-4x text-success mb-4"></i>
                      <h5 class="text-success">Nenhuma missão pendente!</h5>
                      <p class="text-muted">Todas as solicitações foram processadas.</p>
                    </div>
                  <?php endif; ?>
                </div>
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
                  © <script>document.write(new Date().getFullYear())</script>, feito com <i class="fa fa-heart heart"></i> pela Creative Tim
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
      
      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();
      
      // Search functionality for trocas
      $("#searchTrocas").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".troca-row").filter(function() {
          var aluno = $(this).data('aluno');
          var produto = $(this).data('produto');
          $(this).toggle(aluno.indexOf(value) > -1 || produto.indexOf(value) > -1);
        });
      });
      
      // Search functionality for missões
      $("#searchMissoes").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".missao-row").filter(function() {
          var aluno = $(this).data('aluno');
          var missao = $(this).data('missao');
          $(this).toggle(aluno.indexOf(value) > -1 || missao.indexOf(value) > -1);
        });
      });
      
      // Auto-hide alerts
      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
    });
    
    // Função para ordenar tabelas
    function sortTable(n, tableId) {
      var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
      table = document.getElementById(tableId);
      switching = true;
      dir = "asc";
      
      while (switching) {
        switching = false;
        rows = table.rows;
        
        for (i = 1; i < (rows.length - 1); i++) {
          shouldSwitch = false;
          x = rows[i].getElementsByTagName("TD")[n];
          y = rows[i + 1].getElementsByTagName("TD")[n];
          
          if (dir == "asc") {
            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
              shouldSwitch = true;
              break;
            }
          } else if (dir == "desc") {
            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
              shouldSwitch = true;
              break;
            }
          }
        }
        
        if (shouldSwitch) {
          rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
          switching = true;
          switchcount++;
        } else {
          if (switchcount == 0 && dir == "asc") {
            dir = "desc";
            switching = true;
          }
        }
      }
    }
    
    // Função para expandir descrição
    function expandirDescricao(button) {
      var descricao = $(button).data('descricao');
      var container = $(button).closest('.descricao-container');
      var span = container.find('span');
      
      if (span.text().includes('...')) {
        span.text(descricao);
        $(button).find('i').removeClass('fa-expand').addClass('fa-compress');
      } else {
        span.text(descricao.length > 80 ? descricao.substring(0, 80) + '...' : descricao);
        $(button).find('i').removeClass('fa-compress').addClass('fa-expand');
      }
    }
    
    // Função para ver detalhes da troca
    function verDetalhesTroca(id) {
      showToast('info', 'Informação', 'Detalhes da troca serão implementados em breve.');
    }
    
    // Função para ver detalhes da missão
    function verDetalhesMissao(id) {
      showToast('info', 'Informação', 'Detalhes da missão serão implementados em breve.');
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
      
      // Remove toast after 3 seconds
      setTimeout(function() {
        $('.toast').remove();
      }, 3000);
    }
  </script>
</body>

</html>
