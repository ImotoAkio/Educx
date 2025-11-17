<?php
// Conexão com o banco de dados e início da sessão
session_start();
require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_atitudes';

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Verificar se a tabela atitudes existe
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'atitudes'");
    $tableExists = $checkTable->rowCount() > 0;
} catch (PDOException $e) {
    $tableExists = false;
}

// Função para adicionar uma atitude
if (isset($_POST['add'])) {
    if (!$tableExists) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Tabela de atitudes não existe. Execute o script SQL criar_sistema_extrato.sql primeiro.');
    }
    
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $valor_moedas = (int)$_POST['valor_moedas'];
    $tipo = $_POST['tipo'];
    $status = $_POST['status'] ?? 'ativa';

    try {
        $sql = "INSERT INTO atitudes (titulo, descricao, valor_moedas, tipo, status) 
                VALUES (:titulo, :descricao, :valor_moedas, :tipo, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titulo' => $titulo,
            'descricao' => $descricao,
            'valor_moedas' => $valor_moedas,
            'tipo' => $tipo,
            'status' => $status
        ]);
        
        redirecionarComMensagem('editar_atitudes.php', 'success', "Atitude '$titulo' adicionada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Erro ao adicionar atitude: ' . $e->getMessage());
    }
}

// Função para editar uma atitude
if (isset($_POST['edit'])) {
    if (!$tableExists) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Tabela de atitudes não existe. Execute o script SQL criar_sistema_extrato.sql primeiro.');
    }
    
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $valor_moedas = (int)$_POST['valor_moedas'];
    $tipo = $_POST['tipo'];
    $status = $_POST['status'] ?? 'ativa';

    try {
        $sql = "UPDATE atitudes 
                SET titulo = :titulo, descricao = :descricao, valor_moedas = :valor_moedas, tipo = :tipo, status = :status 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'valor_moedas' => $valor_moedas,
            'tipo' => $tipo,
            'status' => $status
        ]);
        
        redirecionarComMensagem('editar_atitudes.php', 'success', "Atitude '$titulo' atualizada com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Erro ao atualizar atitude: ' . $e->getMessage());
    }
}

// Função para excluir uma atitude
if (isset($_GET['delete'])) {
    if (!$tableExists) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Tabela de atitudes não existe. Execute o script SQL criar_sistema_extrato.sql primeiro.');
    }
    
    $id = $_GET['delete'];

    try {
        // Busca o título da atitude antes de deletar
        $stmt = $pdo->prepare("SELECT titulo FROM atitudes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $atitude = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($atitude) {
            $sql = "DELETE FROM atitudes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            redirecionarComMensagem('editar_atitudes.php', 'success', "Atitude '{$atitude['titulo']}' removida com sucesso!");
        } else {
            redirecionarComMensagem('editar_atitudes.php', 'error', 'Atitude não encontrada.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('editar_atitudes.php', 'error', 'Erro ao remover atitude: ' . $e->getMessage());
    }
}

// Verificar se a tabela atitudes existe
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'atitudes'");
    $tableExists = $checkTable->rowCount() > 0;
} catch (PDOException $e) {
    $tableExists = false;
}

