<?php
session_start();
require 'db.php';

// Verificar se é professor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'professor') {
    // Redirecionar para login com retorno para esta página
    $current_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: professor_login.php?return_to=" . $current_url);
    exit;
}

// Verificar se foi passado ID do aluno
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: painel/professor/professor_dashboard.php');
    exit;
}

$aluno_id = (int)$_GET['id'];

// Verificar se as tabelas necessárias existem
$tabelas_necessarias = ['alunos', 'alunos_turmas', 'turmas', 'solicitacoes_missoes', 'missoes'];
$tabelas_existentes = [];

foreach ($tabelas_necessarias as $tabela) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            $tabelas_existentes[] = $tabela;
        }
    } catch (PDOException $e) {
        // Tabela não existe
    }
}

// Buscar dados do aluno
$sql = "
    SELECT 
        a.*,
        " . (in_array('turmas', $tabelas_existentes) ? "t.nome as turma_nome, t.id as turma_id," : "NULL as turma_nome, NULL as turma_id,") . "
        " . (in_array('solicitacoes_missoes', $tabelas_existentes) ? "
        COUNT(DISTINCT sm.id) as total_missoes,
        COUNT(DISTINCT CASE WHEN sm.status = 'aprovado' THEN sm.id END) as missoes_aprovadas,
        COUNT(DISTINCT CASE WHEN sm.status = 'pendente' THEN sm.id END) as missoes_pendentes,
        COUNT(DISTINCT CASE WHEN sm.status = 'rejeitado' THEN sm.id END) as missoes_rejeitadas," : "
        0 as total_missoes, 0 as missoes_aprovadas, 0 as missoes_pendentes, 0 as missoes_rejeitadas,") . "
        0 as total_trocas,
        0 as trocas_aprovadas,
        0 as trocas_pendentes
    FROM alunos a
    " . (in_array('alunos_turmas', $tabelas_existentes) && in_array('turmas', $tabelas_existentes) ? "
    LEFT JOIN alunos_turmas at ON a.id = at.aluno_id
    LEFT JOIN turmas t ON at.turma_id = t.id" : "") . "
    " . (in_array('solicitacoes_missoes', $tabelas_existentes) ? "
    LEFT JOIN solicitacoes_missoes sm ON a.id = sm.aluno_id" : "") . "
    WHERE a.id = :aluno_id
    GROUP BY a.id
";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([':aluno_id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Se der erro na consulta, redireciona para o dashboard
    header('Location: painel/professor/professor_dashboard.php');
    exit;
}

if (!$aluno) {
    header('Location: painel/professor/professor_dashboard.php');
    exit;
}

// Buscar histórico recente de atividades
$historico_sql = "";

if (in_array('solicitacoes_missoes', $tabelas_existentes) && in_array('missoes', $tabelas_existentes)) {
    $historico_sql .= "
        (SELECT 
            'missao' as tipo,
            m.nome as descricao,
            sm.xp as xp_ganho,
            sm.moedas as moedas_ganhas,
            sm.data_solicitacao as data_atividade,
            sm.status,
            'Missão: ' as prefixo
        FROM solicitacoes_missoes sm
        JOIN missoes m ON sm.missao_id = m.id
        WHERE sm.aluno_id = :aluno_id)";
}

if (in_array('log_acoes', $tabelas_existentes)) {
    if (!empty($historico_sql)) {
        $historico_sql .= " UNION ALL ";
    }
    $historico_sql .= "
        (SELECT 
            'acao_professor' as tipo,
            la.descricao as descricao,
            la.xp_alterado as xp_ganho,
            la.moedas_alteradas as moedas_ganhas,
            la.data_acao as data_atividade,
            'executado' as status,
            'Ação: ' as prefixo
        FROM log_acoes la
        WHERE la.aluno_id = :aluno_id)";
}

