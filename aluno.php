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
$current_fundo = $aluno['fundo'] ?? 'wrapper-especial'; // Classe padrão para fundo


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
    <title>Página do Aluno</title>
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
        <div class="footer">
            <a href="missoes.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Missões</span></a>
            <a href="ranking.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Ranking</span></a>
            <a href="personalizar.php?id=<?= $aluno['id']; ?>" class="gradient-button"><span>Personalizar</span></a>
        </div>
    </div>
    </div>

</body>

</html>