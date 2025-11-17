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

// Verificar se a tabela atitudes existe e buscar atitudes ativas
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'atitudes'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if ($tableExists) {
        $stmt_atitudes = $pdo->query("SELECT * FROM atitudes WHERE status = 'ativa' ORDER BY tipo, valor_moedas DESC");
        $atitudes = $stmt_atitudes->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $atitudes = [];
    }
} catch (PDOException $e) {
    $atitudes = [];
    $tableExists = false;
}
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
    
    <link rel="stylesheet" href="asset/loja.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap");
        
        :root {
            --color-light: #faf4f4;
            --color-dark: #17171f;
            --color-dark-rgb: 23, 23, 31;
            --font-family: "Poppins", sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--color-dark);
            font-family: var(--font-family);
            color: var(--color-light);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .aluno-header {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fade-in-bottom 0.6s cubic-bezier(0.39, 0.575, 0.565, 1) both;
        }
        
        .aluno-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            margin: 0 auto 20px;
            display: block;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .aluno-header h2 {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .aluno-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            animation: fade-in-bottom 0.6s cubic-bezier(0.39, 0.575, 0.565, 1) both;
        }
        
        .stats-card:nth-child(1) { animation-delay: 0.1s; }
        .stats-card:nth-child(2) { animation-delay: 0.2s; }
        .stats-card:nth-child(3) { animation-delay: 0.3s; }
        .stats-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stats-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stats-card h5 {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stats-card h2 {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .stats-card .text-muted {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
        }
        
        .progress {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-bar-custom {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
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
            border-radius: 15px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--color-dark);
            background: radial-gradient(
                farthest-side at bottom center,
                #f0c1bb 25%,
                transparent 75%
            ),
            radial-gradient(farthest-side at left bottom, #ebc6c0 25%, transparent 75%),
            radial-gradient(farthest-side at left center, #dabbc3 25%, transparent 75%),
            radial-gradient(farthest-side at left top, #e7cdce 25%, transparent 75%),
            radial-gradient(farthest-side at right top, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right bottom, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right center, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at top center, #f6ede8 60%, transparent 100%),
            radial-gradient(
                farthest-side at center center,
                #eed8db 100%,
                transparent 100%
            );
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(
                farthest-side at bottom center,
                #f0c1bb 25%,
                transparent 75%
            ),
            radial-gradient(farthest-side at left bottom, #ebc6c0 25%, transparent 75%),
            radial-gradient(farthest-side at left center, #dabbc3 25%, transparent 75%),
            radial-gradient(farthest-side at left top, #e7cdce 25%, transparent 75%),
            radial-gradient(farthest-side at right top, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right bottom, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right center, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at top center, #f6ede8 60%, transparent 100%),
            radial-gradient(
                farthest-side at center center,
                #eed8db 100%,
                transparent 100%
            );
            z-index: -1;
            transform: scaleY(0.7);
            opacity: 0.6;
            filter: blur(35px);
        }
        
        .action-btn:hover {
            transform: scale(1.05);
        }
        
        .btn-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.3);
        }
        
        .historico-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fade-in-bottom 0.6s cubic-bezier(0.39, 0.575, 0.565, 1) both 0.5s;
        }
        
        .historico-section h5 {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .historico-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .historico-item:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(5px);
        }
        
        .historico-item h6 {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .historico-item small {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .bg-success {
            background: rgba(40, 167, 69, 0.2) !important;
            color: #28a745 !important;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .bg-danger {
            background: rgba(220, 53, 69, 0.2) !important;
            color: #dc3545 !important;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .bg-warning {
            background: rgba(255, 193, 7, 0.2) !important;
            color: #ffc107 !important;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-aprovado {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-pendente {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-rejeitado {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .modal-content {
            background: var(--color-dark);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--color-light);
        }
        
        .modal-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px 20px 0 0;
            color: var(--color-light);
        }
        
        .modal-body {
            background: var(--color-dark);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 15px;
            color: var(--color-light);
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
            color: var(--color-light);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-label {
            color: var(--color-light);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background: radial-gradient(
                farthest-side at bottom center,
                #f0c1bb 25%,
                transparent 75%
            ),
            radial-gradient(farthest-side at left bottom, #ebc6c0 25%, transparent 75%),
            radial-gradient(farthest-side at left center, #dabbc3 25%, transparent 75%),
            radial-gradient(farthest-side at left top, #e7cdce 25%, transparent 75%),
            radial-gradient(farthest-side at right top, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right bottom, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at right center, #bcb7d7 25%, transparent 75%),
            radial-gradient(farthest-side at top center, #f6ede8 60%, transparent 100%),
            radial-gradient(
                farthest-side at center center,
                #eed8db 100%,
                transparent 100%
            );
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            color: var(--color-dark);
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: scale(1.05);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--color-light);
            border-radius: 12px;
            padding: 12px 25px;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }
        
        .card-body {
            color: var(--color-light);
        }
        
        .alert {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--color-light);
            border-radius: 12px;
        }
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border-color: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        
        .loading-spinner {
            background: var(--color-dark);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .loading-spinner p {
            color: var(--color-light);
            margin-top: 15px;
        }
        
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid var(--color-light);
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
        
        @keyframes fade-in-bottom {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            animation: fade-in-bottom 0.3s ease;
        }
        
        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .text-primary {
            color: #667eea !important;
        }
        
        .text-warning {
            color: #ffc107 !important;
        }
        
        .text-success {
            color: #28a745 !important;
        }
        
        .text-info {
            color: #17a2b8 !important;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .main-container {
                padding: 10px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .aluno-header {
                padding: 20px 15px;
            }
            
            .aluno-avatar {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header do Aluno -->
        <div class="aluno-header">
            <img src="<?= htmlspecialchars($aluno['avatar'] ?? 'asset/img/avatar/default.gif') ?>" 
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
        <div class="stats-grid">
            <div class="stats-card">
                <h5>
                    <i class="fas fa-star"></i> Experiência (XP)
                </h5>
                <h2><?= number_format($aluno['xp_total']) ?></h2>
                <div class="progress">
                    <div class="progress-bar progress-bar-custom" 
                         style="width: <?= $progresso_nivel ?>%"></div>
                </div>
                <small class="text-muted">
                    <?= $xp_proximo_nivel ?> XP para o próximo nível
                </small>
            </div>
            
            <div class="stats-card">
                <h5>
                    <i class="fas fa-coins"></i> Moedas
                </h5>
                <h2><?= number_format($aluno['moedas']) ?></h2>
                <p class="text-muted">Disponíveis para troca</p>
            </div>
            
            <div class="stats-card">
                <h5>
                    <i class="fas fa-tasks"></i> Missões
                </h5>
                <h2><?= $aluno['missoes_aprovadas'] ?></h2>
                <p class="text-muted">
                    <?= $aluno['missoes_pendentes'] ?> pendentes<br>
                    <?= $aluno['missoes_rejeitadas'] ?> rejeitadas
                </p>
            </div>
            
            <div class="stats-card">
                <h5>
                    <i class="fas fa-shopping-cart"></i> Trocas
                </h5>
                <h2><?= $aluno['trocas_aprovadas'] ?></h2>
                <p class="text-muted">
                    <?= $aluno['trocas_pendentes'] ?> pendentes
                </p>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="action-buttons">
            <button class="action-btn btn-moedas" onclick="abrirModal('atitudes', 'ganho')">
                <i class="fas fa-plus"></i> Aplicar Atitude Positiva
            </button>
            <button class="action-btn btn-moedas" onclick="abrirModal('atitudes', 'perda')">
                <i class="fas fa-minus"></i> Aplicar Atitude Negativa
            </button>
            <button class="action-btn btn-danger" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Atualizar Dados
            </button>
            <a href="/painel/professor/professor_dashboard.php" class="action-btn btn-danger">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>

        <!-- Histórico de Atividades -->
        <div class="historico-section">
            <h5>
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
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div style="flex: 1; min-width: 200px;">
                                <h6>
                                    <?php
                                    $icon = 'fas fa-trophy';
                                    $iconColor = 'text-warning';
                                    if ($item['tipo'] === 'troca') {
                                        $icon = 'fas fa-shopping-cart';
                                        $iconColor = 'text-info';
                                    } elseif ($item['tipo'] === 'acao_professor') {
                                        $icon = 'fas fa-user-edit';
                                        $iconColor = 'text-primary';
                                    }
                                    ?>
                                    <i class="<?= $icon ?> <?= $iconColor ?>"></i>
                                    <?= htmlspecialchars($item['prefixo'] . $item['descricao']) ?>
                                </h6>
                                <small>
                                    <?= date('d/m/Y H:i', strtotime($item['data_atividade'])) ?>
                                </small>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
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
        // Dados das atitudes para JavaScript
        const atitudes = <?= json_encode($atitudes); ?>;
        
        function atualizarDetalhesAtitude() {
            const select = document.getElementById('atitude_id');
            if (!select) return;
            
            const detalhesDiv = document.getElementById('detalhesAtitude');
            const descricaoSpan = document.getElementById('descricaoAtitude');
            const valorSpan = document.getElementById('valorAtitude');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const valor = option.getAttribute('data-valor');
                const descricao = option.getAttribute('data-descricao');
                const tipo = document.getElementById('operacaoAcao') ? document.getElementById('operacaoAcao').value : 'ganho';
                
                if (descricaoSpan) descricaoSpan.textContent = descricao || 'Sem descrição';
                if (valorSpan) {
                    valorSpan.textContent = (tipo === 'ganho' ? '+' : '-') + valor + ' moedas';
                    valorSpan.className = 'badge badge-' + (tipo === 'ganho' ? 'success' : 'danger');
                }
                if (detalhesDiv) detalhesDiv.style.display = 'block';
            } else {
                if (detalhesDiv) detalhesDiv.style.display = 'none';
            }
        }
        
        function abrirModal(tipo, operacao) {
            const modal = new bootstrap.Modal(document.getElementById('modalAcao'));
            const titulo = document.getElementById('modalTitulo');
            const conteudo = document.getElementById('conteudoModal');
            const form = document.getElementById('formAcao');
            
            document.getElementById('tipoAcao').value = tipo;
            document.getElementById('operacaoAcao').value = operacao;
            
            let tituloTexto = '';
            let conteudoHtml = '';
            
            if (tipo === 'atitudes') {
                tituloTexto = operacao === 'ganho' ? 'Aplicar Atitude Positiva' : 'Aplicar Atitude Negativa';
                
                // Filtrar atitudes por tipo
                const atitudesFiltradas = atitudes.filter(a => a.tipo === operacao);
                
                if (atitudesFiltradas.length === 0) {
                    conteudoHtml = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Nenhuma atitude ${operacao === 'ganho' ? 'positiva' : 'negativa'} cadastrada no sistema.
                        </div>
                    `;
                } else {
                    let opcoesAtitudes = '<option value="">Selecione uma atitude</option>';
                    atitudesFiltradas.forEach(atitude => {
                        const sinal = operacao === 'ganho' ? '+' : '-';
                        opcoesAtitudes += `<option value="${atitude.id}" data-valor="${atitude.valor_moedas}" data-descricao="${atitude.descricao}">
                            ${atitude.titulo} (${sinal}${atitude.valor_moedas} moedas)
                        </option>`;
                    });
                    
                    conteudoHtml = `
                        <div class="mb-3">
                            <label for="atitude_id" class="form-label">Selecione a Atitude <span class="text-danger">*</span></label>
                            <select class="form-control" id="atitude_id" name="atitude_id" required onchange="atualizarDetalhesAtitude()">
                                ${opcoesAtitudes}
                            </select>
                        </div>
                        <div class="mb-3" id="detalhesAtitude" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-1"><strong>Descrição:</strong></p>
                                    <p class="text-muted mb-2" id="descricaoAtitude"></p>
                                    <p class="mb-0"><strong>Valor:</strong> <span id="valorAtitude" class="badge badge-${operacao === 'ganho' ? 'success' : 'danger'}"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo Adicional (Opcional)</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                      placeholder="Adicione um motivo específico para esta aplicação..."></textarea>
                        </div>
                    `;
                }
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
            if (tipo === 'atitudes') {
                const atitudeId = formData.get('atitude_id');
                if (!atitudeId) {
                    showAlert('Por favor, selecione uma atitude', 'danger');
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