if (empty($historico_sql)) {
    $historico_sql = "SELECT 'vazio' as tipo, 'Nenhuma atividade' as descricao, 0 as xp_ganho, 0 as moedas_ganhas, NOW() as data_atividade, 'nenhum' as status, '' as prefixo WHERE 1=0";
}

$historico_sql .= " ORDER BY data_atividade DESC LIMIT 15";

try {
    $stmt_historico = $pdo->prepare($historico_sql);
    $stmt_historico->execute([':aluno_id' => $aluno_id]);
    $historico = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Se der erro, define histórico vazio
    $historico = [];
}

// Buscar ranking do aluno na turma
$posicao_ranking = 1; // Valor padrão

if (in_array('alunos_turmas', $tabelas_existentes)) {
    try {
        $stmt_ranking = $pdo->prepare("
            SELECT 
                COUNT(*) + 1 as posicao
            FROM alunos a2
            LEFT JOIN alunos_turmas at2 ON a2.id = at2.aluno_id
            WHERE at2.turma_id = (SELECT turma_id FROM alunos_turmas WHERE aluno_id = :aluno_id)
            AND a2.xp_total > :xp_aluno
        ");

        $stmt_ranking->execute([
            ':aluno_id' => $aluno_id,
            ':xp_aluno' => $aluno['xp_total']
        ]);
        $ranking = $stmt_ranking->fetch(PDO::FETCH_ASSOC);
        $posicao_ranking = $ranking['posicao'] ?? 1;
    } catch (PDOException $e) {
        // Se der erro, mantém o valor padrão
        $posicao_ranking = 1;
    }
}

// Calcular nível do aluno
$nivel = floor($aluno['xp_total'] / 100) + 1;
$xp_proximo_nivel = ($nivel * 100) - $aluno['xp_total'];
$progresso_nivel = (($aluno['xp_total'] % 100) / 100) * 100;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Aluno - <?= htmlspecialchars($aluno['nome']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }
        
        .aluno-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .aluno-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            margin: 0 auto 20px;
            display: block;
            object-fit: cover;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #667eea;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-xp {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-moedas {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .btn-missao {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }
        
        .progress-bar-custom {
            height: 20px;
            border-radius: 10px;
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        
        .historico-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
        }
        
        .ranking-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-aprovado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-rejeitado {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 5px;
                padding: 15px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .aluno-header {
                padding: 20px 15px;
            }
            
            .aluno-avatar {
                width: 80px;
                height: 80px;
            }
            
            .stats-card {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .historico-item {
                padding: 15px;
            }
            
            .col-md-3, .col-md-4, .col-md-6 {
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .main-container {
                margin: 2px;
                padding: 10px;
            }
            
            .aluno-header h2 {
                font-size: 1.5rem;
            }
            
            .aluno-header p {
                font-size: 0.9rem;
            }
            
            .action-btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header do Aluno -->
        <div class="aluno-header">
            <img src="asset/img/avatar/<?= htmlspecialchars($aluno['avatar'] ?? 'default.gif') ?>" 
                 alt="Avatar" class="aluno-avatar">
            <h2 class="mb-2"><?= htmlspecialchars($aluno['nome']) ?></h2>
            <p class="mb-0">
                <i class="fas fa-graduation-cap"></i> 
                <?= htmlspecialchars($aluno['turma_nome'] ?? 'Sem turma') ?> | 
                <i class="fas fa-trophy"></i> 
                Nível <?= $nivel ?> | 
                <i class="fas fa-medal"></i> 
                #<?= $posicao_ranking ?> na turma
            </p>
        </div>

        <!-- Estatísticas Principais -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-star"></i> Experiência (XP)
                    </h5>
                    <h2 class="text-primary mb-2"><?= number_format($aluno['xp_total']) ?></h2>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-custom" 
                             style="width: <?= $progresso_nivel ?>%"></div>
                    </div>
                    <small class="text-muted">
                        <?= $xp_proximo_nivel ?> XP para o próximo nível
                    </small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <h5 class="text-warning mb-3">
                        <i class="fas fa-coins"></i> Moedas
                    </h5>
                    <h2 class="text-warning mb-2"><?= number_format($aluno['moedas']) ?></h2>
                    <p class="text-muted mb-0">Disponíveis para troca</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-tasks"></i> Missões
                    </h5>
                    <h2 class="text-success mb-2"><?= $aluno['missoes_aprovadas'] ?></h2>
                    <p class="text-muted mb-0">
                        <?= $aluno['missoes_pendentes'] ?> pendentes<br>
                        <?= $aluno['missoes_rejeitadas'] ?> rejeitadas
                    </p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <h5 class="text-info mb-3">
                        <i class="fas fa-shopping-cart"></i> Trocas
                    </h5>
                    <h2 class="text-info mb-2"><?= $aluno['trocas_aprovadas'] ?></h2>
                    <p class="text-muted mb-0">
                        <?= $aluno['trocas_pendentes'] ?> pendentes
                    </p>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="action-buttons">
            <button class="action-btn btn-xp" onclick="abrirModal('xp', 'adicionar')">
                <i class="fas fa-plus"></i> Adicionar XP
            </button>
            <button class="action-btn btn-xp" onclick="abrirModal('xp', 'remover')">
                <i class="fas fa-minus"></i> Remover XP
            </button>
            <button class="action-btn btn-moedas" onclick="abrirModal('moedas', 'adicionar')">
                <i class="fas fa-plus"></i> Adicionar Moedas
            </button>
            <button class="action-btn btn-moedas" onclick="abrirModal('moedas', 'remover')">
                <i class="fas fa-minus"></i> Remover Moedas
            </button>
            <button class="action-btn btn-missao" onclick="abrirModal('missao', 'criar')">
                <i class="fas fa-plus"></i> Criar Missão Rápida
            </button>
            <button class="action-btn btn-danger" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Atualizar Dados
            </button>
            <a href="/painel/professor/professor_dashboard.php" class="action-btn btn-danger">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>

        <!-- Histórico de Atividades -->
        <div class="stats-card">
            <h5 class="text-primary mb-4">
                <i class="fas fa-history"></i> Histórico Recente
            </h5>
            
            <?php if (empty($historico)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Nenhuma atividade registrada ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($historico as $item): ?>
                    <div class="historico-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">
                                    <?php
                                    $icon = 'fas fa-trophy text-warning';
                                    if ($item['tipo'] === 'troca') {
                                        $icon = 'fas fa-shopping-cart text-info';
                                    } elseif ($item['tipo'] === 'acao_professor') {
                                        $icon = 'fas fa-user-edit text-primary';
                                    }
                                    ?>
                                    <i class="<?= $icon ?>"></i>
                                    <?= htmlspecialchars($item['prefixo'] . $item['descricao']) ?>
                                </h6>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($item['data_atividade'])) ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <?php if ($item['xp_ganho'] != 0): ?>
                                    <span class="badge <?= $item['xp_ganho'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $item['xp_ganho'] > 0 ? '+' : '' ?><?= $item['xp_ganho'] ?> XP
                                    </span>
                                <?php endif; ?>
                                <?php if ($item['moedas_ganhas'] != 0): ?>
                                    <span class="badge <?= $item['moedas_ganhas'] > 0 ? 'bg-warning' : 'bg-danger' ?>">
                                        <?= $item['moedas_ganhas'] > 0 ? '+' : '' ?><?= abs($item['moedas_ganhas']) ?> Moedas
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="status-badge status-<?= $item['status'] ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Processando ação...</p>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Modal para Ações -->
    <div class="modal fade" id="modalAcao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">
                        <i class="fas fa-cog"></i> Ação
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAcao">
                        <input type="hidden" id="tipoAcao" name="tipo">
                        <input type="hidden" id="operacaoAcao" name="operacao">
                        <input type="hidden" name="aluno_id" value="<?= $aluno_id ?>">
                        
                        <div id="conteudoModal">
                            <!-- Conteúdo será preenchido via JavaScript -->
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function abrirModal(tipo, operacao) {
            const modal = new bootstrap.Modal(document.getElementById('modalAcao'));
            const titulo = document.getElementById('modalTitulo');
            const conteudo = document.getElementById('conteudoModal');
            const form = document.getElementById('formAcao');
            
            document.getElementById('tipoAcao').value = tipo;
            document.getElementById('operacaoAcao').value = operacao;
            
            let tituloTexto = '';
            let conteudoHtml = '';
            
            if (tipo === 'xp') {
                tituloTexto = operacao === 'adicionar' ? 'Adicionar XP' : 'Remover XP';
                conteudoHtml = `
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor de XP</label>
                        <input type="number" class="form-control" id="valor" name="valor" 
                               min="1" max="1000" required>
                        <div class="form-text">
                            ${operacao === 'adicionar' ? 'XP será adicionado ao total do aluno' : 'XP será removido do total do aluno'}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                  placeholder="Descreva o motivo da ${operacao === 'adicionar' ? 'adição' : 'remoção'} de XP"></textarea>
                    </div>
                `;
            } else if (tipo === 'moedas') {
                tituloTexto = operacao === 'adicionar' ? 'Adicionar Moedas' : 'Remover Moedas';
                conteudoHtml = `
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor de Moedas</label>
                        <input type="number" class="form-control" id="valor" name="valor" 
                               min="1" max="1000" required>
                        <div class="form-text">
                            ${operacao === 'adicionar' ? 'Moedas serão adicionadas ao saldo do aluno' : 'Moedas serão removidas do saldo do aluno'}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                  placeholder="Descreva o motivo da ${operacao === 'adicionar' ? 'adição' : 'remoção'} de moedas"></textarea>
                    </div>
                `;
            } else if (tipo === 'missao') {
                tituloTexto = 'Criar Missão Rápida';
                conteudoHtml = `
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Missão</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="xp" class="form-label">XP de Recompensa</label>
                                <input type="number" class="form-control" id="xp" name="xp" min="1" max="1000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="moedas" class="form-label">Moedas de Recompensa</label>
                                <input type="number" class="form-control" id="moedas" name="moedas" min="1" max="1000" required>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            titulo.innerHTML = `<i class="fas fa-cog"></i> ${tituloTexto}`;
            conteudo.innerHTML = conteudoHtml;
            
            modal.show();
        }
        
        // Função para mostrar alertas
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show alert-custom" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.insertAdjacentHTML('beforeend', alertHtml);
            
            // Auto-remove após 5 segundos
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }
        
        // Função para mostrar/ocultar loading
        function toggleLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = show ? 'flex' : 'none';
        }
        
        // Submissão do formulário
        document.getElementById('formAcao').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const tipo = formData.get('tipo');
            const operacao = formData.get('operacao');
            
            // Validações
            if (tipo === 'xp' || tipo === 'moedas') {
                const valor = parseInt(formData.get('valor'));
                if (valor < 1 || valor > 1000) {
                    showAlert('Valor deve estar entre 1 e 1000', 'danger');
                    return;
                }
            }
            
            // Mostrar loading
            toggleLoading(true);
            
            // Enviar dados via AJAX
            fetch('api/professor_acoes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                toggleLoading(false);
                
                if (data.success) {
                    showAlert('Ação executada com sucesso!', 'success');
                    
                    // Fechar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAcao'));
                    modal.hide();
                    
                    // Recarregar página após 1 segundo
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('Erro: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                toggleLoading(false);
                console.error('Erro:', error);
                showAlert('Erro ao executar ação. Tente novamente.', 'danger');
            });
        });
        
        // Auto-refresh a cada 30 segundos
        setInterval(() => {
            // Só atualiza se não estiver em um modal aberto
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
