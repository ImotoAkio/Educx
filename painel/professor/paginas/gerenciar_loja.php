<?php
require '../../../db.php';
session_start();

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'gerenciar_loja';

// Verifica se o usuário é um professor logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: ../../../login.php");
    exit;
}

// Obtém o ID do professor logado
$professor_id = $_SESSION['usuario_id'];

// Processar ações
$mensagem = '';
$tipo_mensagem = '';

// Função para processar upload de imagem
function processarUploadImagem($arquivo, $produto_id = null) {
    $upload_dir = '../uploads/produtos/';
    
    // Verificar se o diretório existe
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Verificar se há arquivo
    if (!isset($arquivo['tmp_name']) || empty($arquivo['tmp_name'])) {
        return null;
    }
    
    // Verificar se o upload foi bem-sucedido
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo');
    }
    
    // Verificar tipo de arquivo
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tipo_arquivo = mime_content_type($arquivo['tmp_name']);
    
    if (!in_array($tipo_arquivo, $tipos_permitidos)) {
        throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP');
    }
    
    // Verificar tamanho (máximo 5MB)
    if ($arquivo['size'] > 5 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande. Máximo 5MB');
    }
    
    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nome_arquivo = 'produto_' . ($produto_id ? $produto_id . '_' : '') . time() . '_' . uniqid() . '.' . $extensao;
    $caminho_completo = $upload_dir . $nome_arquivo;
    
    // Mover arquivo
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        return 'painel/professor/uploads/produtos/' . $nome_arquivo;
    } else {
        throw new Exception('Erro ao salvar arquivo');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'adicionar') {
        $nome = trim($_POST['nome'] ?? '');
        $moeda = floatval($_POST['moeda'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        
        if (empty($nome) || $moeda <= 0) {
            $mensagem = 'Nome e valor em moedas são obrigatórios!';
            $tipo_mensagem = 'error';
        } else {
            try {
                // Processar upload de imagem
                $imagem = null;
                if (isset($_FILES['imagem']) && !empty($_FILES['imagem']['tmp_name'])) {
                    $imagem = processarUploadImagem($_FILES['imagem']);
                }
                
                $stmt = $pdo->prepare("INSERT INTO produtos (nome, moeda, descricao, imagem) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $moeda, $descricao, $imagem]);
                $mensagem = 'Produto adicionado com sucesso!';
                $tipo_mensagem = 'success';
            } catch (Exception $e) {
                $mensagem = 'Erro ao adicionar produto: ' . $e->getMessage();
                $tipo_mensagem = 'error';
            }
        }
    } elseif ($acao === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $moeda = floatval($_POST['moeda'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        
        if ($id <= 0 || empty($nome) || $moeda <= 0) {
            $mensagem = 'Dados inválidos!';
            $tipo_mensagem = 'error';
        } else {
            try {
                // Buscar produto atual
                $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
                $stmt->execute([$id]);
                $produto_atual = $stmt->fetch(PDO::FETCH_ASSOC);
                $imagem_atual = $produto_atual['imagem'] ?? null;
                
                // Processar upload de nova imagem
                $imagem = $imagem_atual; // Manter imagem atual por padrão
                if (isset($_FILES['imagem']) && !empty($_FILES['imagem']['tmp_name'])) {
                    $imagem = processarUploadImagem($_FILES['imagem'], $id);
                    
                    // Remover imagem antiga se existir
                    if ($imagem_atual && file_exists('../../' . $imagem_atual)) {
                        unlink('../../' . $imagem_atual);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, moeda = ?, descricao = ?, imagem = ? WHERE id = ?");
                $stmt->execute([$nome, $moeda, $descricao, $imagem, $id]);
                $mensagem = 'Produto atualizado com sucesso!';
                $tipo_mensagem = 'success';
            } catch (Exception $e) {
                $mensagem = 'Erro ao atualizar produto: ' . $e->getMessage();
                $tipo_mensagem = 'error';
            }
        }
    } elseif ($acao === 'excluir') {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            $mensagem = 'ID inválido!';
            $tipo_mensagem = 'error';
        } else {
            try {
                // Buscar e remover imagem do produto
                $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
                $stmt->execute([$id]);
                $produto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($produto && $produto['imagem'] && file_exists('../../' . $produto['imagem'])) {
                    unlink('../../' . $produto['imagem']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
                $stmt->execute([$id]);
                $mensagem = 'Produto excluído com sucesso!';
                $tipo_mensagem = 'success';
            } catch (Exception $e) {
                $mensagem = 'Erro ao excluir produto: ' . $e->getMessage();
                $tipo_mensagem = 'error';
            }
        }
    }
}

// Buscar produtos
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY nome");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'include/navbar.php';
?>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">
          <i class="nc-icon nc-shop"></i> Gerenciar Loja
        </h4>
        <p class="card-category">Adicione, edite e remova produtos da loja</p>
      </div>
      <div class="card-body">
        
        <!-- Botão Adicionar Produto -->
        <div class="row mb-3">
          <div class="col-12">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalProduto">
              <i class="nc-icon nc-simple-add"></i> Adicionar Produto
            </button>
          </div>
        </div>

        <!-- Grid de Produtos -->
        <div class="row">
          <?php if (empty($produtos)): ?>
            <div class="col-12">
              <div class="text-center py-5">
                <i class="nc-icon nc-shop fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Nenhum produto cadastrado</h4>
                <p class="text-muted">Clique em "Adicionar Produto" para começar</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($produtos as $produto): ?>
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card produto-card h-100">
                  <div class="position-relative">
                    <?php if (!empty($produto['imagem'])): ?>
                      <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                           class="card-img-top produto-imagem" 
                           alt="<?= htmlspecialchars($produto['nome']) ?>"
                           style="height: 200px; object-fit: cover;"
                           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                      <div class="produto-imagem d-flex align-items-center justify-content-center" 
                           style="height: 200px; background: #f8f9fa; display: none;">
                        <i class="nc-icon nc-image fa-3x text-muted"></i>
                      </div>
                    <?php else: ?>
                      <div class="produto-imagem d-flex align-items-center justify-content-center" 
                           style="height: 200px; background: #f8f9fa;">
                        <i class="nc-icon nc-image fa-3x text-muted"></i>
                      </div>
                    <?php endif; ?>
                    <span class="position-absolute top-0 end-0 m-2">
                      <span class="badge badge-warning">
                        <i class="fa fa-coins"></i> <?= number_format($produto['moeda'], 0, ',', '.') ?>
                      </span>
                    </span>
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                    <p class="card-text text-muted flex-grow-1">
                      <?= htmlspecialchars($produto['descricao'] ?: 'Sem descrição') ?>
                    </p>
                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-info btn-sm flex-fill" 
                              onclick="editarProduto(<?= htmlspecialchars(json_encode($produto)) ?>)">
                        <i class="fa fa-edit"></i> Editar
                      </button>
                      <button type="button" class="btn btn-danger btn-sm flex-fill" 
                              onclick="excluirProduto(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome']) ?>')">
                        <i class="fa fa-trash"></i> Excluir
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Adicionar/Editar Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitulo">
          <i class="nc-icon nc-simple-add"></i> Adicionar Produto
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" id="formProduto" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="acao" id="acao" value="adicionar">
          <input type="hidden" name="id" id="produto_id">
          
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label for="nome" class="form-label">
                  <i class="fa fa-tag"></i> Nome do Produto *
                </label>
                <input type="text" class="form-control" id="nome" name="nome" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="moeda" class="form-label">
                  <i class="fa fa-coins"></i> Valor (Moedas) *
                </label>
                <input type="number" class="form-control" id="moeda" name="moeda" 
                       min="1" step="0.01" required>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="descricao" class="form-label">
              <i class="fa fa-align-left"></i> Descrição
            </label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                      placeholder="Descreva o produto..."></textarea>
          </div>
          
          <div class="form-group">
            <label for="imagem" class="form-label">
              <i class="fa fa-image"></i> Imagem do Produto
            </label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="imagem" name="imagem" 
                     accept="image/*" onchange="previewImagem(this)">
              <label class="custom-file-label" for="imagem" id="imagemLabel">
                <i class="fa fa-upload"></i> Selecionar arquivo...
              </label>
            </div>
            <small class="form-text text-muted">
              <i class="fa fa-info-circle"></i> 
              Formatos aceitos: JPG, PNG, GIF, WebP (máximo 5MB)
            </small>
          </div>
          
          <div id="previewImagem" class="text-center" style="display: none;">
            <img id="imgPreview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fa fa-exclamation-triangle"></i> Confirmar Exclusão
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir o produto <strong id="nomeProdutoExcluir"></strong>?</p>
        <p class="text-muted">Esta ação não pode ser desfeita.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fa fa-times"></i> Cancelar
        </button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="acao" value="excluir">
          <input type="hidden" name="id" id="idProdutoExcluir">
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash"></i> Excluir
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
.produto-card {
  transition: transform 0.2s;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  overflow: hidden;
}
.produto-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Estilo para campo de upload personalizado */
.custom-file {
  position: relative;
  display: inline-block;
  width: 100%;
  height: calc(1.5em + 0.75rem + 2px);
}

.custom-file-input {
  position: relative;
  z-index: 2;
  width: 100%;
  height: calc(1.5em + 0.75rem + 2px);
  margin: 0;
  opacity: 0;
}

.custom-file-label {
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  z-index: 1;
  height: calc(1.5em + 0.75rem + 2px);
  padding: 0.375rem 0.75rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.15s ease-in-out;
}

.custom-file-label:hover {
  background-color: #f8f9fa;
  border-color: #adb5bd;
}

.custom-file-label::after {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 3;
  display: block;
  height: calc(1.5em + 0.75rem);
  padding: 0.375rem 0.75rem;
  line-height: 1.5;
  color: #495057;
  content: "Procurar";
  background-color: #e9ecef;
  border-left: inherit;
  border-radius: 0 0.25rem 0.25rem 0;
  cursor: pointer;
}
</style>

<script>
function editarProduto(produto) {
  document.getElementById('modalTitulo').innerHTML = '<i class="nc-icon nc-settings-gear-65"></i> Editar Produto';
  document.getElementById('acao').value = 'editar';
  document.getElementById('produto_id').value = produto.id;
  document.getElementById('nome').value = produto.nome;
  document.getElementById('moeda').value = produto.moeda;
  document.getElementById('descricao').value = produto.descricao || '';
  
  // Limpar campo de arquivo
  document.getElementById('imagem').value = '';
  document.getElementById('imagemLabel').innerHTML = '<i class="fa fa-upload"></i> Selecionar arquivo...';
  
  // Mostrar preview da imagem atual se existir
  if (produto.imagem) {
    document.getElementById('imgPreview').src = produto.imagem;
    document.getElementById('previewImagem').style.display = 'block';
    document.getElementById('previewImagem').innerHTML = 
      '<img id="imgPreview" src="' + produto.imagem + '" alt="Preview" class="img-thumbnail" style="max-width: 200px;">' +
      '<p class="text-muted mt-2"><i class="fa fa-info-circle"></i> Imagem atual</p>';
  } else {
    document.getElementById('previewImagem').style.display = 'none';
  }
  
  $('#modalProduto').modal('show');
}

function excluirProduto(id, nome) {
  document.getElementById('idProdutoExcluir').value = id;
  document.getElementById('nomeProdutoExcluir').textContent = nome;
  $('#modalExcluir').modal('show');
}

// Função para preview da imagem
function previewImagem(input) {
  const preview = document.getElementById('previewImagem');
  const label = document.getElementById('imagemLabel');
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    
    // Atualizar label
    label.innerHTML = '<i class="fa fa-check"></i> ' + file.name;
    
    // Verificar tamanho do arquivo
    if (file.size > 5 * 1024 * 1024) {
      alert('Arquivo muito grande! Máximo 5MB.');
      input.value = '';
      label.innerHTML = '<i class="fa fa-upload"></i> Selecionar arquivo...';
      preview.style.display = 'none';
      return;
    }
    
    // Verificar tipo do arquivo
    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!tiposPermitidos.includes(file.type)) {
      alert('Tipo de arquivo não permitido! Use JPG, PNG, GIF ou WebP.');
      input.value = '';
      label.innerHTML = '<i class="fa fa-upload"></i> Selecionar arquivo...';
      preview.style.display = 'none';
      return;
    }
    
    // Criar preview
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = 
        '<img id="imgPreview" src="' + e.target.result + '" alt="Preview" class="img-thumbnail" style="max-width: 200px;">' +
        '<p class="text-muted mt-2"><i class="fa fa-info-circle"></i> Preview da nova imagem</p>';
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = 'none';
    label.innerHTML = '<i class="fa fa-upload"></i> Selecionar arquivo...';
  }
}

// Limpar formulário ao fechar modal
$('#modalProduto').on('hidden.bs.modal', function() {
  document.getElementById('formProduto').reset();
  document.getElementById('modalTitulo').innerHTML = '<i class="nc-icon nc-simple-add"></i> Adicionar Produto';
  document.getElementById('acao').value = 'adicionar';
  document.getElementById('previewImagem').style.display = 'none';
  document.getElementById('imagemLabel').innerHTML = '<i class="fa fa-upload"></i> Selecionar arquivo...';
});

// Mostrar mensagem de feedback se existir
<?php if (!empty($mensagem)): ?>
  showFeedback('<?= $tipo_mensagem ?>', '<?= addslashes($mensagem) ?>');
<?php endif; ?>
</script>

<?php include 'include/footer.php'; ?>