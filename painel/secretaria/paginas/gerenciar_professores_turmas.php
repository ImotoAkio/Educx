<?php
/**
 * Página para gerenciar vínculos de professores às turmas
 * Localização: painel/secretaria/paginas/gerenciar_professores_turmas.php
 */

session_start();
require '../../../db.php';
require 'include/feedback.php';

$pagina_ativa = 'gerenciar_professores_turmas';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

// Processar ações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'vincular_professor':
                $professor_id = (int)$_POST['professor_id'];
                $turma_id = (int)$_POST['turma_id'];
                
                // Verificar se já existe vínculo
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM turmas_professores WHERE professor_id = :professor_id AND turma_id = :turma_id");
                $stmt->execute([':professor_id' => $professor_id, ':turma_id' => $turma_id]);
                $existe = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existe['total'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Professor já está vinculado a esta turma.']);
                    exit;
                }
                
                // Criar vínculo
                $stmt = $pdo->prepare("INSERT INTO turmas_professores (professor_id, turma_id) VALUES (:professor_id, :turma_id)");
                $stmt->execute([':professor_id' => $professor_id, ':turma_id' => $turma_id]);
                
                echo json_encode(['success' => true, 'message' => 'Professor vinculado à turma com sucesso!']);
                break;
                
            case 'desvincular_professor':
                $professor_id = (int)$_POST['professor_id'];
                $turma_id = (int)$_POST['turma_id'];
                
                $stmt = $pdo->prepare("DELETE FROM turmas_professores WHERE professor_id = :professor_id AND turma_id = :turma_id");
                $stmt->execute([':professor_id' => $professor_id, ':turma_id' => $turma_id]);
                
                echo json_encode(['success' => true, 'message' => 'Professor desvinculado da turma com sucesso!']);
                break;
                
            case 'buscar_professores_turma':
                $turma_id = (int)$_POST['turma_id'];
                
                // Buscar professores da turma
                $stmt = $pdo->prepare("
                    SELECT p.id, p.nome, p.email, p.tipo_usuario
                    FROM professores p
                    JOIN turmas_professores tp ON p.id = tp.professor_id
                    WHERE tp.turma_id = :turma_id
                    ORDER BY p.nome ASC
                ");
                $stmt->execute([':turma_id' => $turma_id]);
                $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'professores' => $professores]);
                break;
                
            case 'buscar_professores_disponiveis':
                $turma_id = (int)$_POST['turma_id'];
                
                // Buscar professores que NÃO estão na turma
                $stmt = $pdo->prepare("
                    SELECT p.id, p.nome, p.email, p.tipo_usuario
                    FROM professores p
                    WHERE p.id NOT IN (
                        SELECT professor_id FROM turmas_professores WHERE turma_id = :turma_id
                    )
                    ORDER BY p.nome ASC
                ");
                $stmt->execute([':turma_id' => $turma_id]);
                $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'professores' => $professores]);
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

