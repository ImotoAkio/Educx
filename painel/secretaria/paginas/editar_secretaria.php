<?php
// Conexão com o banco de dados
session_start();
require '../../../db.php'; // Conexão com o banco

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_secretaria';

// Verifica se o usuário está logado como secretaria
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Adicionar secretário
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); // Criptografa a senha

    try {
        // Insere o secretário
        $sql = "INSERT INTO professores (nome, email, senha, tipo_usuario) VALUES (:nome, :email, :senha, 'secretaria')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha
        ]);
        
        redirecionarComMensagem('editar_secretaria.php', 'success', "Secretário '$nome' adicionado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_secretaria.php', 'error', 'Erro ao adicionar secretário: ' . $e->getMessage());
    }
}

// Editar secretário
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];

    try {
        // Atualiza os dados do secretário (sem alterar a senha, a menos que fornecida)
        if (!empty($_POST['senha'])) {
            $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
            $sql = "UPDATE professores SET nome = :nome, email = :email, senha = :senha WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha
            ]);
        } else {
            $sql = "UPDATE professores SET nome = :nome, email = :email WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'nome' => $nome,
                'email' => $email
            ]);
        }
        
        redirecionarComMensagem('editar_secretaria.php', 'success', "Secretário '$nome' atualizado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_secretaria.php', 'error', 'Erro ao atualizar secretário: ' . $e->getMessage());
    }
}

// Função para remover um secretário
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        // Busca o nome do secretário antes de deletar
        $stmt = $pdo->prepare("SELECT nome FROM professores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $secretario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($secretario) {
            $sql = "DELETE FROM professores WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            redirecionarComMensagem('editar_secretaria.php', 'success', "Secretário '{$secretario['nome']}' removido com sucesso!");
        } else {
            redirecionarComMensagem('editar_secretaria.php', 'error', 'Secretário não encontrado.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('editar_secretaria.php', 'error', 'Erro ao remover secretário: ' . $e->getMessage());
    }
}

// Consulta todos os secretários
$sql = "SELECT * FROM professores WHERE tipo_usuario = 'secretaria'";
$stmt = $pdo->query($sql);
$secretarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                  <i class="fa fa-users text-info"></i> Gerenciar Secretários
                  <span class="badge badge-info ml-2"><?= count($secretarios); ?></span>
                </h4>
                <div class="card-tools">
                  <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSecretarioModal">
                    <i class="fa fa-plus"></i> Adicionar Secretário
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaSecretarios">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaSecretarios')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaSecretarios')">
                          <i class="fa fa-user"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(2, 'tabelaSecretarios')">
                          <i class="fa fa-envelope"></i> Email <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($secretarios as $secretario): ?>
                        <tr class="secretario-row" data-nome="<?= strtolower($secretario['nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $secretario['id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-user text-info mr-2"></i>
                              <strong><?= htmlspecialchars($secretario['nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <span class="text-muted">
                              <i class="fa fa-envelope mr-1"></i>
                              <?= htmlspecialchars($secretario['email']); ?>
                            </span>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="editarSecretario(<?= $secretario['id']; ?>, '<?= htmlspecialchars($secretario['nome']); ?>', '<?= htmlspecialchars($secretario['email']); ?>')" 
                                      data-toggle="tooltip" title="Editar secretário">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $secretario['id']; ?>, '<?= htmlspecialchars($secretario['nome']); ?>')" 
                                      data-toggle="tooltip" title="Remover secretário">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($secretarios)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-users fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhum secretário cadastrado</h5>
                      <p class="text-muted">Adicione o primeiro secretário usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Secretário -->
      <div class="modal fade" id="addSecretarioModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Secretário
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="nome">Nome Completo</label>
                  <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                  <label for="senha">Senha</label>
                  <input type="password" class="form-control" id="senha" name="senha" required>
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

      <!-- Modal Editar Secretário -->
      <div class="modal fade" id="editSecretarioModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-info"></i> Editar Secretário
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_nome">Nome Completo</label>
                  <input type="text" class="form-control" id="edit_nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="edit_email">Email</label>
                  <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                  <label for="edit_senha">Nova Senha (deixe em branco para manter a atual)</label>
                  <input type="password" class="form-control" id="edit_senha" name="senha">
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
    
    // Função para editar secretário
    function editarSecretario(id, nome, email) {
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_email').val(email);
      $('#edit_senha').val(''); // Limpa o campo de senha
      $('#editSecretarioModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
      if (confirm('Tem certeza que deseja remover o secretário "' + nome + '"?\n\nEsta ação não pode ser desfeita.')) {
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
</body>

</html>