<?php
// Conexão com o banco de dados
session_start();
require '../../../db.php'; // Conexão com o banco

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'turmas';

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Adicionar Turma
if (isset($_POST['add_turma'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $ano_letivo = (int)$_POST['ano_letivo'];

    // Validação
    if (strlen($nome) < 2) {
        redirecionarComMensagem('turmas.php', 'error', 'O nome da turma deve ter pelo menos 2 caracteres.');
    }
    
    if ($ano_letivo < 2000 || $ano_letivo > 2100) {
        redirecionarComMensagem('turmas.php', 'error', 'O ano letivo deve estar entre 2000 e 2100.');
    }

    try {
        $sql = "INSERT INTO turmas (nome, descricao, ano_letivo) VALUES (:nome, :descricao, :ano_letivo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome, 
            'descricao' => $descricao ?: null, 
            'ano_letivo' => $ano_letivo
        ]);
        
        redirecionarComMensagem('turmas.php', 'success', "Turma '$nome' adicionada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('turmas.php', 'error', 'Erro ao adicionar turma: ' . $e->getMessage());
    }
}

// Editar Turma
if (isset($_POST['edit_turma'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $ano_letivo = (int)$_POST['ano_letivo'];

    // Validação
    if (strlen($nome) < 2) {
        redirecionarComMensagem('turmas.php', 'error', 'O nome da turma deve ter pelo menos 2 caracteres.');
    }
    
    if ($ano_letivo < 2000 || $ano_letivo > 2100) {
        redirecionarComMensagem('turmas.php', 'error', 'O ano letivo deve estar entre 2000 e 2100.');
    }

    try {
        $sql = "UPDATE turmas SET nome = :nome, descricao = :descricao, ano_letivo = :ano_letivo WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $nome, 
            'descricao' => $descricao ?: null, 
            'ano_letivo' => $ano_letivo
        ]);
        
        redirecionarComMensagem('turmas.php', 'success', "Turma '$nome' atualizada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('turmas.php', 'error', 'Erro ao atualizar turma: ' . $e->getMessage());
    }
}

// Função para remover uma turma
if (isset($_GET['delete_turma'])) {
    $turma_id = $_GET['delete_turma'];

    try {
        // Busca o nome da turma antes de deletar
        $stmt = $pdo->prepare("SELECT nome FROM turmas WHERE id = :id");
        $stmt->execute(['id' => $turma_id]);
        $turma = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($turma) {
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
            
            redirecionarComMensagem('turmas.php', 'success', "Turma '{$turma['nome']}' removida com sucesso!");
        } else {
            redirecionarComMensagem('turmas.php', 'error', 'Turma não encontrada.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('turmas.php', 'error', 'Erro ao remover turma: ' . $e->getMessage());
    }
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
  <title>Painel da Secretaria</title>
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
                  <i class="fa fa-graduation-cap text-primary"></i> Gerenciar Turmas
                  <span class="badge badge-primary ml-2"><?= count($turmas); ?></span>
                </h4>
                <div class="card-tools">
                  <input type="text" class="form-control form-control-sm" id="searchTurmas" placeholder="Buscar turmas..." style="width: 250px;">
                  <button class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#addTurmaModal">
                    <i class="fa fa-plus"></i> Adicionar Turma
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaTurmas">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaTurmas')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaTurmas')">
                          <i class="fa fa-graduation-cap"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaTurmas')">
                          <i class="fa fa-calendar"></i> Ano Letivo <i class="fa fa-sort"></i>
                        </th>
                        <th>Professores</th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($turmas as $turma): ?>
                        <tr class="turma-row" data-nome="<?= strtolower($turma['turma_nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $turma['turma_id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-graduation-cap text-primary mr-2"></i>
                              <strong><?= htmlspecialchars($turma['turma_nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="descricao-container">
                              <span class="text-muted" data-toggle="tooltip" title="<?= addslashes(htmlspecialchars($turma['descricao'])); ?>">
                                <?= strlen($turma['descricao']) > 80 ? substr(htmlspecialchars($turma['descricao']), 0, 80) . '...' : htmlspecialchars($turma['descricao']); ?>
                              </span>
                              <button class="btn btn-link btn-sm p-0 ml-2" onclick="expandirDescricao(this)" data-descricao="<?= addslashes(htmlspecialchars($turma['descricao'])); ?>">
                                <i class="fa fa-expand"></i>
                              </button>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-info">
                              <i class="fa fa-calendar"></i> <?= $turma['ano_letivo']; ?>
                            </span>
                          </td>
                          <td>
                            <?php if (!empty($turma['professores_associados'])): ?>
                              <span class="text-muted">
                                <i class="fa fa-users mr-1"></i>
                                <?= htmlspecialchars($turma['professores_associados']); ?>
                              </span>
                            <?php else: ?>
                              <span class="text-muted">Nenhum professor</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="verAlunos(<?= $turma['turma_id']; ?>, '<?= addslashes(htmlspecialchars($turma['turma_nome'])); ?>')" 
                                      data-toggle="tooltip" title="Ver alunos da turma">
                                <i class="fa fa-users"></i>
                              </button>
                              <button class="btn btn-warning btn-sm" onclick="editarTurma(<?= $turma['turma_id']; ?>, '<?= addslashes(htmlspecialchars($turma['turma_nome'])); ?>', '<?= addslashes(htmlspecialchars($turma['descricao'])); ?>', '<?= $turma['ano_letivo']; ?>')" 
                                      data-toggle="tooltip" title="Editar turma">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $turma['turma_id']; ?>, '<?= addslashes(htmlspecialchars($turma['turma_nome'])); ?>')" 
                                      data-toggle="tooltip" title="Remover turma">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($turmas)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-graduation-cap fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhuma turma cadastrada</h5>
                      <p class="text-muted">Adicione a primeira turma usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Turma -->
      <div class="modal fade" id="addTurmaModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Turma
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST" id="addTurmaForm">
              <div class="modal-body">
                <div class="form-group">
                  <label for="nome">Nome da Turma <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nome" name="nome" required maxlength="255">
                  <small class="form-text text-muted">Ex: 1º ano A, 2º ano B, etc.</small>
                </div>
                <div class="form-group">
                  <label for="descricao">Descrição</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="500"></textarea>
                  <small class="form-text text-muted">
                    <span id="charCount">0</span>/500 caracteres
                  </small>
                </div>
                <div class="form-group">
                  <label for="ano_letivo">Ano Letivo <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="ano_letivo" name="ano_letivo" placeholder="Ex: 2024" required min="2000" max="2100">
                  <small class="form-text text-muted">Ano letivo da turma (entre 2000 e 2100)</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="add_turma" class="btn btn-success">
                  <i class="fa fa-plus"></i> Adicionar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Ver Alunos da Turma -->
      <div class="modal fade" id="verAlunosModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-users text-info"></i> Alunos da Turma: <span id="turmaNome"></span>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="alunosTurmaContent">
                <!-- Conteúdo será carregado via AJAX -->
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Editar Turma -->
      <div class="modal fade" id="editTurmaModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-warning"></i> Editar Turma
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST" id="editTurmaForm">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_nome">Nome da Turma <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_nome" name="nome" required maxlength="255">
                  <small class="form-text text-muted">Ex: 1º ano A, 2º ano B, etc.</small>
                </div>
                <div class="form-group">
                  <label for="edit_descricao">Descrição</label>
                  <textarea class="form-control" id="edit_descricao" name="descricao" rows="3" maxlength="500"></textarea>
                  <small class="form-text text-muted">
                    <span id="editCharCount">0</span>/500 caracteres
                  </small>
                </div>
                <div class="form-group">
                  <label for="edit_ano_letivo">Ano Letivo <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="edit_ano_letivo" name="ano_letivo" placeholder="Ex: 2024" required min="2000" max="2100">
                  <small class="form-text text-muted">Ano letivo da turma (entre 2000 e 2100)</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="edit_turma" class="btn btn-warning">
                  <i class="fa fa-save"></i> Salvar
                </button>
              </div>
            </form>
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
      
      // Auto-hide alerts
      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
      
      // Função de busca para turmas
      $('#searchTurmas').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#tabelaTurmas tbody tr').filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
      });
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
    
    // Função para ver alunos da turma
    function verAlunos(turmaId, turmaNome) {
      $('#turmaNome').text(turmaNome);
      $('#alunosTurmaContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Carregando alunos...</p></div>');
      $('#verAlunosModal').modal('show');
      
      // Fazer requisição AJAX para buscar alunos
      $.ajax({
        url: 'buscar_alunos_turma.php',
        type: 'POST',
        data: { turma_id: turmaId },
        dataType: 'json',
        success: function(data) {
          if (data.error) {
            $('#alunosTurmaContent').html(`
              <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> ${data.error}
              </div>
            `);
          } else {
            // Exibir informações da turma
            var turmaInfo = `
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-graduation-cap text-primary"></i> Informações da Turma
                      </h6>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm">
                        <tr>
                          <td><strong>Nome:</strong></td>
                          <td>${data.turma.nome}</td>
                        </tr>
                        <tr>
                          <td><strong>Ano Letivo:</strong></td>
                          <td>${data.turma.ano_letivo || 'N/A'}</td>
                        </tr>
                        <tr>
                          <td><strong>Descrição:</strong></td>
                          <td>${data.turma.descricao || 'N/A'}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-chart-bar text-success"></i> Estatísticas
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="row text-center">
                        <div class="col-6">
                          <h4 class="text-primary">${data.estatisticas.total_alunos}</h4>
                          <small class="text-muted">Alunos</small>
                        </div>
                        <div class="col-6">
                          <h4 class="text-success">${data.estatisticas.total_professores}</h4>
                          <small class="text-muted">Professores</small>
                        </div>
                      </div>
                      <hr>
                      <div class="row text-center">
                        <div class="col-6">
                          <h5 class="text-warning">${data.estatisticas.media_moedas}</h5>
                          <small class="text-muted">Média de Moedas</small>
                        </div>
                        <div class="col-6">
                          <h5 class="text-info">${data.estatisticas.media_xp}</h5>
                          <small class="text-muted">Média de XP</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `;
            
            // Exibir professores
            var professoresHtml = '';
            if (data.professores.length > 0) {
              professoresHtml = `
                <div class="card mb-4">
                  <div class="card-header">
                    <h6 class="card-title">
                      <i class="fa fa-users text-info"></i> Professores da Turma (${data.professores.length})
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-sm">
                                                 <thead>
                           <tr>
                             <th>Nome</th>
                           </tr>
                         </thead>
                         <tbody>
                           ${data.professores.map(function(professor) {
                             return `
                               <tr>
                                 <td>
                                   <i class="fa fa-user text-primary mr-2"></i>
                                   ${professor.nome}
                                 </td>
                               </tr>
                             `;
                           }).join('')}
                         </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              `;
            }
            
            // Exibir alunos
            var alunosHtml = '';
            if (data.alunos.length > 0) {
              alunosHtml = `
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title">
                      <i class="fa fa-users text-success"></i> Alunos da Turma (${data.alunos.length})
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-sm">
                                                 <thead>
                           <tr>
                             <th>Nome</th>
                             <th>Moedas</th>
                             <th>XP</th>
                             <th>Nível</th>
                           </tr>
                         </thead>
                         <tbody>
                           ${data.alunos.map(function(aluno) {
                             return `
                               <tr>
                                 <td>
                                   <i class="fa fa-user text-success mr-2"></i>
                                   ${aluno.nome}
                                 </td>
                                 <td>
                                   <span class="badge badge-warning">
                                     <i class="fa fa-coins"></i> ${aluno.moedas}
                                   </span>
                                 </td>
                                 <td>
                                   <span class="badge badge-info">
                                     <i class="fa fa-star"></i> ${aluno.xp_total}
                                   </span>
                                 </td>
                                 <td>
                                   <span class="badge badge-primary">
                                     Nível ${aluno.nivel}
                                   </span>
                                 </td>
                               </tr>
                             `;
                           }).join('')}
                         </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              `;
            } else {
              alunosHtml = `
                <div class="card">
                  <div class="card-body text-center">
                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum aluno vinculado</h5>
                    <p class="text-muted">Esta turma ainda não possui alunos vinculados.</p>
                  </div>
                </div>
              `;
            }
            
            $('#alunosTurmaContent').html(turmaInfo + professoresHtml + alunosHtml);
          }
        },
        error: function(xhr, status, error) {
          $('#alunosTurmaContent').html(`
            <div class="alert alert-danger">
              <i class="fa fa-exclamation-triangle"></i> Erro ao carregar dados: ${error}
            </div>
          `);
        }
      });
    }
    
    // Função para editar turma
    function editarTurma(id, nome, descricao, ano_letivo) {
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_descricao').val(descricao);
      $('#edit_ano_letivo').val(ano_letivo);
      $('#editCharCount').text(descricao.length);
      $('#editTurmaModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
      if (confirm('Tem certeza que deseja remover a turma "' + nome + '"?\n\nEsta ação não pode ser desfeita e removerá todas as associações com alunos e professores.')) {
        window.location.href = '?delete_turma=' + id;
      }
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
    
    // Validação do formulário de adicionar turma
    $('#addTurmaForm').on('submit', function(e) {
      var nome = $('#nome').val().trim();
      var ano_letivo = $('#ano_letivo').val();
      
      if (nome.length < 2) {
        e.preventDefault();
        showToast('error', 'Erro', 'O nome da turma deve ter pelo menos 2 caracteres.');
        $('#nome').focus();
        return false;
      }
      
      if (ano_letivo < 2000 || ano_letivo > 2100) {
        e.preventDefault();
        showToast('error', 'Erro', 'O ano letivo deve estar entre 2000 e 2100.');
        $('#ano_letivo').focus();
        return false;
      }
      
      // Mostrar loading no botão
      var submitBtn = $(this).find('button[type="submit"]');
      var originalText = submitBtn.html();
      submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Adicionando...').prop('disabled', true);
      
      // Reabilitar após 3 segundos (caso haja erro)
      setTimeout(function() {
        submitBtn.html(originalText).prop('disabled', false);
      }, 3000);
    });
    
    // Validação do formulário de editar turma
    $('#editTurmaForm').on('submit', function(e) {
      var nome = $('#edit_nome').val().trim();
      var ano_letivo = $('#edit_ano_letivo').val();
      
      if (nome.length < 2) {
        e.preventDefault();
        showToast('error', 'Erro', 'O nome da turma deve ter pelo menos 2 caracteres.');
        $('#edit_nome').focus();
        return false;
      }
      
      if (ano_letivo < 2000 || ano_letivo > 2100) {
        e.preventDefault();
        showToast('error', 'Erro', 'O ano letivo deve estar entre 2000 e 2100.');
        $('#edit_ano_letivo').focus();
        return false;
      }
      
      // Mostrar loading no botão
      var submitBtn = $(this).find('button[type="submit"]');
      var originalText = submitBtn.html();
      submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Salvando...').prop('disabled', true);
      
      // Reabilitar após 3 segundos (caso haja erro)
      setTimeout(function() {
        submitBtn.html(originalText).prop('disabled', false);
      }, 3000);
    });
    
    // Limpar formulário quando modal é fechado
    $('#addTurmaModal').on('hidden.bs.modal', function() {
      $('#addTurmaForm')[0].reset();
      $('#addTurmaForm .is-invalid').removeClass('is-invalid');
      $('#charCount').text('0');
    });
    
    // Limpar formulário quando modal de editar é fechado
    $('#editTurmaModal').on('hidden.bs.modal', function() {
      $('#editTurmaForm .is-invalid').removeClass('is-invalid');
    });
    
    // Contador de caracteres para descrição
    $('#descricao').on('input', function() {
      var length = $(this).val().length;
      $('#charCount').text(length);
      
      if (length > 450) {
        $('#charCount').addClass('text-warning');
      } else {
        $('#charCount').removeClass('text-warning');
      }
      
      if (length >= 500) {
        $('#charCount').removeClass('text-warning').addClass('text-danger');
      }
    });
    
    // Contador de caracteres para descrição do modal de editar
    $('#edit_descricao').on('input', function() {
      var length = $(this).val().length;
      $('#editCharCount').text(length);
      
      if (length > 450) {
        $('#editCharCount').addClass('text-warning');
      } else {
        $('#editCharCount').removeClass('text-warning');
      }
      
      if (length >= 500) {
        $('#editCharCount').removeClass('text-warning').addClass('text-danger');
      }
    });
  </script>
</body>

</html>