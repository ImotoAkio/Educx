<?php
session_start();
require '../../db.php'; // Conexão com o banco

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}


try {
    // Busca o nome do professor usando o ID da sessão
    $stmt = $pdo->prepare("SELECT nome FROM professores WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($professor) {
        $professor_nome = htmlspecialchars($professor['nome']);
    } else {
        // Caso o professor não seja encontrado, redireciona para login
        header("Location: ../login.php");
        exit;
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar informações do professor: " . $e->getMessage();
    exit;
}

try {
    // Consulta para buscar todos os alunos ordenados por XP total
    $query = "SELECT id, nome, xp_total, moedas, avatar, GREATEST(FLOOR(xp_total / 1000), 1) AS nivel FROM alunos ORDER BY xp_total DESC";
    $stmt = $pdo->query($query);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}

// Inicializa variáveis
$alunos = [];
$erro = "";
$sucesso = "";
$professor_nome = htmlspecialchars($_SESSION['usuario_nome']);
$current_avatar = $alunoEncontrado['avatar'] ?? 'asset/img/default.gif';






// Processa a pesquisa do professor
if (isset($_GET['pesquisa'])) {
    $pesquisa = $_GET['pesquisa'];
    $stmt = $pdo->prepare("SELECT id, nome, xp_total, moedas, avatar FROM alunos WHERE nome LIKE :pesquisa");
    $stmt->execute([':pesquisa' => "%$pesquisa%"]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($alunos)) {
        $erro = "Nenhum aluno encontrado com o nome '$pesquisa'.";
    }
}

// Processa a adição de moedas ao aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno_id']) && isset($_POST['valor'])) {
    $aluno_id = $_POST['aluno_id'];
    $valor = $_POST['valor'];  // Valor a ser adicionado ou removido
    $descricao = $_POST['descricao'] ?? 'Ajuste manual de moedas';

    // Validação do valor
    if (!is_numeric($valor) || $valor == 0) {
        $erro = "Por favor, insira um valor válido diferente de zero.";
    } else {
        // Verifica se estamos adicionando ou removendo moedas
        if ($valor > 0) {
            // Adiciona moedas
            try {
                $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas + :valor WHERE id = :aluno_id");
                $stmt->execute([':valor' => $valor, ':aluno_id' => $aluno_id]);
                
                // Registra a transação no histórico (se houver uma tabela de histórico)
                try {
                    $stmt = $pdo->prepare("INSERT INTO historico_moedas (aluno_id, professor_id, quantidade, tipo, descricao, data) VALUES (:aluno_id, :professor_id, :quantidade, 'adicao', :descricao, NOW())");
                    $stmt->execute([
                        ':aluno_id' => $aluno_id,
                        ':professor_id' => $_SESSION['usuario_id'],
                        ':quantidade' => $valor,
                        ':descricao' => $descricao
                    ]);
                } catch (Exception $e) {
                    // Se a tabela não existir, apenas ignora o erro
                    // O importante é que as moedas foram atualizadas
                }
                
                $sucesso = "Adicionadas " . $valor . " moedas com sucesso!";
            } catch (Exception $e) {
                $erro = "Erro ao adicionar moedas: " . $e->getMessage();
            }
        } else {
            // Remove moedas (se valor for negativo)
            try {
                $stmt = $pdo->prepare("UPDATE alunos SET moedas = GREATEST(moedas + :valor, 0) WHERE id = :aluno_id");
                // Usando GREATEST para garantir que o valor não fique negativo
                $stmt->execute([':valor' => $valor, ':aluno_id' => $aluno_id]);
                
                // Registra a transação no histórico
                try {
                    $stmt = $pdo->prepare("INSERT INTO historico_moedas (aluno_id, professor_id, quantidade, tipo, descricao, data) VALUES (:aluno_id, :professor_id, :quantidade, 'remocao', :descricao, NOW())");
                    $stmt->execute([
                        ':aluno_id' => $aluno_id,
                        ':professor_id' => $_SESSION['usuario_id'],
                        ':quantidade' => abs($valor),
                        ':descricao' => $descricao
                    ]);
                } catch (Exception $e) {
                    // Se a tabela não existir, apenas ignora o erro
                    // O importante é que as moedas foram atualizadas
                }
                
                $sucesso = "Removidas " . abs($valor) . " moedas com sucesso!";
            } catch (Exception $e) {
                $erro = "Erro ao remover moedas: " . $e->getMessage();
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor</title>
    <link rel="stylesheet" href="../../asset/loja.css"> <!-- Link para o CSS -->
     <link rel="stylesheet" href="../../asset/button.css"> <!-- Link para o CSS -->

     <style>
/* From Uiverse.io by adamgiebl */ 
.botao-verde {
 position: relative;
 padding: 10px 40px;
 margin: 0px 10px 10px 0px;
 float: left;
 border-radius: 3px;
 font-size: 20px;
 color: #FFF;
 text-decoration: none;
 background-color: #2ecc71;
 border: none;
 border-bottom: 5px solid #27ae60;
 text-shadow: 0px -2px #27ae60;
 -webkit-transition: all 0.1s;
 transition: all 0.1s;
}

.botao-verde:hover, button:active {
 -webkit-transform: translate(0px,5px);
 -ms-transform: translate(0px,5px);
 transform: translate(0px,5px);
 border-bottom: 1px solid #2ecc71;
}
.botao-vermelho {
 position: relative;
 padding: 10px 40px;
 margin: 0px 10px 10px 0px;
 float: left;
 border-radius: 3px;
 font-size: 20px;
 color: #FFF;
 text-decoration: none;
 background-color: #cc552e;
 border: none;
 border-bottom: 5px solid #ae3727;
 text-shadow: 0px -2px #ae3727;
 -webkit-transition: all 0.1s;
 transition: all 0.1s;
}

.botao-vermelho:hover, button:active {
 -webkit-transform: translate(0px,5px);
 -ms-transform: translate(0px,5px);
 transform: translate(0px,5px);
 border-bottom: 1px solid #cc552e;
}

.voltar {

	margin-bottom: 15px;
  font: inherit;
  background-color: #f0f0f0;
  border: 0;
  color: #242424;
  border-radius: 0.5em;
  font-size: 1rem;
  padding: 0.375em 1em;
  font-weight: 600;
  text-shadow: 0 0.0625em 0 #fff;
  box-shadow: inset 0 0.0625em 0 0 #f4f4f4, 0 0.0625em 0 0 #efefef,
    0 0.125em 0 0 #ececec, 0 0.25em 0 0 #e0e0e0, 0 0.3125em 0 0 #dedede,
    0 0.375em 0 0 #dcdcdc, 0 0.425em 0 0 #cacaca, 0 0.425em 0.5em 0 #cecece;
  transition: 0.15s ease;
  cursor: pointer;
}
.voltar:active {
  translate: 0 0.225em;
  box-shadow: inset 0 0.03em 0 0 #f4f4f4, 0 0.03em 0 0 #efefef,
    0 0.0625em 0 0 #ececec, 0 0.125em 0 0 #e0e0e0, 0 0.125em 0 0 #dedede,
    0 0.2em 0 0 #dcdcdc, 0 0.225em 0 0 #cacaca, 0 0.225em 0.375em 0 #cecece;
}

    </style>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <!-- Cabeçalho -->
        <header>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-4-sm">
                        <a href="paginas/dashboard.php" class="voltar">Painel principal</a>
                    </div>
                    <div class="col-4-sm center">
                        <h1 class="page-title">Painel do Professor</h1>
                    </div>
                    <div class="col-4-sm">
                        <!-- Espaço vazio para manter layout -->
                    </div>
                </div>
            </div>
        </header>

        <section>
            <div class="container-fluid">
                <!-- Cartão principal -->
                <div class="row">
                    <div class="col-12">
                        <div class="hero-card">
                            <div class="content-image">
                                <img src="../../asset/img/professor.png" alt="Imagem do professor">
                            </div>
                            <div class="card-content">
                                <h3>Olá, <?= $professor_nome; ?>!</h3> <!-- Saudação -->
                                <p>Pesquisar aluno</p> <!-- Descrição -->
                                <form method="GET" action="professor_dashboard.php">
                                    <div class="content-input">
                                        <input type="text" id="pesquisa" name="pesquisa" placeholder="Pesquisar aluno" required>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Título da categoria -->
                <div class="row margin-vertical">
                    <div class="col-6-sm">
                        <h3 class="segment-title left">Lista de Alunos</h3>
                    </div>
                </div>

                <!-- Alunos encontrados -->
                <?php if (!empty($erro)): ?>
                    <div class="row">
                        <div class="col-12">
                            <p class="erro" style="color: red; background-color: #ffe6e6; padding: 10px; border-radius: 5px; border: 1px solid #ff9999;"><?= htmlspecialchars($erro) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="row">
                        <div class="col-12">
                            <p class="sucesso" style="color: green; background-color: #e6ffe6; padding: 10px; border-radius: 5px; border: 1px solid #99ff99;"><?= htmlspecialchars($sucesso) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                <?php foreach ($alunos as $aluno): ?>
<div class="col-6-sm">
    <div class="product">

    <img src="/game/<?= htmlspecialchars($aluno['avatar'] ?: 'asset/img/default.gif'); ?>" alt="Avatar do Aluno">

        <!-- Controle de Moedas Simplificado -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
            <div class="moedas-control" style="margin: 15px 0; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; border: 1px solid #dee2e6; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h5 style="margin: 0 0 15px 0; color: #495057; font-size: 16px; font-weight: 600; text-align: center;">💰 Controle de Moedas</h5>
                
                <!-- Transação Personalizada -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: center;">
                        <input type="number" name="valor" placeholder="Quantidade" min="1" max="1000" 
                               style="width: 150px; padding: 10px; border: 2px solid #ced4da; border-radius: 6px; font-size: 14px; text-align: center;" required>
                        <button type="submit" class="botao-verde" style="padding: 10px 20px; font-size: 14px; border-radius: 6px;">➕ Adicionar</button>
                        <button type="submit" class="botao-vermelho" style="padding: 10px 20px; font-size: 14px; border-radius: 6px;" onclick="document.querySelector('input[name=valor]').value = '-' + Math.abs(document.querySelector('input[name=valor]').value); return true;">➖ Remover</button>
                    </div>
                </div>

                <!-- Botões Rápidos -->
                <div style="text-align: center;">
                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 12px; font-weight: 500;">Transações Rápidas:</p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
                        <button type="button" class="botao-verde" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="adicionarMoedas(5)">+5</button>
                        <button type="button" class="botao-verde" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="adicionarMoedas(10)">+10</button>
                        <button type="button" class="botao-verde" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="adicionarMoedas(20)">+20</button>
                        <button type="button" class="botao-verde" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="adicionarMoedas(50)">+50</button>
                        <button type="button" class="botao-vermelho" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="removerMoedas(5)">-5</button>
                        <button type="button" class="botao-vermelho" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="removerMoedas(10)">-10</button>
                        <button type="button" class="botao-vermelho" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="removerMoedas(20)">-20</button>
                        <button type="button" class="botao-vermelho" style="padding: 8px 16px; font-size: 13px; border-radius: 5px;" onclick="removerMoedas(50)">-50</button>
                    </div>
                </div>
            </div>
        </form>


        
        <div class="detail">
            <h4 class="name"><?= htmlspecialchars($aluno['nome']) ?></h4>
            <div class="detail-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <div class="price left" style="font-size: 12px; color: #6c757d;">ID: <?= $aluno['id'] ?></div>
                <div class="review right" style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-weight: 600; color: #28a745;">💰 <?= $aluno['moedas'] ?> moedas</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

                </div>
            </div>
        </section>
    </div>
</body>

<script>
// Função para atualizar a página após uma transação bem-sucedida
function atualizarPagina() {
    // Remove as mensagens de sucesso após 3 segundos
    setTimeout(function() {
        const mensagens = document.querySelectorAll('.sucesso, .erro');
        mensagens.forEach(function(mensagem) {
            mensagem.style.display = 'none';
        });
    }, 3000);
}

// Funções para os botões rápidos
function adicionarMoedas(quantidade) {
    const form = event.target.closest('form');
    const valorInput = form.querySelector('input[name="valor"]');
    
    valorInput.value = quantidade;
    form.submit();
}

function removerMoedas(quantidade) {
    const form = event.target.closest('form');
    const valorInput = form.querySelector('input[name="valor"]');
    
    valorInput.value = -quantidade;
    form.submit();
}

// Executa a função quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    atualizarPagina();
    
    // Adiciona validação nos campos de quantidade
    const camposQuantidade = document.querySelectorAll('input[type="number"]');
    camposQuantidade.forEach(function(campo) {
        campo.addEventListener('input', function() {
            const valor = parseInt(this.value);
            if (valor < 1) {
                this.value = 1;
            } else if (valor > 1000) {
                this.value = 1000;
            }
        });
    });
});

</script>

<!-- Modal de Notificação do App Professor -->
<div class="modal fade" id="modalAppProfessor" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 30px;">
                <div class="text-center w-100">
                    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px;">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4 class="modal-title mb-0">📱 App Professor Disponível!</h4>
                </div>
            </div>
            <div class="modal-body" style="padding: 30px; text-align: center;">
                <p class="mb-4" style="font-size: 16px; color: #666;">
                    O aplicativo móvel do professor já está disponível! 
                    Baixe e instale para ter acesso rápido aos dados dos alunos via QR Code.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="../../instalar_app_professor.php" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 25px; border-radius: 10px; font-weight: bold;">
                        <i class="fas fa-download me-2"></i>
                        Ver Instruções
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="padding: 12px 25px; border-radius: 10px;">
                        <i class="fas fa-times me-2"></i>
                        Fechar
                    </button>
                </div>
                <p class="mt-3 mb-0" style="font-size: 12px; color: #999;">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta notificação aparecerá apenas uma vez por sessão
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar modal do app após 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, verificando modal...');
    
    // Modal aparece apenas uma vez por sessão
    
    // Verificar se já foi mostrada nesta sessão
    if (!sessionStorage.getItem('appModalShown')) {
        console.log('Modal não foi mostrada ainda, aguardando 5 segundos...');
        setTimeout(function() {
            console.log('Tentando mostrar modal...');
            
            // Testar se jQuery está disponível
            if (typeof $ !== 'undefined') {
                console.log('jQuery disponível, mostrando modal...');
                $('#modalAppProfessor').modal('show');
                
                // Marcar como mostrada APÓS mostrar
                sessionStorage.setItem('appModalShown', 'true');
                console.log('Modal marcada como mostrada');
            } else {
                console.log('jQuery não disponível, tentando método nativo...');
                // Método alternativo sem jQuery
                const modal = document.getElementById('modalAppProfessor');
                if (modal) {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    
                    // Marcar como mostrada APÓS mostrar
                    sessionStorage.setItem('appModalShown', 'true');
                    console.log('Modal marcada como mostrada');
                } else {
                    console.log('Modal não encontrada no DOM');
                }
            }
        }, 5000);
    } else {
        console.log('Modal já foi mostrada nesta sessão');
    }
});
</script>

<!-- jQuery e Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</html>
