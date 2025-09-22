<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_aluno';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    // Redireciona para a página de login se a secretaria não estiver logada
    header('Location: ../../../login.php');
    exit;
}

// Variável para a URL base do QR Code
$base_url = 'http://localhost/Educx/aluno.php?id=';

// Adicionar aluno
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $moedas = $_POST['moedas'];

    try {
        // Insere o aluno sem o QR Code Link inicialmente
        $sql = "INSERT INTO alunos (nome, moedas) VALUES (:nome, :moedas)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'moedas' => $moedas
        ]);

        // Obtém o ID do aluno recém-inserido
        $id = $pdo->lastInsertId();

        // Gera o QR Code Link com base no ID e atualiza o registro
        $qr_code_link = $base_url . $id;

        $sql = "UPDATE alunos SET qr_code_link = :qr_code_link WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'qr_code_link' => $qr_code_link,
            'id' => $id
        ]);
        
        redirecionarComMensagem('editar_aluno.php', 'success', "Aluno '$nome' adicionado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_aluno.php', 'error', 'Erro ao adicionar aluno: ' . $e->getMessage());
    }
}

// Editar aluno
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $moedas = $_POST['moedas'];

    try {
        // Gera o QR Code Link novamente
        $qr_code_link = $base_url . $id;

        $sql = "UPDATE alunos SET nome = :nome, moedas = :moedas, qr_code_link = :qr_code_link WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'moedas' => $moedas,
            'qr_code_link' => $qr_code_link
        ]);
        
        redirecionarComMensagem('editar_aluno.php', 'success', "Aluno '$nome' atualizado com sucesso!");
    } catch (Exception $e) {
        redirecionarComMensagem('editar_aluno.php', 'error', 'Erro ao atualizar aluno: ' . $e->getMessage());
    }
}

// Função para remover um aluno
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        // Busca o nome do aluno antes de deletar
        $stmt = $pdo->prepare("SELECT nome FROM alunos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($aluno) {
            $sql = "DELETE FROM alunos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            redirecionarComMensagem('editar_aluno.php', 'success', "Aluno '{$aluno['nome']}' removido com sucesso!");
        } else {
            redirecionarComMensagem('editar_aluno.php', 'error', 'Aluno não encontrado.');
        }
    } catch (Exception $e) {
        redirecionarComMensagem('editar_aluno.php', 'error', 'Erro ao remover aluno: ' . $e->getMessage());
    }
}

