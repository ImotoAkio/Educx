<?php
/**
 * Página para gerenciar vínculos de alunos às turmas
 * Localização: painel/secretaria/paginas/gerenciar_alunos_turmas.php
 */

session_start();
require '../../../db.php';
require 'include/feedback.php';

$pagina_ativa = 'gerenciar_alunos_turmas';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Processar ações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'vincular_aluno':
                $aluno_id = (int)$_POST['aluno_id'];
                $turma_id = (int)$_POST['turma_id'];
                
                // Verificar se já existe vínculo
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM alunos_turmas WHERE aluno_id = :aluno_id AND turma_id = :turma_id");
                $stmt->execute([':aluno_id' => $aluno_id, ':turma_id' => $turma_id]);
                $existe = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existe['total'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Aluno já está vinculado a esta turma.']);
                    exit;
                }
                
                // Criar vínculo
                $stmt = $pdo->prepare("INSERT INTO alunos_turmas (aluno_id, turma_id) VALUES (:aluno_id, :turma_id)");
                $stmt->execute([':aluno_id' => $aluno_id, ':turma_id' => $turma_id]);
                
                echo json_encode(['success' => true, 'message' => 'Aluno vinculado à turma com sucesso!']);
                break;
                
            case 'desvincular_aluno':
                $aluno_id = (int)$_POST['aluno_id'];
                $turma_id = (int)$_POST['turma_id'];
                
                $stmt = $pdo->prepare("DELETE FROM alunos_turmas WHERE aluno_id = :aluno_id AND turma_id = :turma_id");
                $stmt->execute([':aluno_id' => $aluno_id, ':turma_id' => $turma_id]);
                
                echo json_encode(['success' => true, 'message' => 'Aluno desvinculado da turma com sucesso!']);
                break;
                
            case 'buscar_alunos_turma':
                $turma_id = (int)$_POST['turma_id'];
                
                // Buscar alunos da turma
                $stmt = $pdo->prepare("
                    SELECT a.id, a.nome, a.moedas, a.xp_total
                    FROM alunos a
                    JOIN alunos_turmas at ON a.id = at.aluno_id
                    WHERE at.turma_id = :turma_id
                    ORDER BY a.nome ASC
                ");
                $stmt->execute([':turma_id' => $turma_id]);
                $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'alunos' => $alunos]);
                break;
                
            case 'buscar_alunos_disponiveis':
                $turma_id = (int)$_POST['turma_id'];
                
                // Buscar alunos que NÃO estão na turma
                $stmt = $pdo->prepare("
                    SELECT a.id, a.nome, a.moedas, a.xp_total
                    FROM alunos a
                    WHERE a.id NOT IN (
                        SELECT aluno_id FROM alunos_turmas WHERE turma_id = :turma_id
                    )
                    ORDER BY a.nome ASC
                ");
                $stmt->execute([':turma_id' => $turma_id]);
                $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'alunos' => $alunos]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}

