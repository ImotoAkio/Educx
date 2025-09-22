<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_loja';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    // Redireciona para a página de login se a secretaria não estiver logada
    header('Location: ../../../login.php');
    exit;
}

// Adicionar produto
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $moeda = $_POST['moeda'];
    $quantidade = $_POST['quantidade'];

    try {
        // Insere o produto
        $sql = "INSERT INTO produtos (nome, descricao, moeda, quantidade) VALUES (:nome, :descricao, :moeda, :quantidade)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'moeda' => $moeda,
            'quantidade' => $quantidade
        ]);
        
        redirecionarComMensagem('editar_loja.php', 'success', "Produto '$nome' adicionado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_loja.php', 'error', 'Erro ao adicionar produto: ' . $e->getMessage());
    }
}

// Editar produto
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $moeda = $_POST['moeda'];
    $quantidade = $_POST['quantidade'];

    try {
        $sql = "UPDATE produtos SET nome = :nome, descricao = :descricao, moeda = :moeda, quantidade = :quantidade WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'descricao' => $descricao,
            'moeda' => $moeda,
            'quantidade' => $quantidade
        ]);
        
        redirecionarComMensagem('editar_loja.php', 'success', "Produto '$nome' atualizado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_loja.php', 'error', 'Erro ao atualizar produto: ' . $e->getMessage());
    }
}

// Função para remover um produto
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        // Busca o nome do produto antes de deletar
        $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            $sql = "DELETE FROM produtos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            redirecionarComMensagem('editar_loja.php', 'success', "Produto '{$produto['nome']}' removido com sucesso!");
        } else {
            redirecionarComMensagem('editar_loja.php', 'error', 'Produto não encontrado.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('editar_loja.php', 'error', 'Erro ao remover produto: ' . $e->getMessage());
    }
}

// Consulta todos os produtos
$sql = "SELECT * FROM produtos";
$stmt = $pdo->query($sql);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                  <i class="fa fa-shopping-bag text-warning"></i> Gerenciar Produtos da Loja
                  <span class="badge badge-warning ml-2"><?= count($produtos); ?></span>
                </h4>
                <div class="card-tools">
                  <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addProdutoModal">
                    <i class="fa fa-plus"></i> Adicionar Produto
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaProdutos">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaProdutos')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaProdutos')">
                          <i class="fa fa-box"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaProdutos')">
                          <i class="fa fa-coins"></i> Preço <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaProdutos')">
                          <i class="fa fa-cubes"></i> Quantidade <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($produtos as $produto): ?>
                        <tr class="produto-row" data-nome="<?= strtolower($produto['nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $produto['id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-box text-warning mr-2"></i>
                              <strong><?= htmlspecialchars($produto['nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <div class="descricao-container">
                              <span class="text-muted" data-toggle="tooltip" title="<?= addslashes(htmlspecialchars($produto['descricao'])); ?>">
                                <?= strlen($produto['descricao']) > 80 ? substr(htmlspecialchars($produto['descricao']), 0, 80) . '...' : htmlspecialchars($produto['descricao']); ?>
                              </span>
                              <button class="btn btn-link btn-sm p-0 ml-2" onclick="expandirDescricao(this)" data-descricao="<?= addslashes(htmlspecialchars($produto['descricao'])); ?>">
                                <i class="fa fa-expand"></i>
                              </button>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-success">
                              <i class="fa fa-coins"></i> <?= $produto['moeda']; ?> moedas
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-info">
                              <i class="fa fa-cubes"></i> <?= isset($produto['quantidade']) ? $produto['quantidade'] : 1; ?>
                            </span>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="editarProduto(<?= $produto['id']; ?>, '<?= addslashes(htmlspecialchars($produto['nome'])); ?>', '<?= addslashes(htmlspecialchars($produto['descricao'])); ?>', <?= $produto['moeda']; ?>, <?= isset($produto['quantidade']) ? $produto['quantidade'] : 1; ?>)" 
                                      data-toggle="tooltip" title="Editar produto">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $produto['id']; ?>, '<?= addslashes(htmlspecialchars($produto['nome'])); ?>')" 
                                      data-toggle="tooltip" title="Remover produto">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($produtos)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-shopping-bag fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhum produto cadastrado</h5>
                      <p class="text-muted">Adicione o primeiro produto usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Produto -->
      <div class="modal fade" id="addProdutoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Produto
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="nome">Nome do Produto</label>
                  <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="descricao">Descrição</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="moeda">Preço (moedas)</label>
                  <input type="number" class="form-control" id="moeda" name="moeda" min="1" required>
                </div>
                <div class="form-group">
                  <label for="quantidade">Quantidade Disponível</label>
                  <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="1" required>
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

      <!-- Modal Editar Produto -->
      <div class="modal fade" id="editProdutoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-info"></i> Editar Produto
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_nome">Nome do Produto</label>
                  <input type="text" class="form-control" id="edit_nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="edit_descricao">Descrição</label>
                  <textarea class="form-control" id="edit_descricao" name="descricao" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label for="edit_moeda">Preço (moedas)</label>
                  <input type="number" class="form-control" id="edit_moeda" name="moeda" min="1" required>
                </div>
                <div class="form-group">
                  <label for="edit_quantidade">Quantidade Disponível</label>
                  <input type="number" class="form-control" id="edit_quantidade" name="quantidade" min="1" required>
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
    
    // Função para editar produto
    function editarProduto(id, nome, descricao, moeda, quantidade) {
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_descricao').val(descricao);
      $('#edit_moeda').val(moeda);
      $('#edit_quantidade').val(quantidade);
      $('#editProdutoModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
      if (confirm('Tem certeza que deseja remover o produto "' + nome + '"?\n\nEsta ação não pode ser desfeita.')) {
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