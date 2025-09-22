<?php
// Conexão com o banco de dados e início da sessão
session_start();
require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_missoes';

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Função para adicionar uma missão
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $xp = $_POST['xp'];
    $moedas = $_POST['moedas'];
    $link = $_POST['link'];
    $status = $_POST['status'] ?? 'ativa';
    $turma_id = !empty($_POST['turma_id']) ? $_POST['turma_id'] : null;

    try {
        $sql = "INSERT INTO missoes (nome, descricao, xp, moedas, link, status, turma_id) VALUES (:nome, :descricao, :xp, :moedas, :link, :status, :turma_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'xp' => $xp,
            'moedas' => $moedas,
            'link' => $link,
            'status' => $status,
            'turma_id' => $turma_id
        ]);
        
        redirecionarComMensagem('editar_missoes.php', 'success', "Missão '$nome' adicionada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao adicionar missão: ' . $e->getMessage());
    }
}

// Função para editar uma missão
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $xp = $_POST['xp'];
    $moedas = $_POST['moedas'];
    $link = $_POST['link'];
    $status = $_POST['status'] ?? 'ativa';
    $turma_id = !empty($_POST['turma_id']) ? $_POST['turma_id'] : null;

    try {
        $sql = "UPDATE missoes SET nome = :nome, descricao = :descricao, xp = :xp, moedas = :moedas, link = :link, status = :status, turma_id = :turma_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'descricao' => $descricao,
            'xp' => $xp,
            'moedas' => $moedas,
            'link' => $link,
            'status' => $status,
            'turma_id' => $turma_id
        ]);
        
        redirecionarComMensagem('editar_missoes.php', 'success', "Missão '$nome' atualizada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao atualizar missão: ' . $e->getMessage());
    }
}

// Função para excluir uma missão
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        // Busca o nome da missão antes de deletar
        $stmt = $pdo->prepare("SELECT nome FROM missoes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $missao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($missao) {
            $sql = "DELETE FROM missoes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            redirecionarComMensagem('editar_missoes.php', 'success', "Missão '{$missao['nome']}' removida com sucesso!");
        } else {
            redirecionarComMensagem('editar_missoes.php', 'error', 'Missão não encontrada.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao remover missão: ' . $e->getMessage());
    }
}

