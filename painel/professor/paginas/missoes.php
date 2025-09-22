<?php
require '../../../db.php';
session_start();

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'missoes';

// Verifica se o usuário é um professor logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: ../../../login.php");
    exit;
}

// Obtém o ID do professor logado
$professor_id = $_SESSION['usuario_id'];

// Buscar todas as solicitações pendentes de missões criadas pelo professor logado
$stmt = $pdo->prepare("
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
    WHERE s.status = 'pendente' AND m.criador_id = :professor_id
");
$stmt->execute(['professor_id' => $professor_id]);
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    Painel do Professor
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
                  <i class="fa fa-tasks"></i> Aprovar Solicitações de Missões
                  <span class="badge badge-info ml-2"><?= count($solicitacoes); ?></span>
                </h4>
                <div class="card-tools">
                  <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control form-control-sm" id="searchMissoes" placeholder="Buscar por aluno ou missão...">
                    <div class="input-group-append">
                      <button class="btn btn-sm btn-outline-secondary" type="button" onclick="limparBusca()">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaMissoes">
                    <thead class="text-primary">
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
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($solicitacoes as $solicitacao): ?>
                        <tr class="missao-row" data-aluno="<?= strtolower($solicitacao['aluno_nome']); ?>" data-missao="<?= strtolower($solicitacao['missao_nome']); ?>">
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar-sm mr-3">
                                <i class="fa fa-user-circle fa-2x text-primary"></i>
                              </div>
                              <div>
                                <strong><?= htmlspecialchars($solicitacao['aluno_nome']); ?></strong>
                                <br>
                                <small class="text-muted">ID: <?= $solicitacao['solicitacao_id']; ?></small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar-sm mr-3">
                                <i class="fa fa-star fa-2x text-warning"></i>
                              </div>
                              <div>
                                <strong><?= htmlspecialchars($solicitacao['missao_nome']); ?></strong>
                                <br>
                                <small class="text-muted">Missão</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="descricao-container">
                              <span class="text-muted" data-toggle="tooltip" title="<?= htmlspecialchars($solicitacao['descricao']); ?>">
                                <?= strlen($solicitacao['descricao']) > 60 ? substr($solicitacao['descricao'], 0, 60) . '...' : $solicitacao['descricao']; ?>
                              </span>
                              <button class="btn btn-link btn-sm p-0 ml-2" onclick="expandirDescricao(this)" data-descricao="<?= htmlspecialchars($solicitacao['descricao']); ?>">
                                <i class="fa fa-expand"></i>
                              </button>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-success badge-lg">
                              <i class="fa fa-star"></i> <?= $solicitacao['xp']; ?> XP
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-warning badge-lg">
                              <i class="fa fa-coins"></i> <?= $solicitacao['moedas']; ?> Moedas
                            </span>
                          </td>
                          <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                              <form method="POST" action="processar_aprovacao.php" class="d-inline mb-1">
                                <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['solicitacao_id']; ?>">
                                <button class="btn btn-success btn-block" type="submit" name="acao" value="aprovar" 
                                        data-toggle="tooltip" title="Aprovar missão do aluno">
                                  <i class="fa fa-check"></i> Aprovar
                                </button>
                              </form>
                              <form method="POST" action="processar_aprovacao.php" class="d-inline mb-1">
                                <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['solicitacao_id']; ?>">
                                <button class="btn btn-danger btn-block" type="submit" name="acao" value="rejeitar" 
                                        data-toggle="tooltip" title="Rejeitar missão do aluno">
                                  <i class="fa fa-times"></i> Rejeitar
                                </button>
                              </form>
                              <button type="button" class="btn btn-info btn-block" onclick="verDetalhesMissao(<?= $solicitacao['solicitacao_id']; ?>)" 
                                      data-toggle="tooltip" title="Ver detalhes da missão">
                                <i class="fa fa-eye"></i> Detalhes
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($solicitacoes)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-check-circle fa-4x text-success mb-4"></i>
                      <h5 class="text-muted">Nenhuma solicitação pendente</h5>
                      <p class="text-muted">Todos os alunos estão em dia com suas missões!</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer class="footer footer-black  footer-white ">
      <div class="container-fluid">
        <div class="row">

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
  
  <script>
    $(document).ready(function() {
      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();
      
      // Search functionality
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
    
    // Função para limpar busca
    function limparBusca() {
      $("#searchMissoes").val('');
      $(".missao-row").show();
    }
    
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
        span.text(descricao.length > 60 ? descricao.substring(0, 60) + '...' : descricao);
        $(button).find('i').removeClass('fa-compress').addClass('fa-expand');
      }
    }
    
    // Função para ver detalhes da missão
    function verDetalhesMissao(id) {
      // Buscar dados da missão via AJAX
      $.ajax({
        url: 'ajax/buscar_detalhes_missao.php',
        method: 'POST',
        data: { solicitacao_id: id },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            mostrarModalDetalhes(response.data);
          } else {
            showToast('error', 'Erro', 'Erro ao carregar detalhes da missão: ' + response.message);
          }
        },
        error: function() {
          showToast('error', 'Erro', 'Erro ao carregar detalhes da missão.');
        }
      });
    }
    
    // Função para mostrar modal com detalhes
    function mostrarModalDetalhes(dados) {
      var modalHtml = `
        <div class="modal fade" id="modalDetalhesMissao" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                  <i class="fa fa-trophy mr-2"></i>Detalhes da Missão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                  <span>&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                      <i class="fa fa-user mr-1"></i> Informações do Aluno
                    </h6>
                    <table class="table table-sm">
                      <tr>
                        <td><strong>Nome:</strong></td>
                        <td>${dados.aluno_nome}</td>
                      </tr>
                      <tr>
                        <td><strong>ID:</strong></td>
                        <td>#${dados.aluno_id}</td>
                      </tr>
                      <tr>
                        <td><strong>XP Atual:</strong></td>
                        <td><span class="badge badge-success">${dados.aluno_xp}</span></td>
                      </tr>
                      <tr>
                        <td><strong>Moedas:</strong></td>
                        <td><span class="badge badge-warning">${dados.aluno_moedas}</span></td>
                      </tr>
                      <tr>
                        <td><strong>Nível:</strong></td>
                        <td><span class="badge badge-info">${dados.aluno_nivel || 'N/A'}</span></td>
                      </tr>
                      ${dados.turma_nome ? `
                      <tr>
                        <td><strong>Turma:</strong></td>
                        <td><span class="badge badge-secondary">${dados.turma_nome}</span></td>
                      </tr>
                      ` : ''}
                    </table>
                  </div>
                  <div class="col-md-6">
                    <h6 class="text-success mb-3">
                      <i class="fa fa-tasks mr-1"></i> Detalhes da Missão
                    </h6>
                    <table class="table table-sm">
                      <tr>
                        <td><strong>Nome:</strong></td>
                        <td>${dados.missao_nome}</td>
                      </tr>
                      <tr>
                        <td><strong>Descrição:</strong></td>
                        <td>${dados.missao_descricao}</td>
                      </tr>
                      <tr>
                        <td><strong>Recompensa XP:</strong></td>
                        <td><span class="badge badge-success">+${dados.missao_xp}</span></td>
                      </tr>
                      <tr>
                        <td><strong>Recompensa Moedas:</strong></td>
                        <td><span class="badge badge-warning">+${dados.missao_moedas}</span></td>
                      </tr>
                      <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge badge-warning">Pendente</span></td>
                      </tr>
                    </table>
                    ${dados.missao_link ? `
                    <div class="mt-3">
                      <a href="${dados.missao_link}" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fa fa-external-link"></i> Ver Link da Missão
                      </a>
                    </div>
                    ` : ''}
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-12">
                    <h6 class="text-info mb-3">
                      <i class="fa fa-info-circle mr-1"></i> Status da Solicitação
                    </h6>
                    <div class="row">
                      <div class="col-md-4">
                        <strong>Data:</strong><br>
                        <span class="text-muted">${dados.data_solicitacao_formatada}</span>
                      </div>
                      <div class="col-md-4">
                        <strong>Hora:</strong><br>
                        <span class="text-muted">${dados.hora_solicitacao}</span>
                      </div>
                      <div class="col-md-4">
                        <strong>Tempo decorrido:</strong><br>
                        <span class="text-primary">${dados.tempo_decorrido}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  <i class="fa fa-times mr-1"></i> Fechar
                </button>
                <form method="POST" action="processar_aprovacao.php" class="d-inline">
                  <input type="hidden" name="solicitacao_id" value="${dados.solicitacao_id}">
                  <button type="submit" name="acao" value="aprovar" class="btn btn-success">
                    <i class="fa fa-check mr-1"></i> Aprovar
                  </button>
                  <button type="submit" name="acao" value="rejeitar" class="btn btn-danger" 
                          onclick="return confirm('Tem certeza que deseja rejeitar esta missão?')">
                    <i class="fa fa-times mr-1"></i> Rejeitar
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Remover modal anterior se existir
      $('#modalDetalhesMissao').remove();
      
      // Adicionar novo modal ao body
      $('body').append(modalHtml);
      
      // Mostrar modal
      $('#modalDetalhesMissao').modal('show');
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

<?php include 'include/footer.php'; ?>