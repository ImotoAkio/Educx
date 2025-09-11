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

// Passa os valores do banco às variáveis
$current_avatar = $aluno['avatar'] ?? '1.gif';
$current_fundo = $aluno['fundo'] ?? 'wrapper-especial'; // Classe padrão para fundo

// ===== SISTEMA DE PRESENÇA PARA APP =====

// Função para verificar se é dia útil (não fim de semana)
function isDiaUtil($data) {
    $diaSemana = date('N', strtotime($data)); // 1 = Segunda, 7 = Domingo
    return $diaSemana >= 1 && $diaSemana <= 5; // Segunda a Sexta
}

// Função para verificar se está dentro do horário permitido (até 7:30)
function isHorarioValido($hora) {
    try {
        // Criar objetos DateTime com timezone explícito
        $horaAtual = new DateTime($hora, new DateTimeZone('America/Sao_Paulo'));
        $limite = new DateTime('07:30:00', new DateTimeZone('America/Sao_Paulo'));
        
        // Comparar apenas hora e minuto (ignorar segundos)
        $horaAtualFormatada = $horaAtual->format('H:i');
        $limiteFormatado = $limite->format('H:i');
        
        return $horaAtualFormatada <= $limiteFormatado;
    } catch (Exception $e) {
        // Fallback para método simples em caso de erro
        $horaAtual = new DateTime($hora);
        $limite = new DateTime('07:30:00');
        return $horaAtual <= $limite;
    }
}

// Função para verificar se os últimos 5 dias foram todos no horário
function verificarStreak5DiasNoHorario($pdo, $alunoId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as dias_no_horario
        FROM presencas 
        WHERE aluno_id = :aluno_id 
        AND data_presenca >= DATE_SUB(CURDATE(), INTERVAL 4 DAY)
        AND data_presenca <= CURDATE()
        AND TIME(hora_presenca) <= '07:30:00'
    ");
    $stmt->execute([':aluno_id' => $alunoId]);
    $diasNoHorario = $stmt->fetch(PDO::FETCH_ASSOC)['dias_no_horario'];
    
    return $diasNoHorario == 5;
}

// Função para calcular streak atual
function calcularStreak($pdo, $aluno_id) {
    try {
        // Buscar todas as presenças ordenadas por data (mais recente primeiro)
        $stmt = $pdo->prepare("
            SELECT data_presenca, streak_atual 
            FROM presencas 
            WHERE aluno_id = :aluno_id 
            ORDER BY data_presenca DESC 
            LIMIT 30
        ");
        $stmt->execute([':aluno_id' => $aluno_id]);
        $presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($presencas)) {
            return 0;
        }
        
        $streak = 0;
        $dataAtual = new DateTime();
        
        foreach ($presencas as $presenca) {
            $dataPresenca = new DateTime($presenca['data_presenca']);
            $diferencaDias = $dataAtual->diff($dataPresenca)->days;
            
            // Se for hoje ou ontem (considerando apenas dias úteis)
            if ($diferencaDias <= 1 && isDiaUtil($presenca['data_presenca'])) {
                $streak++;
                $dataAtual = $dataPresenca;
            } else {
                break;
            }
        }
        
        return $streak;
    } catch (PDOException $e) {
        return 0;
    }
}

// Processar presença quando a página é carregada (APENAS PARA APP)
$mostrarModal = false;
$dadosModal = [];

