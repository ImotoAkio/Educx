<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'tables';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    // Redireciona para a página de login se a secretaria não estiver logada
    header('Location: ../../../login.php');
    exit;
}

// Consulta as trocas pendentes, incluindo data, status e valor das moedas
$trocasStmt = $pdo->query("SELECT t.id, t.data_troca, t.status, a.nome AS aluno_nome, p.nome AS produto_nome, p.moeda AS produto_moeda 
                           FROM trocas t
                           JOIN alunos a ON t.aluno_id = a.id
                           JOIN produtos p ON t.produto_id = p.id
                           WHERE t.status = 'pendente'");
$trocas = $trocasStmt->fetchAll(PDO::FETCH_ASSOC);
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
  <title>Painel da Secretaria</title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <!-- Mobile CSS -->
  <link href="../assets/css/mobile-header.css" rel="stylesheet" />
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
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">
                  <i class="fa fa-shopping-cart text-warning"></i> Aprovar Trocas
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
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaTrocas')">
                          <i class="fa fa-coins"></i> Valor <i class="fa fa-sort"></i>
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
                            <span class="badge badge-info">
                              <i class="fa fa-coins"></i> <?= $troca['produto_moeda']; ?> moedas
                            </span>
                          </td>
                          <td>
                            <form method="POST" action="processar_aprovacao.php" class="d-inline-block">
                              <input type="hidden" name="troca_id" value="<?= $troca['id']; ?>">
                              <div class="btn-group" role="group">
                                <button class="btn btn-success btn-sm" type="submit" name="acao" value="aprovar" 
                                        data-toggle="tooltip" title="Aprovar troca">
                                  <i class="fa fa-check"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" type="submit" name="acao" value="rejeitar" 
                                        data-toggle="tooltip" title="Rejeitar troca">
                                  <i class="fa fa-times"></i>
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="verDetalhesTroca(<?= $troca['id']; ?>)" 
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
      </div>

      <!-- Modal Detalhes da Troca -->
      <div class="modal fade" id="detalhesTrocaModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-shopping-cart text-info"></i> Detalhes da Troca #<span id="trocaId"></span>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="detalhesTrocaContent">
                <div class="text-center">
                  <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                  <p class="text-muted mt-2">Carregando detalhes...</p>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
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
    
    // Função para ver detalhes da troca
    function verDetalhesTroca(id) {
      // Atualiza o ID no título do modal
      $('#trocaId').text(id);
      
      // Mostra o modal
      $('#detalhesTrocaModal').modal('show');
      
      // Busca os detalhes via AJAX
      $.ajax({
        url: 'buscar_detalhes_troca.php',
        type: 'GET',
        data: { troca_id: id },
        dataType: 'json',
        success: function(data) {
          if (data.error) {
            $('#detalhesTrocaContent').html(`
              <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> ${data.error}
              </div>
            `);
          } else {
            // Formata os dados para exibição
            var statusClass = data.status === 'pendente' ? 'warning' : 
                             data.status === 'aprovado' ? 'success' : 'danger';
            var statusText = data.status === 'pendente' ? 'Pendente' : 
                            data.status === 'aprovado' ? 'Aprovado' : 'Rejeitado';
            
            $('#detalhesTrocaContent').html(`
              <div class="row">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-user text-primary"></i> Informações do Aluno
                      </h6>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm">
                                                                        <tr>
                                                    <td><strong>Nome:</strong></td>
                                                    <td>${data.aluno.nome}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Moedas Atuais:</strong></td>
                                                    <td><span class="badge badge-info">${data.aluno.moedas} moedas</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XP Total:</strong></td>
                                                    <td><span class="badge badge-success">${data.aluno.xp_total} XP</span></td>
                                                </tr>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-box text-warning"></i> Informações do Produto
                      </h6>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm">
                                                                        <tr>
                                                    <td><strong>Nome:</strong></td>
                                                    <td>${data.produto.nome}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Descrição:</strong></td>
                                                    <td>${data.produto.descricao || 'N/A'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Preço:</strong></td>
                                                    <td><span class="badge badge-warning">${data.produto.moeda} moedas</span></td>
                                                </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-info-circle text-info"></i> Informações da Transação
                      </h6>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm">
                        <tr>
                          <td><strong>ID da Troca:</strong></td>
                          <td><span class="badge badge-secondary">#${data.id}</span></td>
                        </tr>
                        <tr>
                          <td><strong>Data da Solicitação:</strong></td>
                          <td><i class="fa fa-calendar"></i> ${data.data_troca}</td>
                        </tr>
                        <tr>
                          <td><strong>Status:</strong></td>
                          <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                        </tr>
                        <tr>
                          <td><strong>Saldo Após Troca:</strong></td>
                          <td><span class="badge badge-info">${data.aluno.moedas - data.produto.moeda} moedas</span></td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            `);
          }
        },
        error: function(xhr, status, error) {
          $('#detalhesTrocaContent').html(`
            <div class="alert alert-danger">
              <i class="fa fa-exclamation-triangle"></i> Erro ao carregar detalhes: ${error}
            </div>
          `);
        }
      });
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
  
  <!-- Footer com scripts mobile -->
  <?php include 'include/footer.php'; ?>
</body>

</html>