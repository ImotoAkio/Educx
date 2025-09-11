<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'editar_missoes';

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
  // Redireciona para a página de login se o professor não estiver logado
  header('Location: ../../../login.php');
  exit;
}

// Obtém o ID do professor logado
$professor_id = $_SESSION['usuario_id'];



// Função para adicionar uma missão
if (isset($_POST['add'])) {
  $nome = $_POST['nome'];
  $descricao = $_POST['descricao'];
  $xp = $_POST['xp'];
  $moedas = $_POST['moedas'];
  $link = $_POST['link'];
  $criador_id = $_SESSION['usuario_id'];
  $turma_id = $_POST['turma_id']; // Turma selecionada pelo professor

  try {
    $sql = "INSERT INTO missoes (nome, descricao, xp, moedas, link, criador_id, turma_id) 
            VALUES (:nome, :descricao, :xp, :moedas, :link, :criador_id, :turma_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'descricao' => $descricao,
        'xp' => $xp,
        'moedas' => $moedas,
        'link' => $link,
        'criador_id' => $criador_id,
        'turma_id' => $turma_id
    ]);
    
    redirecionarComMensagem('editar_missoes.php', 'success', "Missão '$nome' criada com sucesso!");
  } catch (Exception $e) {
    redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao criar missão: ' . $e->getMessage());
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
  $turma_id = $_POST['turma_id']; // Nova turma selecionada pelo professor

  $professor_id = $_SESSION['usuario_id'];

  try {
    $sql = "UPDATE missoes 
            SET nome = :nome, descricao = :descricao, xp = :xp, moedas = :moedas, link = :link, turma_id = :turma_id 
            WHERE id = :id AND criador_id = :criador_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $id,
        'nome' => $nome,
        'descricao' => $descricao,
        'xp' => $xp,
        'moedas' => $moedas,
        'link' => $link,
        'turma_id' => $turma_id,
        'criador_id' => $professor_id
    ]);

    if ($stmt->rowCount() > 0) {
      redirecionarComMensagem('editar_missoes.php', 'success', "Missão '$nome' atualizada com sucesso!");
    } else {
      redirecionarComMensagem('editar_missoes.php', 'error', 'Você não tem permissão para editar esta missão.');
    }
  } catch (Exception $e) {
    redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao editar missão: ' . $e->getMessage());
  }
}

// Função para excluir uma missão
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];

  // Verifica se a missão pertence ao professor logado
  $professor_id = $_SESSION['usuario_id'];
  
  try {
    // Busca o nome da missão antes de deletar
    $stmt = $pdo->prepare("SELECT nome FROM missoes WHERE id = :id AND criador_id = :criador_id");
    $stmt->execute(['id' => $id, 'criador_id' => $professor_id]);
    $missao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($missao) {
      $sql = "DELETE FROM missoes WHERE id = :id AND criador_id = :criador_id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(['id' => $id, 'criador_id' => $professor_id]);

      if ($stmt->rowCount() > 0) {
        redirecionarComMensagem('editar_missoes.php', 'success', "Missão '{$missao['nome']}' excluída com sucesso!");
      } else {
        redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao excluir missão.');
      }
    } else {
      redirecionarComMensagem('editar_missoes.php', 'error', 'Você não tem permissão para excluir esta missão.');
    }
  } catch (Exception $e) {
    redirecionarComMensagem('editar_missoes.php', 'error', 'Erro ao excluir missão: ' . $e->getMessage());
  }
}


// Recupera apenas as missões criadas pelo professor logado
$professor_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM missoes WHERE criador_id = :criador_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['criador_id' => $professor_id]);
$missoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    color: #000 !important; /* Define a cor do texto como preta */
  }

  .main-panel.d-md-none .navbar-nav .nav-link:hover {
    color: #007bff !important; /* Cor de hover azul */
  }
  <style>
    form .form-control {
        width: 100% !important; /* Garante que os inputs ocupem toda a largura */
    }