// Buscar todas as turmas
$stmt = $pdo->query("SELECT id, nome, descricao, ano_letivo FROM turmas ORDER BY nome ASC");
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os alunos
$stmt = $pdo->query("SELECT id, nome, moedas, xp_total FROM alunos ORDER BY nome ASC");
$todos_alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Alunos e Turmas</title>
    
    <!-- CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/paper-dashboard.min.css" rel="stylesheet">
    <link href="../assets/css/demo.css" rel="stylesheet">
    <link href="../assets/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        .aluno-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .aluno-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .aluno-card.vinculado {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .aluno-card.disponivel {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-vincular {
            background: #28a745;
            border-color: #28a745;
        }
        .btn-desvincular {
            background: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>

<body>
    <?php include 'include/navbar.php'; ?>
    
    <div class="content">
        <?php exibirMensagemSessao(); ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-users text-primary"></i> Gerenciar Alunos e Turmas
                        </h4>
                        <p class="card-category">Vincule alunos às turmas e gerencie os relacionamentos</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Seleção de Turma -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="selectTurma">Selecione uma Turma:</label>
                                <select id="selectTurma" class="form-control">
                                    <option value="">-- Selecione uma turma --</option>
                                    <?php foreach ($turmas as $turma): ?>
                                        <option value="<?= $turma['id']; ?>" 
                                                data-nome="<?= htmlspecialchars($turma['nome']); ?>"
                                                data-descricao="<?= htmlspecialchars($turma['descricao']); ?>"
                                                data-ano="<?= $turma['ano_letivo']; ?>">
                                            <?= htmlspecialchars($turma['nome']); ?> (<?= $turma['ano_letivo']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card" id="statsCard" style="display: none;">
                                    <h5 id="statsTitle">Estatísticas da Turma</h5>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h3 id="totalAlunos">0</h3>
                                            <small>Alunos</small>
                                        </div>
                                        <div class="col-4">
                                            <h3 id="totalDisponiveis">0</h3>
                                            <small>Disponíveis</small>
                                        </div>
                                        <div class="col-4">
                                            <h3 id="totalGeral"><?= count($todos_alunos); ?></h3>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações da Turma Selecionada -->
                        <div id="turmaInfo" class="alert alert-info" style="display: none;">
                            <h5 id="turmaNome"></h5>
                            <p id="turmaDescricao"></p>
                            <small id="turmaAno"></small>
                        </div>
                        
                        <!-- Abas -->
                        <ul class="nav nav-tabs" id="alunosTabs" style="display: none;">
                            <li class="nav-item">
                                <a class="nav-link active" id="vinculados-tab" data-toggle="tab" href="#vinculados" role="tab">
                                    <i class="fa fa-check-circle text-success"></i> Alunos Vinculados
                                    <span class="badge badge-success" id="badgeVinculados">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="disponiveis-tab" data-toggle="tab" href="#disponiveis" role="tab">
                                    <i class="fa fa-plus-circle text-primary"></i> Alunos Disponíveis
                                    <span class="badge badge-primary" id="badgeDisponiveis">0</span>
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Conteúdo das Abas -->
                        <div class="tab-content" id="alunosTabContent" style="display: none;">
                            <!-- Alunos Vinculados -->
                            <div class="tab-pane fade show active" id="vinculados" role="tabpanel">
                                <div class="row" id="alunosVinculados">
                                    <div class="col-12 text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Carregando alunos vinculados...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Alunos Disponíveis -->
                            <div class="tab-pane fade" id="disponiveis" role="tabpanel">
                                <div class="row" id="alunosDisponiveis">
                                    <div class="col-12 text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Carregando alunos disponíveis...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mensagem quando nenhuma turma está selecionada -->
                        <div id="mensagemInicial" class="text-center text-muted">
                            <i class="fa fa-graduation-cap fa-3x mb-3"></i>
                            <h5>Selecione uma turma para gerenciar os alunos</h5>
                            <p>Escolha uma turma na lista acima para ver e gerenciar os alunos vinculados</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/core/jquery.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/bootstrap-notify.js"></script>
    
    <script>
        let turmaSelecionada = null;
        
        $(document).ready(function() {
            // Verificar se há parâmetros na URL
            const urlParams = new URLSearchParams(window.location.search);
            const turmaIdParam = urlParams.get('turma_id');
            const turmaNomeParam = urlParams.get('turma_nome');
            
            if (turmaIdParam && turmaNomeParam) {
                // Selecionar automaticamente a turma
                $('#selectTurma').val(turmaIdParam);
                const option = $('#selectTurma').find('option:selected');
                turmaSelecionada = turmaIdParam;
                mostrarInfoTurma(option);
                carregarAlunosTurma();
                $('#alunosTabs, #alunosTabContent, #statsCard').show();
                $('#mensagemInicial').hide();
            }
            
            $('#selectTurma').change(function() {
                const turmaId = $(this).val();
                const option = $(this).find('option:selected');
                
                if (turmaId) {
                    turmaSelecionada = turmaId;
                    mostrarInfoTurma(option);
                    carregarAlunosTurma();
                    $('#alunosTabs, #alunosTabContent, #statsCard').show();
                    $('#mensagemInicial').hide();
                } else {
                    turmaSelecionada = null;
                    $('#alunosTabs, #alunosTabContent, #statsCard, #turmaInfo').hide();
                    $('#mensagemInicial').show();
                }
            });
        });
        
        function mostrarInfoTurma(option) {
            const nome = option.data('nome');
            const descricao = option.data('descricao');
            const ano = option.data('ano');
            
            $('#turmaNome').text(nome);
            $('#turmaDescricao').text(descricao || 'Sem descrição');
            $('#turmaAno').text(`Ano Letivo: ${ano}`);
            $('#turmaInfo').show();
        }
        
        function carregarAlunosTurma() {
            if (!turmaSelecionada) return;
            
            // Carregar alunos vinculados
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'buscar_alunos_turma',
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlunosVinculados(response.alunos);
                    } else {
                        mostrarErro('Erro ao carregar alunos vinculados: ' + response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
            
            // Carregar alunos disponíveis
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'buscar_alunos_disponiveis',
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAlunosDisponiveis(response.alunos);
                    } else {
                        mostrarErro('Erro ao carregar alunos disponíveis: ' + response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
        }
        
        function mostrarAlunosVinculados(alunos) {
            const container = $('#alunosVinculados');
            const badge = $('#badgeVinculados');
            
            badge.text(alunos.length);
            
            if (alunos.length === 0) {
                container.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="fa fa-users fa-2x mb-3"></i>
                        <h5>Nenhum aluno vinculado</h5>
                        <p>Esta turma ainda não possui alunos vinculados</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            alunos.forEach(function(aluno) {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="aluno-card vinculado">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${aluno.nome}</h6>
                                    <small class="text-muted">
                                        <i class="fa fa-coins text-warning"></i> ${aluno.moedas} moedas<br>
                                        <i class="fa fa-star text-success"></i> ${aluno.xp_total} XP
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-desvincular text-white" 
                                        onclick="desvincularAluno(${aluno.id}, '${aluno.nome}')">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
        }
        
        function mostrarAlunosDisponiveis(alunos) {
            const container = $('#alunosDisponiveis');
            const badge = $('#badgeDisponiveis');
            
            badge.text(alunos.length);
            
            if (alunos.length === 0) {
                container.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="fa fa-check-circle fa-2x mb-3"></i>
                        <h5>Todos os alunos já estão vinculados</h5>
                        <p>Não há alunos disponíveis para vincular a esta turma</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            alunos.forEach(function(aluno) {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="aluno-card disponivel">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${aluno.nome}</h6>
                                    <small class="text-muted">
                                        <i class="fa fa-coins text-warning"></i> ${aluno.moedas} moedas<br>
                                        <i class="fa fa-star text-success"></i> ${aluno.xp_total} XP
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-vincular text-white" 
                                        onclick="vincularAluno(${aluno.id}, '${aluno.nome}')">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
        }
        
        function vincularAluno(alunoId, alunoNome) {
            if (!confirm(`Deseja vincular o aluno "${alunoNome}" a esta turma?`)) {
                return;
            }
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'vincular_aluno',
                    aluno_id: alunoId,
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarSucesso(response.message);
                        carregarAlunosTurma(); // Recarregar dados
                    } else {
                        mostrarErro(response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
        }
        
        function desvincularAluno(alunoId, alunoNome) {
            if (!confirm(`Deseja desvincular o aluno "${alunoNome}" desta turma?`)) {
                return;
            }
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'desvincular_aluno',
                    aluno_id: alunoId,
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarSucesso(response.message);
                        carregarAlunosTurma(); // Recarregar dados
                    } else {
                        mostrarErro(response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
        }
        
        function mostrarSucesso(mensagem) {
            $.notify({
                message: mensagem,
                icon: 'fa fa-check'
            }, {
                type: 'success',
                placement: {
                    from: 'top',
                    align: 'right'
                }
            });
        }
        
        function mostrarErro(mensagem) {
            $.notify({
                message: mensagem,
                icon: 'fa fa-exclamation-triangle'
            }, {
                type: 'danger',
                placement: {
                    from: 'top',
                    align: 'right'
                }
            });
        }
    </script>
</body>
</html>
