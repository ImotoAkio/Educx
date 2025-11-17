<?php
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

// Passa os valores do banco às variáveis
$current_avatar = $aluno['avatar'] ?? '1.gif';
$current_fundo = $aluno['fundo'] ?? 'wrapper-especial';

// Verificar se a tabela atitudes existe e buscar atitudes ativas
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'atitudes'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if ($tableExists) {
        $stmt_atitudes = $pdo->query("SELECT * FROM atitudes WHERE status = 'ativa' ORDER BY tipo, valor_moedas DESC");
        $atitudes = $stmt_atitudes->fetchAll(PDO::FETCH_ASSOC);
        
        // Separar atitudes por tipo
        $atitudes_ganho = array_filter($atitudes, function($a) { return $a['tipo'] === 'ganho'; });
        $atitudes_perda = array_filter($atitudes, function($a) { return $a['tipo'] === 'perda'; });
    } else {
        $atitudes = [];
        $atitudes_ganho = [];
        $atitudes_perda = [];
    }
} catch (PDOException $e) {
    $atitudes = [];
    $atitudes_ganho = [];
    $atitudes_perda = [];
    $tableExists = false;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Regras do Jogo - Penalidades</title>
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="asset/fundos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background: #000000;
            color: #ffffff;
            font-family: 'Orbitron', monospace;
            min-height: 100vh;
        }

        .wrapper {
            min-height: 100vh;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header .coins {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2em;
        }

        .header .coins img {
            width: 28px;
            height: 28px;
        }

        .coin-font {
            font-family: "Press Start 2P", cursive;
            font-size: 1em;
            color: #F3E600;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.5em;
            color: #F3E600;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #aaa;
            font-size: 0.9em;
        }

        .info-banner {
            background: linear-gradient(135deg, rgba(243, 230, 0, 0.2), rgba(243, 230, 0, 0.05));
            border-left: 4px solid #F3E600;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .info-banner h3 {
            color: #F3E600;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .info-banner p {
            color: #ddd;
            line-height: 1.6;
        }

        .tabs-container {
            margin-bottom: 30px;
        }

        .filter-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #aaa;
            font-size: 0.95em;
            font-family: 'Orbitron', monospace;
        }

        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .filter-btn.active {
            color: #000;
            box-shadow: 0 4px 15px rgba(243, 230, 0, 0.3);
        }

        .filter-btn.active.ganho {
            background: linear-gradient(135deg, #00FFAB, #00cc88);
            border-color: #00FFAB;
        }

        .filter-btn.active.perda {
            background: linear-gradient(135deg, #ff6b6b, #ff4444);
            border-color: #ff6b6b;
        }

        .filter-btn.active.todas {
            background: linear-gradient(135deg, #F3E600, #ffd93d);
            border-color: #F3E600;
        }

        .tab-content {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .section-group {
            display: block;
        }

        .section-group.hidden {
            display: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            text-align: center;
            margin: 30px 0 20px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .section-header h3 {
            font-family: 'Press Start 2P', cursive;
            font-size: 1em;
            margin-bottom: 5px;
        }

        .section-header.ganho h3 {
            color: #00FFAB;
        }

        .section-header.perda h3 {
            color: #ff6b6b;
        }

        .rules-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .rule-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .rule-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .rule-info {
            flex: 1;
        }

        .rule-title {
            font-weight: 700;
            font-size: 1.1em;
            color: #ffd93d;
            margin-bottom: 8px;
        }

        .rule-description {
            color: #ccc;
            font-size: 0.9em;
            line-height: 1.5;
        }

        .rule-value {
            text-align: center;
            min-width: 120px;
        }

        .value-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1em;
            font-family: 'Press Start 2P', cursive;
        }

        .value-ganho {
            background: rgba(0, 255, 171, 0.2);
            color: #00FFAB;
            border: 2px solid #00FFAB;
        }

        .value-perda {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 2px solid #ff6b6b;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
        }

        .empty-state i {
            font-size: 4em;
            color: #555;
                margin-bottom: 20px;
            }
            
        .empty-state p {
            color: #888;
            font-size: 1.1em;
        }

        .tips-box {
            background: rgba(243, 230, 0, 0.1);
            border: 2px solid rgba(243, 230, 0, 0.3);
            border-radius: 15px;
                padding: 25px;
            margin-top: 30px;
        }

        .tips-box h3 {
            color: #F3E600;
                margin-bottom: 15px;
            font-size: 1.2em;
            font-family: 'Press Start 2P', cursive;
        }

        .tips-box ul {
            list-style: none;
            padding: 0;
        }

        .tips-box li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ddd;
            line-height: 1.6;
        }

        .tips-box li:last-child {
            border-bottom: none;
        }

        .tips-box strong {
            color: #F3E600;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .rule-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .rule-value {
                width: 100%;
                text-align: left;
            }
            
            .tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .tab {
                white-space: nowrap;
                min-width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
            <!-- Cabeçalho -->
            <div class="header">
                <a href="aluno.php?id=<?= $aluno['id']; ?>" class="gradient-button" style="text-decoration: none; min-width: 100px;">
                    <span><i class="fas fa-arrow-left"></i> Voltar</span>
                </a>
                <div class="coins">
                    <img src="asset/img/coin.gif" alt="Moeda">
                    <span class="coin-font"><?= htmlspecialchars($aluno['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <!-- Título -->
            <div class="page-title">
                <h1>💰 Regras do Jogo</h1>
                <p>Conheça as ações que podem fazer você ganhar ou perder moedas Educx</p>
        </div>

            <!-- Banner Informativo -->
            <div class="info-banner">
                <h3>ℹ️ Como Funciona</h3>
                <p>As moedas Educx são ganhas através de boas atitudes e perdidas quando você comete infrações às regras escolares. Lembre-se: o objetivo é sempre o aprendizado e a melhoria do convívio escolar.</p>
        </div>

            <!-- Atitudes -->
            <div class="tabs-container">
                <!-- ATITUDES -->
                <?php if ($tableExists && (count($atitudes_ganho) > 0 || count($atitudes_perda) > 0)): ?>
                <div id="atitudes" class="tab-content active">
                    <!-- Botões de Filtro -->
                    <div class="filter-buttons">
                        <button class="filter-btn active todas" onclick="filterAtitudes('todas')">
                            <i class="fas fa-list"></i> Todas
                        </button>
                        <?php if (count($atitudes_ganho) > 0): ?>
                        <button class="filter-btn ganho" onclick="filterAtitudes('ganho')">
                            <i class="fas fa-arrow-up"></i> Positivas
                        </button>
                        <?php endif; ?>
                        <?php if (count($atitudes_perda) > 0): ?>
                        <button class="filter-btn perda" onclick="filterAtitudes('perda')">
                            <i class="fas fa-arrow-down"></i> Negativas
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Seção de Atitudes Positivas -->
                    <?php if (count($atitudes_ganho) > 0): ?>
                        <div class="section-group" id="section-ganho">
                            <div class="section-header ganho">
                                <h3><i class="fas fa-arrow-up"></i> Atitudes Positivas</h3>
                                <p>Ganhe moedas com boas ações!</p>
                            </div>
                            <div class="rules-grid">
                                <?php foreach ($atitudes_ganho as $atitude): ?>
                                <div class="rule-item">
                                    <div class="rule-info">
                                        <div class="rule-title"><?= htmlspecialchars($atitude['titulo']); ?></div>
                                        <div class="rule-description"><?= htmlspecialchars($atitude['descricao'] ?? 'Sem descrição'); ?></div>
                                    </div>
                                    <div class="rule-value">
                                        <span class="value-badge value-ganho">+<?= $atitude['valor_moedas']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Seção de Atitudes Negativas -->
                    <?php if (count($atitudes_perda) > 0): ?>
                        <div class="section-group" id="section-perda">
                            <div class="section-header perda">
                                <h3><i class="fas fa-arrow-down"></i> Atitudes Negativas</h3>
                                <p>Evite perder moedas!</p>
                            </div>
                            <div class="rules-grid">
                                <?php foreach ($atitudes_perda as $atitude): ?>
                                <div class="rule-item">
                                    <div class="rule-info">
                                        <div class="rule-title"><?= htmlspecialchars($atitude['titulo']); ?></div>
                                        <div class="rule-description"><?= htmlspecialchars($atitude['descricao'] ?? 'Sem descrição'); ?></div>
                                    </div>
                                    <div class="rule-value">
                                        <span class="value-badge value-perda">-<?= $atitude['valor_moedas']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhuma atitude cadastrada no momento.</p>
                        <p style="margin-top: 10px; font-size: 0.9em; color: #888;">As atitudes serão exibidas aqui quando forem cadastradas pela secretaria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dicas -->
            <div class="tips-box">
            <h3>💡 Dicas Importantes</h3>
            <ul>
                <li><strong>Arrependimento:</strong> Se você se arrepender e demonstrar mudança de comportamento, o professor pode reduzir ou cancelar a penalidade.</li>
                <li><strong>Reincidência:</strong> Comportamentos repetidos podem ter penalidades maiores.</li>
                <li><strong>Contexto:</strong> O professor sempre considera o contexto e as circunstâncias antes de aplicar uma penalidade.</li>
                <li><strong>Recuperação:</strong> Lembre-se que você sempre pode recuperar moedas através de boas ações e missões!</li>
            </ul>
        </div>
        </div>
    </div>

    <script>
        function filterAtitudes(tipo) {
            // Remover active de todos os botões
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Adicionar active ao botão clicado
            event.target.classList.add('active');
            
            // Mostrar/ocultar seções
            const sectionGanho = document.getElementById('section-ganho');
            const sectionPerda = document.getElementById('section-perda');
            
            if (tipo === 'todas') {
                if (sectionGanho) sectionGanho.classList.remove('hidden');
                if (sectionPerda) sectionPerda.classList.remove('hidden');
            } else if (tipo === 'ganho') {
                if (sectionGanho) sectionGanho.classList.remove('hidden');
                if (sectionPerda) sectionPerda.classList.add('hidden');
            } else if (tipo === 'perda') {
                if (sectionGanho) sectionGanho.classList.add('hidden');
                if (sectionPerda) sectionPerda.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
