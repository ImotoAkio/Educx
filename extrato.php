<?php
// Configurar timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');

require 'db.php';

// Verifica se o ID do aluno foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do aluno não fornecido.");
}

$id = (int) $_GET['id'];

// Consulta as informações do aluno
try {
    $stmt = $pdo->prepare("SELECT id, nome, moedas, avatar, xp_atual, xp_total, nivel, fundo FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("Aluno não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao acessar o banco de dados: " . $e->getMessage());
}

// Verificar se a tabela extrato_moedas existe e buscar extrato de moedas do aluno
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'extrato_moedas'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if ($tableExists) {
        $stmt_extrato = $pdo->prepare("
            SELECT 
                em.*,
                a.titulo as atitude_titulo,
                p.nome as professor_nome
            FROM extrato_moedas em
            LEFT JOIN atitudes a ON em.atitude_id = a.id
            LEFT JOIN professores p ON em.professor_id = p.id
            WHERE em.aluno_id = :aluno_id
            ORDER BY em.data_transacao DESC
            LIMIT 100
        ");
        $stmt_extrato->execute([':aluno_id' => $id]);
        $extrato = $stmt_extrato->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $extrato = [];
    }
} catch (PDOException $e) {
    $extrato = [];
    $tableExists = false;
}

// Calcular totais
$total_ganhos = 0;
$total_perdas = 0;
foreach ($extrato as $transacao) {
    if ($transacao['tipo'] === 'ganho') {
        $total_ganhos += $transacao['valor'];
    } else {
        $total_perdas += $transacao['valor'];
    }
}
$saldo_atual = $aluno['moedas'];

// Agrupar por data
$extrato_por_data = [];
foreach ($extrato as $transacao) {
    $data = date('d/m/Y', strtotime($transacao['data_transacao']));
    if (!isset($extrato_por_data[$data])) {
        $extrato_por_data[$data] = [];
    }
    $extrato_por_data[$data][] = $transacao;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    <title>Extrato de Moedas - <?= htmlspecialchars($aluno['nome']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0a0e27;
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .bank-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .bank-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-3px);
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .balance-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .balance-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .balance-amount {
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .balance-stats {
            display: flex;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            flex: 1;
        }

        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .stat-value.positive {
            color: #4ade80;
        }

        .stat-value.negative {
            color: #f87171;
        }

        .transactions-section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .transaction-group {
            margin-bottom: 30px;
        }

        .date-header {
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
            margin-bottom: 12px;
            padding: 0 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transaction-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .transaction-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .transaction-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .transaction-icon.credit {
            background: linear-gradient(135deg, rgba(74, 222, 128, 0.2), rgba(74, 222, 128, 0.1));
            color: #4ade80;
        }

        .transaction-icon.debit {
            background: linear-gradient(135deg, rgba(248, 113, 113, 0.2), rgba(248, 113, 113, 0.1));
            color: #f87171;
        }

        .transaction-details {
            flex: 1;
            min-width: 0;
        }

        .transaction-title {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 4px;
        }

        .transaction-subtitle {
            font-size: 13px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .transaction-amount {
            font-size: 18px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .transaction-amount.credit {
            color: #4ade80;
        }

        .transaction-amount.debit {
            color: #f87171;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
        }

        .empty-state i {
            font-size: 64px;
            color: #4b5563;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .bank-container {
                padding: 15px;
            }

            .balance-card {
                padding: 24px;
            }

            .balance-amount {
                font-size: 36px;
            }

            .transaction-item {
                padding: 16px;
            }

            .transaction-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="bank-container">
        <!-- Cabeçalho -->
        <div class="bank-header">
            <a href="aluno.php?id=<?= $aluno['id']; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">
                <i class="fas fa-wallet"></i> Extrato
            </h1>
            <div style="width: 60px;"></div>
        </div>

        <!-- Card de Saldo -->
        <div class="balance-card">
            <div class="balance-label">Saldo Disponível</div>
            <div class="balance-amount"><?= number_format($saldo_atual, 0, ',', '.'); ?> <span style="font-size: 24px;">moedas</span></div>
            <div class="balance-stats">
                <div class="stat-item">
                    <div class="stat-label">Entradas</div>
                    <div class="stat-value positive">+<?= number_format($total_ganhos, 0, ',', '.'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Saídas</div>
                    <div class="stat-value negative">-<?= number_format($total_perdas, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- Seção de Transações -->
        <div class="transactions-section">
            <h2 class="section-title">
                <i class="fas fa-history"></i> Histórico de Transações
            </h2>

            <?php if (empty($extrato)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Nenhuma transação</h3>
                    <p>Seu histórico de transações aparecerá aqui</p>
                </div>
            <?php else: ?>
                <?php foreach ($extrato_por_data as $data => $transacoes): ?>
                    <div class="transaction-group">
                        <div class="date-header">
                            <i class="fas fa-calendar-day"></i> <?= $data; ?>
                        </div>
                        <?php foreach ($transacoes as $transacao): 
                            $hora = date('H:i', strtotime($transacao['data_transacao']));
                            $tipo = $transacao['tipo'] === 'ganho' ? 'credit' : 'debit';
                            $valor_formatado = ($transacao['tipo'] === 'ganho' ? '+' : '-') . number_format($transacao['valor'], 0, ',', '.');
                            $descricao = !empty($transacao['atitude_titulo']) ? $transacao['atitude_titulo'] : $transacao['descricao'];
                            if (!empty($transacao['motivo'])) {
                                $descricao .= ' - ' . $transacao['motivo'];
                            }
                            $icon = $transacao['tipo'] === 'ganho' ? 'fa-arrow-down' : 'fa-arrow-up';
                        ?>
                        <div class="transaction-item">
                            <div class="transaction-icon <?= $tipo; ?>">
                                <i class="fas <?= $icon; ?>"></i>
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-title"><?= htmlspecialchars($descricao); ?></div>
                                <div class="transaction-subtitle">
                                    <i class="fas fa-clock"></i> <?= $hora; ?>
                                    <?php if (!empty($transacao['professor_nome'])): ?>
                                        <span style="margin-left: 8px;">•</span>
                                        <i class="fas fa-user-tie"></i> <?= htmlspecialchars($transacao['professor_nome']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="transaction-amount <?= $tipo; ?>">
                                <?= $valor_formatado; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