// Consulta todos os alunos
$sql = "SELECT * FROM alunos";
$stmt = $pdo->query($sql);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar e corrigir QR code links vazios
foreach ($alunos as &$aluno) {
    if (empty($aluno['qr_code_link'])) {
        $qr_code_link = $base_url . $aluno['id'];
        $update_sql = "UPDATE alunos SET qr_code_link = :qr_code_link WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            'qr_code_link' => $qr_code_link,
            'id' => $aluno['id']
        ]);
        $aluno['qr_code_link'] = $qr_code_link;
    }
}
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
                  <i class="fa fa-users text-success"></i> Gerenciar Alunos
                  <span class="badge badge-success ml-2"><?= count($alunos); ?></span>
                </h4>
                <div class="card-tools">
                  <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addAlunoModal">
                    <i class="fa fa-plus"></i> Adicionar Aluno
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tabelaAlunos">
                    <thead class="thead-dark">
                      <tr>
                        <th style="cursor: pointer;" onclick="sortTable(0, 'tabelaAlunos')">
                          <i class="fa fa-hashtag"></i> ID <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(1, 'tabelaAlunos')">
                          <i class="fa fa-user"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(2, 'tabelaAlunos')">
                          <i class="fa fa-coins"></i> Moedas <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaAlunos')">
                          <i class="fa fa-star"></i> XP <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-qrcode"></i> QR Code
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($alunos as $aluno): ?>
                        <tr class="aluno-row" data-nome="<?= strtolower($aluno['nome']); ?>">
                          <td>
                            <span class="badge badge-secondary"><?= $aluno['id']; ?></span>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="fa fa-user text-success mr-2"></i>
                              <strong><?= htmlspecialchars($aluno['nome']); ?></strong>
                            </div>
                          </td>
                          <td>
                            <span class="badge badge-warning">
                              <i class="fa fa-coins"></i> <?= $aluno['moedas'] ?? 0; ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-info">
                              <i class="fa fa-star"></i> <?= $aluno['xp_total'] ?? 0; ?>
                            </span>
                          </td>
                          <td>
                            <button class="btn btn-info btn-sm" onclick="verQRCode('<?= htmlspecialchars($aluno['qr_code_link']); ?>', '<?= htmlspecialchars($aluno['nome']); ?>')" 
                                    data-toggle="tooltip" title="Ver QR Code">
                              <i class="fa fa-qrcode"></i>
                            </button>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <button class="btn btn-info btn-sm" onclick="editarAluno(<?= $aluno['id']; ?>, '<?= htmlspecialchars($aluno['nome']); ?>', <?= $aluno['moedas'] ?? 0; ?>)" 
                                      data-toggle="tooltip" title="Editar aluno">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $aluno['id']; ?>, '<?= htmlspecialchars($aluno['nome']); ?>')" 
                                      data-toggle="tooltip" title="Remover aluno">
                                <i class="fa fa-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php if (empty($alunos)): ?>
                    <div class="text-center py-5">
                      <i class="fa fa-users fa-4x text-muted mb-4"></i>
                      <h5 class="text-muted">Nenhum aluno cadastrado</h5>
                      <p class="text-muted">Adicione o primeiro aluno usando o botão acima.</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Aluno -->
      <div class="modal fade" id="addAlunoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-plus text-success"></i> Adicionar Aluno
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="nome">Nome</label>
                  <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="moedas">Moedas Iniciais</label>
                  <input type="number" class="form-control" id="moedas" name="moedas" value="0" min="0" required>
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

      <!-- Modal Editar Aluno -->
      <div class="modal fade" id="editAlunoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-edit text-info"></i> Editar Aluno
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                  <label for="edit_nome">Nome</label>
                  <input type="text" class="form-control" id="edit_nome" name="nome" required>
                </div>
                <div class="form-group">
                  <label for="edit_moedas">Moedas</label>
                  <input type="number" class="form-control" id="edit_moedas" name="moedas" min="0" required>
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

      <!-- Modal QR Code -->
      <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-qrcode text-info"></i> QR Code do Aluno: <span id="qr-aluno-nome"></span>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6 text-center">
                  <div id="qrcode-container" class="mb-3"></div>
                  <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Como usar:</strong> Escaneie este QR code com o aplicativo de câmera do seu celular ou qualquer app de QR code.
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="card-title">
                        <i class="fa fa-link text-primary"></i> Informações do Link
                      </h6>
                    </div>
                    <div class="card-body">
                      <p><strong>Link do QR Code:</strong></p>
                      <div class="input-group">
                        <input type="text" class="form-control" id="qr-link" readonly>
                        <div class="input-group-append">
                          <button class="btn btn-outline-secondary" type="button" onclick="copiarLink()" 
                                  data-toggle="tooltip" title="Copiar link">
                            <i class="fa fa-copy"></i>
                          </button>
                        </div>
                      </div>
                      <hr>
                      <p><strong>Funcionalidade:</strong></p>
                      <ul class="list-unstyled">
                        <li><i class="fa fa-check text-success"></i> Acesso direto ao perfil do aluno</li>
                        <li><i class="fa fa-check text-success"></i> Visualização de moedas e XP</li>
                        <li><i class="fa fa-check text-success"></i> Personalização de avatar</li>
                        <li><i class="fa fa-check text-success"></i> Histórico de atividades</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
              <button type="button" class="btn btn-primary" onclick="baixarQRCode()">
                <i class="fa fa-download"></i> Baixar QR Code
              </button>
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
  <script src="../../assets/js/qrcode-local.js"></script>
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
    
    // Função para editar aluno
    function editarAluno(id, nome, moedas) {
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_moedas').val(moedas);
      $('#editAlunoModal').modal('show');
    }
    
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
      if (confirm('Tem certeza que deseja remover o aluno "' + nome + '"?\n\nEsta ação não pode ser desfeita.')) {
        window.location.href = '?delete=' + id;
      }
    }
    
    // Função para ver QR Code
    function verQRCode(link, nome) {
      // Verificar se o link está vazio
      if (!link || link.trim() === '') {
        alert('Erro: Link do QR code não está disponível. Verifique se o aluno foi criado corretamente.');
        return;
      }
      
      // Verificar se QRCode está disponível
      if (typeof QRCode === 'undefined') {
        alert('Erro: Biblioteca QRCode não está carregada. Recarregue a página.');
        return;
      }
      
      $('#qr-aluno-nome').text(nome);
      $('#qr-link').val(link);
      $('#qrcode-container').empty();
      
      // Criar um elemento canvas para o QR code
      var canvas = document.createElement('canvas');
      $('#qrcode-container').append(canvas);
      
      // Gerar o QR code
      QRCode.toCanvas(canvas, link, {
        width: 200,
        margin: 2,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      }, function (error) {
        if (error) {
          console.error('Erro ao gerar QR Code:', error);
          $('#qrcode-container').html('<div class="alert alert-danger">Erro ao gerar QR Code</div>');
        }
      });
      
      $('#qrCodeModal').modal('show');
    }
    
    // Função para copiar link
    function copiarLink() {
      var linkInput = document.getElementById('qr-link');
      linkInput.select();
      linkInput.setSelectionRange(0, 99999); // Para dispositivos móveis
      
      try {
        document.execCommand('copy');
        showToast('success', 'Sucesso!', 'Link copiado para a área de transferência!');
      } catch (err) {
        showToast('error', 'Erro!', 'Não foi possível copiar o link automaticamente.');
      }
    }
    
    // Função para baixar QR Code
    function baixarQRCode() {
      var canvas = document.querySelector('#qrcode-container canvas');
      if (canvas) {
        var link = document.createElement('a');
        link.download = 'qr-code-aluno.png';
        link.href = canvas.toDataURL();
        link.click();
        showToast('success', 'Sucesso!', 'QR Code baixado com sucesso!');
      } else {
        showToast('error', 'Erro!', 'QR Code não encontrado.');
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