// Recupera todas as atitudes
if ($tableExists) {
    $sql = "SELECT * FROM atitudes ORDER BY tipo, valor_moedas DESC, id DESC";
    $stmt = $pdo->query($sql);
    $atitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $atitudes = [];
}
?>
<!--
=========================================================
* Paper Dashboard 2 - v2.0.1
=========================================================
-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../../../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>Gerenciar Atitudes - Painel da Secretaria</title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <link href="../assets/css/mobile-header.css" rel="stylesheet" />
  <style>
    .navbar-toggler-bar {
      display: none;
    }
    .main-panel.d-md-none .navbar-nav .nav-link {
      color: #000 !important;
    }
    .main-panel.d-md-none .navbar-nav .nav-link:hover {
      color: #007bff !important;
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
                  <i class="fa fa-balance-scale text-primary"></i> Gerenciar Atitudes
                  <span class="badge badge-primary ml-2"><?= count($atitudes); ?></span>
                </h4>
                <div class="card-tools">
                  <?php if ($tableExists): ?>
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addAtitudeModal">
                      <i class="fa fa-plus"></i> Adicionar Atitude
                    </button>
                  <?php else: ?>
                    <div class="alert alert-warning mb-0" style="padding: 8px 15px; margin: 0;">
                      <i class="fa fa-exclamation-triangle"></i> Execute o script SQL <code>criar_sistema_extrato.sql</code> primeiro
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="card-body">
                <?php if (!$tableExists): ?>
                  <div class="alert alert-danger">
                    <h5><i class="fa fa-exclamation-triangle"></i> Tabela não encontrada</h5>
                    <p>Para usar o sistema de atitudes, você precisa executar o script SQL <code>criar_sistema_extrato.sql</code> no seu banco de dados.</p>
                    <p><strong>Localização do arquivo:</strong> <code>E:\Xampp\htdocs\Educx\criar_sistema_extrato.sql</code></p>
                  </div>
                <?php endif; ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaAtitudes">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaAtitudes')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaAtitudes')">
                          <i class="fa fa-tag"></i> Título <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaAtitudes')">
                          <i class="fa fa-coins"></i> Valor <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaAtitudes')">
                          <i class="fa fa-toggle-on"></i> Tipo <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(5, 'tabelaAtitudes')">
                          <i class="fa fa-check-circle"></i> Status <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($atitudes as $atitude): ?>
                        <tr>
                          <td>
                            <span class="badge badge-secondary"><?= $atitude['id']; ?></span>
                          </td>
                          <td>
                            <strong><?= htmlspecialchars($atitude['titulo']); ?></strong>
                          </td>
                          <td>
                            <span class="text-muted" data-toggle="tooltip" title="<?= htmlspecialchars($atitude['descricao']); ?>">
                              <?= strlen($atitude['descricao']) > 80 ? substr($atitude['descricao'], 0, 80) . '...' : $atitude['descricao']; ?>
                            </span>
                          </td>
                          <td>
                            <?php if ($atitude['tipo'] === 'ganho'): ?>
                              <span class="badge badge-success">
                                <i class="fa fa-plus"></i> +<?= $atitude['valor_moedas']; ?> moedas
                              </span>
                            <?php else: ?>
                              <span class="badge badge-danger">
                                <i class="fa fa-minus"></i> -<?= $atitude['valor_moedas']; ?> moedas
                              </span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($atitude['tipo'] === 'ganho'): ?>
                              <span class="badge badge-success">Ganho</span>
                            <?php else: ?>
                              <span class="badge badge-danger">Perda</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($atitude['status'] === 'ativa'): ?>
                              <span class="badge badge-success">Ativa</span>
                            <?php else: ?>
                              <span class="badge badge-secondary">Inativa</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="editarAtitude(<?= $atitude['id']; ?>, '<?= htmlspecialchars($atitude['titulo']); ?>', '<?= htmlspecialchars($atitude['descricao']); ?>', <?= $atitude['valor_moedas']; ?>, '<?= $atitude['tipo']; ?>', '<?= $atitude['status']; ?>')" 
                                      data-toggle="tooltip" title="Editar atitude">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $atitude['id']; ?>, '<?= htmlspecialchars($atitude['titulo']); ?>')" 
                                      data-toggle="tooltip" title="Remover atitude">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($atitudes)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-balance-scale fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhuma atitude cadastrada</h5>
                      <p class="text-muted">Adicione a primeira atitude usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Atitude -->
      <div class="modal fade" id="addAtitudeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Atitude
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="titulo">Título da Atitude <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ex: Participação em Aula">
                </div>
                <div class="form-group">
                  <label for="descricao">Descrição</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreva a atitude..."></textarea>
                </div>
                <div class="form-group">
                  <label for="valor_moedas">Valor de Moedas <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="valor_moedas" name="valor_moedas" min="1" max="1000" required>
                  <small class="form-text text-muted">Valor sempre positivo. O tipo define se é ganho ou perda.</small>
                </div>
                <div class="form-group">
                  <label for="tipo">Tipo <span class="text-danger">*</span></label>
                  <select class="form-control" id="tipo" name="tipo" required>
                    <option value="ganho">Ganho de Moedas</option>
                    <option value="perda">Perda de Moedas</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status" required>
                    <option value="ativa">Ativa</option>
                    <option value="inativa">Inativa</option>
                  </select>
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

      <!-- Modal Editar Atitude -->
      <div class="modal fade" id="editAtitudeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-info"></i> Editar Atitude
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_titulo">Título da Atitude <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_titulo" name="titulo" required>
                </div>
                <div class="form-group">
                  <label for="edit_descricao">Descrição</label>
                  <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                </div>
                <div class="form-group">
                  <label for="edit_valor_moedas">Valor de Moedas <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="edit_valor_moedas" name="valor_moedas" min="1" max="1000" required>
                </div>
                <div class="form-group">
                  <label for="edit_tipo">Tipo <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_tipo" name="tipo" required>
                    <option value="ganho">Ganho de Moedas</option>
                    <option value="perda">Perda de Moedas</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="edit_status">Status</label>
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="ativa">Ativa</option>
                    <option value="inativa">Inativa</option>
                  </select>
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

  <!--   Core JS Files   -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="../assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
  <script src="../assets/js/mobile-menu.js"></script>

  <script>
    // Função para ordenar tabela
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
    
    // Função para editar atitude
    function editarAtitude(id, titulo, descricao, valor_moedas, tipo, status) {
      $('#edit_id').val(id);
      $('#edit_titulo').val(titulo);
      $('#edit_descricao').val(descricao);
      $('#edit_valor_moedas').val(valor_moedas);
      $('#edit_tipo').val(tipo);
      $('#edit_status').val(status);
      $('#editAtitudeModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, titulo) {
      if (confirm('Tem certeza que deseja remover a atitude "' + titulo + '"?\n\nEsta ação não pode ser desfeita.')) {
        window.location.href = '?delete=' + id;
      }
    }
    
    // Inicializar tooltips
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });
  </script>
</body>
</html>