try {
    $dataHoje = date('Y-m-d');
    $horaAtual = date('H:i:s');
    $horaFormatada = date('H:i');
    
    // Verificar se já houve presença hoje
    $stmt = $pdo->prepare("SELECT id FROM presencas WHERE aluno_id = :aluno_id AND data_presenca = :data_hoje");
    $stmt->execute([
        ':aluno_id' => $id,
        ':data_hoje' => $dataHoje
    ]);
    $presencaExistente = $stmt->fetch();
    
    if (!$presencaExistente) {
        // Verificar se é dia útil
        if (isDiaUtil($dataHoje)) {
            // SEMPRE registrar presença, independente do horário
            $streakAtual = calcularStreak($pdo, $id) + 1;
            
            // Inserir presença
            $stmt = $pdo->prepare("
                INSERT INTO presencas (aluno_id, data_presenca, hora_presenca, streak_atual) 
                VALUES (:aluno_id, :data_presenca, :hora_presenca, :streak_atual)
            ");
            $stmt->execute([
                ':aluno_id' => $id,
                ':data_presenca' => $dataHoje,
                ':hora_presenca' => $horaAtual,
                ':streak_atual' => $streakAtual
            ]);
            
            // Atualizar streak máximo se necessário
            $stmt = $pdo->prepare("
                UPDATE presencas 
                SET streak_maximo = GREATEST(streak_maximo, :streak_atual) 
                WHERE aluno_id = :aluno_id
            ");
            $stmt->execute([
                ':streak_atual' => $streakAtual,
                ':aluno_id' => $id
            ]);
            
            // Calcular XP baseado no horário e streak
            $xpGanho = 0;
            $xpBonusStreak = 0;
            $mensagemBonus = '';
            
            if (isHorarioValido($horaAtual)) {
                // Dentro do horário - ganha 1 XP base
                $xpGanho = 1;
            }
            
            // Verificar bônus de streak de 5 dias (apenas se todos os 5 dias foram no horário)
            if ($streakAtual == 5) {
                // Verificar se os últimos 5 dias foram todos no horário E hoje também está no horário
                if (verificarStreak5DiasNoHorario($pdo, $id) && isHorarioValido($horaAtual)) {
                    $xpBonusStreak = 5;
                    $mensagemBonus = "🎊 PARABÉNS! Você completou 5 dias consecutivos NO HORÁRIO! +5 XP de bônus!";
                } else {
                    $mensagemBonus = "🎊 Você completou 5 dias consecutivos, mas nem todos foram no horário. Continue tentando!";
                }
            }
            
            // Calcular XP total
            $xpTotal = $xpGanho + $xpBonusStreak;
            
            // Adicionar XP ao aluno se houver algum
            if ($xpTotal > 0) {
                $stmt = $pdo->prepare("UPDATE alunos SET xp_total = xp_total + :xp WHERE id = :id");
                $stmt->execute([':xp' => $xpTotal, ':id' => $id]);
            }
            
            // Buscar streak máximo
            $stmt = $pdo->prepare("
                SELECT MAX(streak_maximo) as streak_maximo 
                FROM presencas 
                WHERE aluno_id = :aluno_id
            ");
            $stmt->execute([':aluno_id' => $id]);
            $streakMaximo = $stmt->fetch(PDO::FETCH_ASSOC)['streak_maximo'] ?? $streakAtual;
            
            // Configurar modal baseado no horário e streak
            if ($streakAtual == 5 && $xpBonusStreak > 0) {
                // Streak de 5 dias NO HORÁRIO - modal especial com bônus
                $mostrarModal = true;
                $dadosModal = [
                    'titulo' => '🎊 STREAK DE 5 DIAS NO HORÁRIO!',
                    'mensagem' => "INCRÍVEL! Você completou 5 dias consecutivos de presença NO HORÁRIO!",
                    'streak_atual' => $streakAtual,
                    'streak_maximo' => $streakMaximo,
                    'xp_ganho' => $xpGanho,
                    'xp_bonus' => $xpBonusStreak,
                    'xp_total' => $xpTotal,
                    'mensagem_bonus' => $mensagemBonus,
                    'tipo' => 'streak',
                    'hora_registro' => $horaFormatada,
                    'dentro_horario' => isHorarioValido($horaAtual)
                ];
            } elseif ($streakAtual == 5 && $xpBonusStreak == 0) {
                // Streak de 5 dias mas não todos no horário - modal informativo
                $mostrarModal = true;
                $dadosModal = [
                    'titulo' => '🎊 STREAK DE 5 DIAS!',
                    'mensagem' => "Parabéns! Você completou 5 dias consecutivos de presença!",
                    'streak_atual' => $streakAtual,
                    'streak_maximo' => $streakMaximo,
                    'xp_ganho' => $xpGanho,
                    'xp_bonus' => $xpBonusStreak,
                    'xp_total' => $xpTotal,
                    'mensagem_bonus' => $mensagemBonus,
                    'tipo' => 'info',
                    'hora_registro' => $horaFormatada,
                    'dentro_horario' => isHorarioValido($horaAtual)
                ];
            } elseif (isHorarioValido($horaAtual)) {
                // Dentro do horário
                $mostrarModal = true;
                $dadosModal = [
                    'titulo' => '🎉 Presença Registrada!',
                    'mensagem' => "Parabéns! Sua presença foi registrada com sucesso às {$horaFormatada}.",
                    'streak_atual' => $streakAtual,
                    'streak_maximo' => $streakMaximo,
                    'xp_ganho' => $xpGanho,
                    'xp_bonus' => $xpBonusStreak,
                    'xp_total' => $xpTotal,
                    'mensagem_bonus' => $mensagemBonus,
                    'tipo' => 'sucesso',
                    'hora_registro' => $horaFormatada
                ];
            } else {
                // Fora do horário, mas presença registrada
                $mostrarModal = true;
                $dadosModal = [
                    'titulo' => '⏰ Presença Registrada (Atrasada)',
                    'mensagem' => "Sua presença foi registrada às {$horaFormatada}, mas o horário limite é 7:30.",
                    'streak_atual' => $streakAtual,
                    'streak_maximo' => $streakMaximo,
                    'xp_ganho' => $xpGanho,
                    'xp_bonus' => $xpBonusStreak,
                    'xp_total' => $xpTotal,
                    'mensagem_bonus' => $mensagemBonus,
                    'tipo' => 'aviso',
                    'hora_registro' => $horaFormatada
                ];
            }
        } else {
            // Fim de semana - não registra presença
            $mostrarModal = true;
            $dadosModal = [
                'titulo' => '📅 Fim de Semana',
                'mensagem' => "Hoje é fim de semana ({$horaFormatada}). Presenças só contam de segunda a sexta.",
                'streak_atual' => calcularStreak($pdo, $id),
                'streak_maximo' => 0,
                'xp_ganho' => 0,
                'tipo' => 'info',
                'hora_registro' => $horaFormatada
            ];
        }
    } else {
        // Presença já registrada hoje - não mostra modal
        $mostrarModal = false;
    }
    
} catch (PDOException $e) {
    $mostrarModal = true;
    $dadosModal = [
        'titulo' => '❌ Erro',
        'mensagem' => 'Erro ao registrar presença: ' . $e->getMessage(),
        'streak_atual' => 0,
        'streak_maximo' => 0,
        'xp_ganho' => 0,
        'tipo' => 'erro',
        'hora_registro' => date('H:i')
    ];
}

// ===== FIM SISTEMA DE PRESENÇA =====

function calcularNivelEProgresso($xp_total)
{
    $nivel = 1;
    $xp_para_proximo_nivel = 499; // XP inicial necessário para o nível 1
    $titulo_nivel = "Iniciante"; // Título padrão

    // Determina o título do nível com base no XP total
    if ($xp_total >= 2000) {
        $titulo_nivel = "Líder";
        $xp_para_proximo_nivel = 0; // Sem próximo nível definido
    } elseif ($xp_total >= 1000) {
        $titulo_nivel = "Guardião";
        $xp_para_proximo_nivel = 1999;
    } elseif ($xp_total >= 500) {
        $titulo_nivel = "Explorador";
        $xp_para_proximo_nivel = 999;
    }

    // Calcula o progresso dentro do nível atual
    $xp_atual_no_nivel = $xp_total % ($xp_para_proximo_nivel + 1);

    return [
        'nivel' => $nivel,
        'titulo_nivel' => $titulo_nivel,
        'xp_atual_no_nivel' => $xp_atual_no_nivel,
        'xp_para_proximo_nivel' => $xp_para_proximo_nivel
    ];
}

// Dados do banco
$xp_total = (int) $aluno['xp_total'];

// Calcular nível e progresso
$dados = calcularNivelEProgresso($xp_total);
$nivel_atual = $dados['nivel'];
$titulo_nivel = $dados['titulo_nivel'];
$xp_atual_no_nivel = $dados['xp_atual_no_nivel'];
$xp_para_proximo_nivel = $dados['xp_para_proximo_nivel'];

// Calcular progresso
$progresso = ($xp_para_proximo_nivel > 0) ? ($xp_atual_no_nivel / $xp_para_proximo_nivel) : 1;
$progresso_percentual = round($progresso * 100, 2);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="asset/loja.css">-->
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="asset/fundos.css">
    <link rel="stylesheet" href="asset/style.css">
    <title>Página do Aluno - App</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

        *,
        *:before,
        *:after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #17171f;
            color: white;
            text-align: center;
        }

        .container {
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .header .coins {
            display: flex;
            align-items: center;
            font-size: 1.2em;
        }

        .header .coins img {
            width: 24px;
            margin-right: 10px;
        }

        .content h1 {
            margin-top: 20px;
            font-size: 2em;
            font-family: "Press Start 2P", cursive;
            /* Fonte aplicada diretamente ao título */
        }

        .avatar {
            margin: 30px auto;
            width: 200px;
            position: relative;
        }

        .avatar img {
            width: 100%;
        }

        .level {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: #00FFAB;
            border-radius: 50px;
            padding: 5px 15px;
            color: #000;
            font-size: 1em;
            font-weight: bold;
        }

        .range {
            position: relative;
            background-color: #333;
            width: 300px;
            height: 30px;
            margin: 20px auto;
            transform: skew(30deg);
            font-family: 'Orbitron', monospace;

        }

        .range-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #F3E600;
            z-index: 0;
            transition: width 0.3s ease;
            /* Para suavizar a animação do progresso */
        }

        .range:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #F3E600;
            width: calc(<?= $progresso_percentual; ?>%);
            z-index: 0;
        }

        .range:after {
            content: '<?= round($progresso_percentual); ?>%';
            color: #000;
            position: absolute;
            left: 5%;
            top: 50%;
            transform: translateY(-50%) skewX(-30deg);
            z-index: 1;
        }

        .range__label {
            transform: skew(-30deg) translateY(-100%);
            line-height: 1.5;
            font-size: 20px;

        }

        .rosa .range__label p {
            font-size: 20px;
            color: black;
            /* Cor preta quando o fundo é rosa */
        }

        .cubos .range__label {
            font-size: 20px;
            color: Gold;
            /* Cor dourada quando o fundo é cubos */
        }

        .footer {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-direction: column;
            align-items: center;
        }

        .coin-font {
            font-family: "Press Start 2P", cursive;
            /* Altere a fonte aqui */
            font-size: 1.5em;
            /* Ajuste o tamanho da fonte */
            color: gold;
            /* Altere a cor, se necessário */
        }

        .especial {
            width: 100%;
            /* Preenche a largura total */
            height: 100vh;
            /* Preenche toda a altura da viewport */
            margin: 0 auto;
            /* Centralizado */
            background: #17171f;
            border-radius: 0;
            /* Remove bordas arredondadas para preencher a tela */
            overflow: hidden;
            position: relative;
            z-index: 1;
            background-image: url('asset/img/.png'), url('asset/img/1.png'), url('asset/img/2.png');
            background-size: cover;
            animation: especial 1000s linear infinite;
        }

        /* Ajustes para dispositivos desktop */
        @media (min-width: 1024px) {
            .especial {
                width: 420px;
                /* Simula o tamanho de um celular em desktops */
                height: 720px;
                /* Altura fixa semelhante à de celulares */
                margin: 40px auto;
                /* Centraliza horizontalmente e cria espaço acima/abaixo */
                border-radius: 45px;
                /* Borda arredondada como design para desktop */
            }
        }

        /* Animação das moedas */
        @keyframes especial {
            0% {
                background-position: 0px 0px, 0px 0px, 0px 0px;
            }

            100% {
                background-position: 50000px 50000px, 10000px 20000px, -10000px 15000px;
            }
        }

        .footer-full-btn {
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        /* Estilos para a Modal de Presença */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 3px solid #00FFAB;
            border-radius: 20px;
            padding: 30px;
            max-width: 350px;
            width: 90%;
            text-align: center;
            position: relative;
            animation: modalSlideIn 0.5s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-titulo {
            font-family: "Press Start 2P", cursive;
            font-size: 1em;
            margin-bottom: 20px;
            color: #00FFAB;
        }

        .modal-mensagem {
            font-family: "Press Start 2P", cursive;
            font-size: 0.6em;
            margin-bottom: 20px;
            line-height: 1.4;
            color: #fff;
        }

        .streak-info {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 15px;
            background: rgba(0, 255, 171, 0.1);
            border-radius: 10px;
        }

        .streak-item {
            text-align: center;
        }

        .streak-number {
            font-family: "Press Start 2P", cursive;
            font-size: 1.2em;
            color: #00FFAB;
            display: block;
        }

        .streak-label {
            font-family: "Press Start 2P", cursive;
            font-size: 0.5em;
            color: #ccc;
            margin-top: 5px;
        }

        .xp-info {
            font-family: "Press Start 2P", cursive;
            font-size: 0.6em;
            color: #F3E600;
            margin: 10px 0;
        }

        .modal-btn {
            background: linear-gradient(45deg, #00FFAB, #00D4AA);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-family: "Press Start 2P", cursive;
            font-size: 0.6em;
            color: #000;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .modal-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 255, 171, 0.4);
        }

        .modal-tipo-sucesso {
            border-color: #00FFAB;
        }

        .modal-tipo-aviso {
            border-color: #FFA500;
        }

        .modal-tipo-info {
            border-color: #4A90E2;
        }

        .modal-tipo-erro {
            border-color: #FF4444;
        }

        .modal-tipo-streak {
            border-color: #FFD700;
            border-width: 4px;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f1419);
            animation: streakGlow 2s ease-in-out infinite alternate;
        }

        @keyframes streakGlow {
            from {
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            }
            to {
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            }
        }
    </style>
</head>

<body>

    <div class="<?= htmlspecialchars($current_fundo); ?>">
        <!-- Cabeçalho -->
        <header>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-4-sm center">
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid">
            <!-- Cabeçalho -->
            <div class="header">
                <div class="coins">
                    <img src="asset/img/coin.gif" alt="Moeda">
                    <span class="coin-font"><?= htmlspecialchars($aluno['moedas'], ENT_QUOTES, 'UTF-8'); ?></span>

                </div>

                <a href="loja.php?id=<?= $aluno['id']; ?>" class="btn-link">
                    <button type="button" class="btn">
                        <strong>LOJA</strong>
                        <div id="container-stars">
                            <div id="stars"></div>
                        </div>
                        <div id="glow">
                            <div class="circle"></div>
                            <div class="circle"></div>
                        </div>
                    </button>
                </a>
            </div>

            <!-- Conteúdo -->
            <div class="content">
                <h1 class="ola">Olá, <?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8'); ?>!</h1>

                <!-- Exibição do avatar atual -->
                <div class="avatar">
                    <img src="<?= htmlspecialchars($current_avatar); ?>" alt="Avatar do aluno <?= htmlspecialchars($aluno['nome']); ?>">
                </div>
                <br>

                <div class="range">
                    <div class="range-bar" style="width: <?= $progresso_percentual; ?>%;"></div>
                    <div class="range__label">
                        <p><?= htmlspecialchars($titulo_nivel); ?></p>
                    </div>
                </div>

            </div>

        </div>

        <!-- Rodapé -->
        <div class="footer" style="flex-direction: column; align-items: center;">
            <div style="display: flex; gap: 10px;">
                <a href="missoes.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Missões</span></a>
                <a href="ranking.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Ranking</span></a>
                <a href="personalizar.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Personalizar</span></a>
            </div>
            <a href="regras.php?id=<?= $aluno['id']; ?>" class="gradient-button footer-full-btn" style="margin-top: 10px;"><span>Regras</span></a>
        </div>
    </div>

    <!-- Modal de Presença (APENAS PARA APP) -->
    <?php if ($mostrarModal): ?>
    <div class="modal-overlay" id="modalPresenca">
        <div class="modal-content modal-tipo-<?= $dadosModal['tipo'] ?>">
            <h2 class="modal-titulo"><?= htmlspecialchars($dadosModal['titulo']) ?></h2>
            <p class="modal-mensagem"><?= htmlspecialchars($dadosModal['mensagem']) ?></p>
            
            <!-- Horário de registro -->
            <div class="hora-info" style="font-family: 'Press Start 2P', cursive; font-size: 0.6em; color: #00FFAB; margin: 15px 0; padding: 10px; background: rgba(0, 255, 171, 0.1); border-radius: 10px;">
                🕐 Horário: <?= htmlspecialchars($dadosModal['hora_registro']) ?>
            </div>
            
            <?php if ($dadosModal['streak_atual'] > 0): ?>
            <div class="streak-info">
                <div class="streak-item">
                    <span class="streak-number"><?= $dadosModal['streak_atual'] ?></span>
                    <span class="streak-label">Streak Atual</span>
                </div>
                <div class="streak-item">
                    <span class="streak-number"><?= $dadosModal['streak_maximo'] ?></span>
                    <span class="streak-label">Máximo</span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Mensagem de bônus de streak -->
            <?php if (!empty($dadosModal['mensagem_bonus'])): ?>
            <div class="bonus-info" style="font-family: 'Press Start 2P', cursive; font-size: 0.5em; color: #FFD700; margin: 15px 0; padding: 15px; background: rgba(255, 215, 0, 0.2); border: 2px solid #FFD700; border-radius: 10px; text-align: center;">
                <?= htmlspecialchars($dadosModal['mensagem_bonus']) ?>
            </div>
            <?php endif; ?>
            
            <!-- Informações de XP -->
            <?php if ($dadosModal['xp_total'] > 0): ?>
            <div class="xp-info" style="font-family: 'Press Start 2P', cursive; font-size: 0.6em; color: #F3E600; margin: 10px 0; padding: 10px; background: rgba(243, 230, 0, 0.1); border-radius: 10px;">
                <?php if ($dadosModal['xp_ganho'] > 0 && $dadosModal['xp_bonus'] > 0): ?>
                    🎯 +<?= $dadosModal['xp_ganho'] ?> XP (presença) + <?= $dadosModal['xp_bonus'] ?> XP (bônus) = <?= $dadosModal['xp_total'] ?> XP total!
                <?php elseif ($dadosModal['xp_ganho'] > 0): ?>
                    🎯 +<?= $dadosModal['xp_ganho'] ?> XP ganho!
                <?php elseif ($dadosModal['xp_bonus'] > 0): ?>
                    🎯 +<?= $dadosModal['xp_bonus'] ?> XP de bônus!
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <button class="modal-btn" onclick="fecharModal()">Continuar</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function fecharModal() {
            document.getElementById('modalPresenca').style.display = 'none';
        }

        // Fechar modal clicando fora dela
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('modalPresenca');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('modalPresenca');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>

</body>

</html>