// Recupera todas as missões
$sql = "SELECT * FROM missoes ORDER BY id DESC";
$stmt = $pdo->query($sql);
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera todas as turmas para o formulário
$sql_turmas = "SELECT id, nome FROM turmas ORDER BY nome ASC";
$stmt_turmas = $pdo->query($sql_turmas);
$turmas = $stmt_turmas->fetchAll(PDO::FETCH_ASSOC);
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
                  <i class="fa fa-tasks text-primary"></i> Gerenciar Missões
                  <span class="badge badge-primary ml-2"><?= count($missoes); ?></span>
                </h4>
                <div class="card-tools">
                  <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addMissaoModal">
                    <i class="fa fa-plus"></i> Adicionar Missão
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaMissoes">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaMissoes')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaMissoes')">
                          <i class="fa fa-tasks"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaMissoes')">
                          <i class="fa fa-star"></i> XP <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaMissoes')">
                          <i class="fa fa-coins"></i> Moedas <i class="fa fa-sort"></i>
                        </th>
                        <th>Link</th>
                        <th style="cursor: pointer;" onclick="sortTable(6, 'tabelaMissoes')">
                          <i class="fa fa-toggle-on"></i> Status <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(7, 'tabelaMissoes')">
                          <i class="fa fa-graduation-cap"></i> Turma <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($missoes as $missao): ?>
                        <tr class="missao-row" data-nome="<?= strtolower($missao['nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $missao['id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-tasks text-primary mr-2"></i>
                              <strong><?= htmlspecialchars($missao['nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="descricao-container">
                              <span class="text-muted" data-toggle="tooltip" title="<?= htmlspecialchars($missao['descricao']); ?>">
                                <?= strlen($missao['descricao']) > 80 ? substr($missao['descricao'], 0, 80) . '...' : $missao['descricao']; ?>
                              </span>
                              <button class="btn btn-link btn-sm p-0 ml-2" onclick="expandirDescricao(this)" data-descricao="<?= htmlspecialchars($missao['descricao']); ?>">
                                <i class="fa fa-expand"></i>
                              </button>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-warning">
                              <i class="fa fa-star"></i> <?= $missao['xp']; ?> XP
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-success">
                              <i class="fa fa-coins"></i> <?= $missao['moedas']; ?> moedas
                            </span>
                          </td>
                          <td>
                            <?php if (!empty($missao['link'])): ?>
                              <a href="<?= htmlspecialchars($missao['link']); ?>" target="_blank" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" title="Abrir link">
                                <i class="fa fa-external-link"></i> Link
                              </a>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($missao['status'] === 'ativa'): ?>
                              <span class="badge badge-success">Ativa</span>
                            <?php else: ?>
                              <span class="badge badge-secondary">Inativa</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if (!empty($missao['turma_id'])): ?>
                              <span class="badge badge-info">Turma <?= $missao['turma_id']; ?></span>
                            <?php else: ?>
                              <span class="badge badge-light">Todas</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="editarMissao(<?= $missao['id']; ?>, '<?= htmlspecialchars($missao['nome']); ?>', '<?= htmlspecialchars($missao['descricao']); ?>', <?= $missao['xp']; ?>, <?= $missao['moedas']; ?>, '<?= htmlspecialchars($missao['link']); ?>', '<?= $missao['status']; ?>', '<?= $missao['turma_id']; ?>')" 
                                      data-toggle="tooltip" title="Editar missão">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $missao['id']; ?>, '<?= htmlspecialchars($missao['nome']); ?>')" 
                                      data-toggle="tooltip" title="Remover missão">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($missoes)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-tasks fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhuma missão cadastrada</h5>
                      <p class="text-muted">Adicione a primeira missão usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Missão -->
      <div class="modal fade" id="addMissaoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Missão
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="nome">Nome da Missão</label>
                  <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="descricao">Descrição</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="xp">XP de Recompensa</label>
                  <input type="number" class="form-control" id="xp" name="xp" min="1" required>
                </div>
                <div class="form-group">
                  <label for="moedas">Moedas de Recompensa</label>
                  <input type="number" class="form-control" id="moedas" name="moedas" min="0" required>
                </div>
                <div class="form-group">
                  <label for="link">Link (opcional)</label>
                  <input type="url" class="form-control" id="link" name="link" placeholder="https://...">
                </div>
                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status" required>
                    <option value="ativa">Ativa</option>
                    <option value="inativa">Inativa</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="turma_id">Turma (opcional)</label>
                  <select class="form-control" id="turma_id" name="turma_id">
                    <option value="">Todas as turmas</option>
                    <?php foreach ($turmas as $turma): ?>
                      <option value="<?= $turma['id']; ?>"><?= htmlspecialchars($turma['nome']); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="form-text text-muted">Deixe em branco para que a missão apareça para todas as turmas</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="add" class="btn btn-success">
                  <i class="fa fa-plus"></i> Adicionar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Editar Missão -->
      <div class="modal fade" id="editMissaoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-info"></i> Editar Missão
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_nome">Nome da Missão</label>
                  <input type="text" class="form-control" id="edit_nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="edit_descricao">Descrição</label>
                  <textarea class="form-control" id="edit_descricao" name="descricao" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="edit_xp">XP de Recompensa</label>
                  <input type="number" class="form-control" id="edit_xp" name="xp" min="1" required>
                </div>
                <div class="form-group">
                  <label for="edit_moedas">Moedas de Recompensa</label>
                  <input type="number" class="form-control" id="edit_moedas" name="moedas" min="0" required>
                </div>
                <div class="form-group">
                  <label for="edit_link">Link (opcional)</label>
                  <input type="url" class="form-control" id="edit_link" name="link" placeholder="https://...">
                </div>
                <div class="form-group">
                  <label for="edit_status">Status</label>
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="ativa">Ativa</option>
                    <option value="inativa">Inativa</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="edit_turma_id">Turma (opcional)</label>
                  <select class="form-control" id="edit_turma_id" name="turma_id">
                    <option value="">Todas as turmas</option>
                    <?php foreach ($turmas as $turma): ?>
                      <option value="<?= $turma['id']; ?>"><?= htmlspecialchars($turma['nome']); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="form-text text-muted">Deixe em branco para que a missão apareça para todas as turmas</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="edit" class="btn btn-info">
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
    
    // Função para editar missão
    function editarMissao(id, nome, descricao, xp, moedas, link, status, turma_id) {
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_descricao').val(descricao);
      $('#edit_xp').val(xp);
      $('#edit_moedas').val(moedas);
      $('#edit_link').val(link);
      $('#edit_status').val(status || 'ativa');
      $('#edit_turma_id').val(turma_id || '');
      $('#editMissaoModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
      if (confirm('Tem certeza que deseja remover a missão "' + nome + '"?\n\nEsta ação não pode ser desfeita.')) {
        window.location.href = '?delete=' + id;
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
  </script>
  <!-- Footer com scripts mobile -->
  <?php include 'include/footer.php'; ?>
</body>

</html>