// Buscar todos os professores
$stmt = $pdo->query("SELECT id, nome, email, tipo_usuario FROM professores ORDER BY nome ASC");
$todos_professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Professores e Turmas</title>
    
    <!-- CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/paper-dashboard.min.css" rel="stylesheet">
    <link href="../assets/css/demo.css" rel="stylesheet">
    <link href="../assets/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        .professor-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .professor-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .professor-card.vinculado {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .professor-card.disponivel {
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
        .tipo-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }
        .tipo-professor {
            background: #007bff;
            color: white;
        }
        .tipo-secretaria {
            background: #6c757d;
            color: white;
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
                            <i class="fa fa-chalkboard-teacher text-primary"></i> Gerenciar Professores e Turmas
                        </h4>
                        <p class="card-category">Vincule professores às turmas e gerencie os relacionamentos</p>
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
                                            <h3 id="totalProfessores">0</h3>
                                            <small>Professores</small>
                                        </div>
                                        <div class="col-4">
                                            <h3 id="totalDisponiveis">0</h3>
                                            <small>Disponíveis</small>
                                        </div>
                                        <div class="col-4">
                                            <h3 id="totalGeral"><?= count($todos_professores); ?></h3>
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
                        <ul class="nav nav-tabs" id="professoresTabs" style="display: none;">
                            <li class="nav-item">
                                <a class="nav-link active" id="vinculados-tab" data-toggle="tab" href="#vinculados" role="tab">
                                    <i class="fa fa-check-circle text-success"></i> Professores Vinculados
                                    <span class="badge badge-success" id="badgeVinculados">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="disponiveis-tab" data-toggle="tab" href="#disponiveis" role="tab">
                                    <i class="fa fa-plus-circle text-primary"></i> Professores Disponíveis
                                    <span class="badge badge-primary" id="badgeDisponiveis">0</span>
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Conteúdo das Abas -->
                        <div class="tab-content" id="professoresTabContent" style="display: none;">
                            <!-- Professores Vinculados -->
                            <div class="tab-pane fade show active" id="vinculados" role="tabpanel">
                                <div class="row" id="professoresVinculados">
                                    <div class="col-12 text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Carregando professores vinculados...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Professores Disponíveis -->
                            <div class="tab-pane fade" id="disponiveis" role="tabpanel">
                                <div class="row" id="professoresDisponiveis">
                                    <div class="col-12 text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Carregando professores disponíveis...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mensagem quando nenhuma turma está selecionada -->
                        <div id="mensagemInicial" class="text-center text-muted">
                            <i class="fa fa-chalkboard-teacher fa-3x mb-3"></i>
                            <h5>Selecione uma turma para gerenciar os professores</h5>
                            <p>Escolha uma turma na lista acima para ver e gerenciar os professores vinculados</p>
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
                carregarProfessoresTurma();
                $('#professoresTabs, #professoresTabContent, #statsCard').show();
                $('#mensagemInicial').hide();
            }
            
            $('#selectTurma').change(function() {
                const turmaId = $(this).val();
                const option = $(this).find('option:selected');
                
                if (turmaId) {
                    turmaSelecionada = turmaId;
                    mostrarInfoTurma(option);
                    carregarProfessoresTurma();
                    $('#professoresTabs, #professoresTabContent, #statsCard').show();
                    $('#mensagemInicial').hide();
                } else {
                    turmaSelecionada = null;
                    $('#professoresTabs, #professoresTabContent, #statsCard, #turmaInfo').hide();
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
        
        function carregarProfessoresTurma() {
            if (!turmaSelecionada) return;
            
            // Carregar professores vinculados
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'buscar_professores_turma',
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarProfessoresVinculados(response.professores);
                    } else {
                        mostrarErro('Erro ao carregar professores vinculados: ' + response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
            
            // Carregar professores disponíveis
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'buscar_professores_disponiveis',
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarProfessoresDisponiveis(response.professores);
                    } else {
                        mostrarErro('Erro ao carregar professores disponíveis: ' + response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
        }
        
        function mostrarProfessoresVinculados(professores) {
            const container = $('#professoresVinculados');
            const badge = $('#badgeVinculados');
            
            badge.text(professores.length);
            
            if (professores.length === 0) {
                container.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="fa fa-chalkboard-teacher fa-2x mb-3"></i>
                        <h5>Nenhum professor vinculado</h5>
                        <p>Esta turma ainda não possui professores vinculados</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            professores.forEach(function(professor) {
                const tipoClass = professor.tipo_usuario === 'professor' ? 'tipo-professor' : 'tipo-secretaria';
                const tipoText = professor.tipo_usuario === 'professor' ? 'Professor' : 'Secretaria';
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="professor-card vinculado">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${professor.nome}</h6>
                                    <small class="text-muted">
                                        <i class="fa fa-envelope text-primary"></i> ${professor.email}<br>
                                        <span class="tipo-badge ${tipoClass}">${tipoText}</span>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-desvincular text-white" 
                                        onclick="desvincularProfessor(${professor.id}, '${professor.nome}')">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
        }
        
        function mostrarProfessoresDisponiveis(professores) {
            const container = $('#professoresDisponiveis');
            const badge = $('#badgeDisponiveis');
            
            badge.text(professores.length);
            
            if (professores.length === 0) {
                container.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="fa fa-check-circle fa-2x mb-3"></i>
                        <h5>Todos os professores já estão vinculados</h5>
                        <p>Não há professores disponíveis para vincular a esta turma</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            professores.forEach(function(professor) {
                const tipoClass = professor.tipo_usuario === 'professor' ? 'tipo-professor' : 'tipo-secretaria';
                const tipoText = professor.tipo_usuario === 'professor' ? 'Professor' : 'Secretaria';
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="professor-card disponivel">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${professor.nome}</h6>
                                    <small class="text-muted">
                                        <i class="fa fa-envelope text-primary"></i> ${professor.email}<br>
                                        <span class="tipo-badge ${tipoClass}">${tipoText}</span>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-vincular text-white" 
                                        onclick="vincularProfessor(${professor.id}, '${professor.nome}')">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
        }
        
        function vincularProfessor(professorId, professorNome) {
            if (!confirm(`Deseja vincular o professor "${professorNome}" a esta turma?`)) {
                return;
            }
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'vincular_professor',
                    professor_id: professorId,
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarSucesso(response.message);
                        carregarProfessoresTurma(); // Recarregar dados
                    } else {
                        mostrarErro(response.message);
                    }
                },
                error: function() {
                    mostrarErro('Erro na comunicação com o servidor');
                }
            });
        }
        
        function desvincularProfessor(professorId, professorNome) {
            if (!confirm(`Deseja desvincular o professor "${professorNome}" desta turma?`)) {
                return;
            }
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'desvincular_professor',
                    professor_id: professorId,
                    turma_id: turmaSelecionada
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarSucesso(response.message);
                        carregarProfessoresTurma(); // Recarregar dados
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
  <!-- Footer com scripts mobile -->
  <?php include 'include/footer.php'; ?>
</body>
</html>