</style>

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
          
        </div>
 <!-- Adicionar Missão -->
 <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="fa fa-plus-circle text-success"></i> Adicionar Nova Missão
              </h5>
            </div>
            <div class="card-body">
              
                <form method="POST" id="formNovaMissao">
                <input type="hidden" name="id" value="<?= htmlspecialchars($missao['id'] ?? ''); ?>">
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="turma_id" class="form-label">
                        <i class="fa fa-users"></i> Turma <span class="text-danger">*</span>
                      </label>
                      <select class="form-control" name="turma_id" required>
                        <option value="">Selecione uma turma</option>
                        <?php
                        $professor_id = $_SESSION['usuario_id'];
                        $sql = "SELECT t.id, t.nome 
                                FROM turmas_professores tp 
                                JOIN turmas t ON tp.turma_id = t.id 
                                WHERE tp.professor_id = :professor_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['professor_id' => $professor_id]);
                        $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($turmas as $turma): ?>
                            <option value="<?= $turma['id']; ?>" <?= isset($missao) && $missao['turma_id'] == $turma['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($turma['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nome" class="form-label">
                        <i class="fa fa-star"></i> Nome da Missão <span class="text-danger">*</span>
                      </label>
                      <input type="text" class="form-control" id="nome" name="nome" required 
                             placeholder="Ex: Resolver exercícios de matemática">
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="descricao" class="form-label">
                    <i class="fa fa-align-left"></i> Descrição <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3" required 
                            placeholder="Descreva detalhadamente o que o aluno deve fazer para completar esta missão..."></textarea>
                  <small class="form-text text-muted">
                    <i class="fa fa-info-circle"></i> Seja específico sobre os requisitos da missão
                  </small>
                </div>
                
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="xp" class="form-label">
                        <i class="fa fa-star"></i> XP <span class="text-danger">*</span>
                      </label>
                      <input type="number" class="form-control" id="xp" name="xp" required min="1" max="1000"
                             placeholder="Ex: 50">
                      <small class="form-text text-muted">Pontos de experiência</small>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="moedas" class="form-label">
                        <i class="fa fa-coins"></i> Moedas <span class="text-danger">*</span>
                      </label>
                      <input type="number" class="form-control" id="moedas" name="moedas" required min="1" max="1000"
                             placeholder="Ex: 25">
                      <small class="form-text text-muted">Moedas como recompensa</small>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="link" class="form-label">
                        <i class="fa fa-link"></i> Link (Opcional)
                      </label>
                      <input type="url" class="form-control" id="link" name="link" 
                             placeholder="https://exemplo.com">
                      <small class="form-text text-muted">Link para material ou atividade</small>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <button type="submit" name="add" class="btn btn-success btn-lg">
                    <i class="fa fa-plus-circle"></i> Criar Missão
                  </button>
                  <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="limparFormulario()">
                    <i class="fa fa-eraser"></i> Limpar
                  </button>
                </div>
                </form>
            </div>
        </div>


    <!-- Listar Missões -->
    <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="fa fa-list text-primary"></i> Missões Existentes
            <span class="badge badge-primary ml-2"><?= count($missoes); ?></span>
          </h5>
          <div class="card-tools">
            <div class="row">
              <div class="col-md-6">
                <input type="text" class="form-control form-control-sm" id="searchMissoes" placeholder="Buscar missões..." style="width: 250px;">
              </div>
              <div class="col-md-6">
                <select class="form-control form-control-sm" id="filtroTurma" style="width: 200px;">
                  <option value="">Todas as turmas</option>
                  <?php
                  $professor_id = $_SESSION['usuario_id'];
                  $sql = "SELECT t.id, t.nome 
                          FROM turmas_professores tp 
                          JOIN turmas t ON tp.turma_id = t.id 
                          WHERE tp.professor_id = :professor_id";
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute(['professor_id' => $professor_id]);
                  $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($turmas as $turma): ?>
                      <option value="<?= $turma['id']; ?>">
                          <?= htmlspecialchars($turma['nome']); ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
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
                          <i class="fa fa-star"></i> Nome <i class="fa fa-sort"></i>
                        </th>
                        <th>Descrição</th>
                        <th style="cursor: pointer;" onclick="sortTable(3, 'tabelaMissoes')">
                          <i class="fa fa-star"></i> XP <i class="fa fa-sort"></i>
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(4, 'tabelaMissoes')">
                          <i class="fa fa-coins"></i> Moedas <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-link"></i> Link
                        </th>
                        <th style="cursor: pointer;" onclick="sortTable(6, 'tabelaMissoes')">
                          <i class="fa fa-users"></i> Turma <i class="fa fa-sort"></i>
                        </th>
                        <th>
                          <i class="fa fa-cogs"></i> Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($missoes as $missao): ?>
        <tr class="missao-row" data-nome="<?= strtolower($missao['nome']); ?>" data-turma="<?= $missao['turma_id'] ?? ''; ?>">
            <td>
              <span class="badge badge-secondary"><?= $missao['id']; ?></span>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <i class="fa fa-star text-warning mr-2"></i>
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
              <span class="badge badge-success">
                <i class="fa fa-star"></i> <?= $missao['xp']; ?>
              </span>
            </td>
            <td>
              <span class="badge badge-warning">
                <i class="fa fa-coins"></i> <?= $missao['moedas']; ?>
              </span>
            </td>
            <td>
              <?php if (!empty($missao['link'])): ?>
                <a href="<?= htmlspecialchars($missao['link']); ?>" target="_blank" class="btn btn-outline-info btn-sm" 
                   data-toggle="tooltip" title="Abrir link">
                  <i class="fa fa-external-link"></i>
                </a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php 
              // Buscar nome da turma
              if ($missao['turma_id']) {
                $stmt_turma = $pdo->prepare("SELECT nome FROM turmas WHERE id = :turma_id");
                $stmt_turma->execute(['turma_id' => $missao['turma_id']]);
                $turma_nome = $stmt_turma->fetch(PDO::FETCH_ASSOC);
                if ($turma_nome) {
                  echo '<span class="badge badge-primary"><i class="fa fa-users"></i> ' . htmlspecialchars($turma_nome['nome']) . '</span>';
                } else {
                  echo '<span class="text-muted">Turma não encontrada</span>';
                }
              } else {
                echo '<span class="badge badge-secondary">Todas as turmas</span>';
              }
              ?>
            </td>
            <td>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-info btn-sm" onclick="editarMissao(<?= $missao['id']; ?>)" 
                        data-toggle="tooltip" title="Editar missão">
                  <i class="fa fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $missao['id']; ?>, '<?= htmlspecialchars($missao['nome']); ?>')" 
                        data-toggle="tooltip" title="Excluir missão">
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
                  <i class="fa fa-inbox fa-4x text-muted mb-4"></i>
                  <h5 class="text-muted">Nenhuma missão encontrada</h5>
                  <p class="text-muted">Crie sua primeira missão usando o formulário acima!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modalEditarMissao" tabindex="-1" role="dialog" aria-labelledby="modalEditarMissaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEditarMissaoLabel">
          <i class="fa fa-edit"></i> Editar Missão
        </h5>
        <button type="button" class="close text-white" onclick="fecharModalEdicao()" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" id="formEditarMissao">
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_turma_id" class="form-label">
                  <i class="fa fa-users"></i> Turma <span class="text-danger">*</span>
                </label>
                <select class="form-control" name="turma_id" id="edit_turma_id" required>
                  <option value="">Selecione uma turma</option>
                  <?php
                  $professor_id = $_SESSION['usuario_id'];
                  $sql = "SELECT t.id, t.nome 
                          FROM turmas_professores tp 
                          JOIN turmas t ON tp.turma_id = t.id 
                          WHERE tp.professor_id = :professor_id";
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute(['professor_id' => $professor_id]);
                  $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($turmas as $turma): ?>
                      <option value="<?= $turma['id']; ?>">
                          <?= htmlspecialchars($turma['nome']); ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_nome" class="form-label">
                  <i class="fa fa-star"></i> Nome da Missão <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_nome" name="nome" required>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="edit_descricao" class="form-label">
              <i class="fa fa-align-left"></i> Descrição <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="edit_descricao" name="descricao" rows="3" required></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_xp" class="form-label">
                  <i class="fa fa-star"></i> XP <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="edit_xp" name="xp" required min="1" max="1000">
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_moedas" class="form-label">
                  <i class="fa fa-coins"></i> Moedas <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="edit_moedas" name="moedas" required min="1" max="1000">
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_link" class="form-label">
                  <i class="fa fa-link"></i> Link (Opcional)
                </label>
                <input type="url" class="form-control" id="edit_link" name="link">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalEdicao()">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" name="edit" class="btn btn-success">
            <i class="fa fa-save"></i> Salvar Alterações
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
      $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Search functionality
        $("#searchMissoes").on("keyup", function() {
          filtrarMissoes();
        });
        
        // Filter by turma
        $("#filtroTurma").on("change", function() {
          filtrarMissoes();
        });
        
        // Função para filtrar missões
        function filtrarMissoes() {
          var searchValue = $("#searchMissoes").val().toLowerCase();
          var turmaValue = $("#filtroTurma").val();
          
          $(".missao-row").filter(function() {
            var nome = $(this).data('nome');
            var turma = $(this).data('turma');
            
            var nomeMatch = nome.indexOf(searchValue) > -1;
            var turmaMatch = turmaValue === '' || turma === turmaValue;
            
            $(this).toggle(nomeMatch && turmaMatch);
          });
        }
        
        // Auto-hide alerts
        setTimeout(function() {
          $('.alert').fadeOut('slow');
        }, 5000);
        
        // Fechar modal com tecla ESC
        $(document).keyup(function(e) {
          if (e.keyCode === 27) { // ESC key
            fecharModalEdicao();
          }
        });
        
        // Fechar modal ao clicar fora dela
        $('#modalEditarMissao').on('click', function(e) {
          if (e.target === this) {
            fecharModalEdicao();
          }
        });
        
        // Form validation - Nova Missão
        $("#formNovaMissao").on("submit", function(e) {
          var nome = $("#nome").val().trim();
          var descricao = $("#descricao").val().trim();
          var xp = $("#xp").val();
          var moedas = $("#moedas").val();
          var turma = $("select[name='turma_id']").val();
          
          if (!nome || !descricao || !xp || !moedas || !turma) {
            e.preventDefault();
            showToast('error', 'Erro', 'Por favor, preencha todos os campos obrigatórios.');
            return false;
          }
          
          if (xp < 1 || xp > 1000) {
            e.preventDefault();
            showToast('error', 'Erro', 'XP deve estar entre 1 e 1000.');
            return false;
          }
          
          if (moedas < 1 || moedas > 1000) {
            e.preventDefault();
            showToast('error', 'Erro', 'Moedas devem estar entre 1 e 1000.');
            return false;
          }
        });
        
        // Form validation - Editar Missão
        $("#formEditarMissao").on("submit", function(e) {
          var nome = $("#edit_nome").val().trim();
          var descricao = $("#edit_descricao").val().trim();
          var xp = $("#edit_xp").val();
          var moedas = $("#edit_moedas").val();
          var turma = $("#edit_turma_id").val();
          
          if (!nome || !descricao || !xp || !moedas || !turma) {
            e.preventDefault();
            showToast('error', 'Erro', 'Por favor, preencha todos os campos obrigatórios.');
            return false;
          }
          
          if (xp < 1 || xp > 1000) {
            e.preventDefault();
            showToast('error', 'Erro', 'XP deve estar entre 1 e 1000.');
            return false;
          }
          
          if (moedas < 1 || moedas > 1000) {
            e.preventDefault();
            showToast('error', 'Erro', 'Moedas devem estar entre 1 e 1000.');
            return false;
          }
        });
      });
      
      // Função para limpar formulário
      function limparFormulario() {
        $("#formNovaMissao")[0].reset();
        showToast('info', 'Informação', 'Formulário limpo com sucesso.');
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
          span.text(descricao.length > 80 ? descricao.substring(0, 80) + '...' : descricao);
          $(button).find('i').removeClass('fa-compress').addClass('fa-expand');
        }
      }
      
      // Função para editar missão
      function editarMissao(id) {
        // Buscar dados da missão
        $.ajax({
          url: 'buscar_missao.php',
          method: 'GET',
          data: { id: id },
          dataType: 'json',
          success: function(data) {
            if (data.success) {
              // Preencher o modal com os dados da missão
              $('#edit_id').val(data.missao.id);
              $('#edit_nome').val(data.missao.nome);
              $('#edit_descricao').val(data.missao.descricao);
              $('#edit_xp').val(data.missao.xp);
              $('#edit_moedas').val(data.missao.moedas);
              $('#edit_link').val(data.missao.link);
              $('#edit_turma_id').val(data.missao.turma_id);
              
              // Abrir o modal
              $('#modalEditarMissao').modal('show');
              
              // Garantir que os botões de fechar funcionem
              $('#modalEditarMissao .close, #modalEditarMissao [data-bs-dismiss="modal"]').off('click').on('click', function() {
                $('#modalEditarMissao').modal('hide');
              });
            } else {
              showToast('error', 'Erro', 'Erro ao carregar dados da missão: ' + data.message);
            }
          },
          error: function() {
            showToast('error', 'Erro', 'Erro ao carregar dados da missão.');
          }
        });
      }
      
      // Função para confirmar exclusão
      function confirmarExclusao(id, nome) {
        if (confirm('Tem certeza que deseja excluir a missão "' + nome + '"?\n\nEsta ação não pode ser desfeita.')) {
          window.location.href = '?delete=' + id;
        }
      }
      
      // Função para fechar modal (fallback)
      function fecharModalEdicao() {
        $('#modalEditarMissao').modal('hide');
        // Fallback caso o modal não feche
        setTimeout(function() {
          if ($('#modalEditarMissao').hasClass('show')) {
            $('#modalEditarMissao').removeClass('show').css('display', 'none');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
          }
        }, 100);
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
        </div>
      </div>
      <footer class="footer footer-black  footer-white ">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <ul>
                <li><a href="https://www.creative-tim.com" target="_blank">Creative Tim</a></li>
                <li><a href="https://www.creative-tim.com/blog" target="_blank">Blog</a></li>
                <li><a href="https://www.creative-tim.com/license" target="_blank">Licenses</a></li>
              </ul>
            </nav>
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
</body>

</html>