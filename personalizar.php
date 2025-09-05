<?php
// Inclui o arquivo de conexão com o banco de dados
include 'db.php';

// ID do aluno (deve ser obtido da sessão ou URL)
$aluno_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Verifica se o ID foi fornecido
if (!$aluno_id) {
    die("ID do aluno não fornecido.");
}

// Função para calcular nível baseado no XP (usando a mesma lógica de aluno.php)
function calcularNivelEProgresso($xp_total) {
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

// Função para obter nível numérico para desbloqueio de avatares
function calcularNivelParaAvatares($xp_total) {
    if ($xp_total >= 2000) return 4; // Líder
    if ($xp_total >= 1000) return 3; // Guardião
    if ($xp_total >= 500) return 2;  // Explorador
    return 1; // Iniciante
}

// Obtém dados do aluno
$stmt = $pdo->prepare("SELECT id, nome, xp_total, moedas, avatar, fundo FROM alunos WHERE id = :id");
$stmt->execute([':id' => $aluno_id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado.");
}

$nivel_atual = calcularNivelParaAvatares($aluno['xp_total']);
$dados_nivel = calcularNivelEProgresso($aluno['xp_total']);
$titulo_nivel = $dados_nivel['titulo_nivel'];

// Lista de fundos disponíveis (nomes das classes CSS)
$fundos = ['cubos', 'rosa', 'dark', 'wave', 'especial'];

// Atualiza avatar ou fundo no banco de dados (somente via POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    // Atualizar avatar
    if (isset($_POST['avatar'])) {
        $avatar_id = (int) $_POST['avatar'];
        
        // Verificar se o aluno tem acesso ao avatar
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   CASE WHEN aa.aluno_id IS NOT NULL THEN 1 ELSE 0 END as desbloqueado
            FROM avatares a 
            LEFT JOIN avatares_alunos aa ON a.id = aa.avatar_id AND aa.aluno_id = :aluno_id
            WHERE a.id = :avatar_id
        ");
        $stmt->execute([':avatar_id' => $avatar_id, ':aluno_id' => $aluno_id]);
        $avatar_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$avatar_info) {
            $response['message'] = 'Avatar não encontrado.';
        } elseif ($avatar_info['desbloqueado'] || $nivel_atual >= $avatar_info['nivel_requerido']) {
            // Se não está desbloqueado mas o aluno tem nível suficiente, desbloqueia
            if (!$avatar_info['desbloqueado']) {
                $stmt = $pdo->prepare("INSERT INTO avatares_alunos (aluno_id, avatar_id, metodo_desbloqueio) VALUES (:aluno_id, :avatar_id, 'nivel')");
                $stmt->execute([':aluno_id' => $aluno_id, ':avatar_id' => $avatar_id]);
            }
            
            // Atualiza o avatar do aluno
            $stmt = $pdo->prepare("UPDATE alunos SET avatar = :avatar WHERE id = :id");
            $stmt->execute([':avatar' => 'asset/img/avatar/' . $avatar_info['arquivo'], ':id' => $aluno_id]);
            $response['success'] = true;
            $response['message'] = 'Avatar atualizado com sucesso!';
        } else {
            $response['message'] = 'Você precisa de nível ' . $avatar_info['nivel_requerido'] . ' para usar este avatar.';
        }
    }

    // Atualizar fundo
    if (isset($_POST['fundo']) && in_array($_POST['fundo'], $fundos)) {
        $stmt = $pdo->prepare("UPDATE alunos SET fundo = :fundo WHERE id = :id");
        $stmt->execute([':fundo' => $_POST['fundo'], ':id' => $aluno_id]);
        $response['success'] = true;
        $response['message'] = 'Fundo atualizado com sucesso!';
    }

    echo json_encode($response);
    exit;
}

// Obtém o avatar e fundo atuais do aluno
$current_avatar = $aluno['avatar'] ?? 'asset/img/avatar/default.gif';
$current_fundo = $aluno['fundo'] ?? 'wrapper-snow';

// Buscar todos os avatares com informações de desbloqueio
$stmt = $pdo->prepare("
    SELECT a.*, 
           CASE WHEN aa.aluno_id IS NOT NULL THEN 1 ELSE 0 END as desbloqueado,
           CASE WHEN a.nivel_requerido <= :nivel_atual THEN 1 ELSE 0 END as nivel_suficiente
    FROM avatares a 
    LEFT JOIN avatares_alunos aa ON a.id = aa.avatar_id AND aa.aluno_id = :aluno_id
    WHERE a.disponivel = 1
    ORDER BY a.nivel_requerido ASC, a.xp_requerido ASC
");
$stmt->execute([':nivel_atual' => $nivel_atual, ':aluno_id' => $aluno_id]);
$avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizar Avatar e Fundo</title>
    <link rel="stylesheet" href="asset/fundos.css">
    <link rel="stylesheet" href="asset/loja.css">

    <!-- Inclusão do jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJ+Y3d5v7HCK2AqjygPuQevTWUL3w7j7+oqpE="
        crossorigin="anonymous"></script>

    <script>
        async function savePreference(type, value) {
            const formData = new FormData();
            formData.append(type, value);

            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                // Mostrar mensagem de sucesso
                if (result.message) {
                    alert(result.message);
                }
                // Redireciona para aluno.php com o ID do aluno
                const alunoId = <?= json_encode($aluno_id); ?>;
                window.location.href = `aluno.php?id=${alunoId}`;
            } else {
                alert(result.message || 'Erro ao salvar. Tente novamente.');
            }
        }
        
        function showAvatarInfo(avatarId, nome, nivel, xp, categoria, desbloqueado, nivelSuficiente) {
            let mensagem = `Avatar: ${nome}\n`;
            mensagem += `Categoria: ${categoria}\n`;
            mensagem += `Requisito: Nível ${nivel} (${xp} XP)\n\n`;
            
            if (desbloqueado == 1) {
                mensagem += '✅ DESBLOQUEADO!\nClique para usar.';
            } else if (nivelSuficiente == 1) {
                mensagem += '🔓 DISPONÍVEL!\nClique para desbloquear e usar.';
            } else {
                mensagem += '🔒 BLOQUEADO\nContinue ganhando XP para desbloquear!';
            }
            
            alert(mensagem);
        }
        
        $(document).ready(function() {
            $("a").on("click", function(e) {
                e.preventDefault();
            });
        });
    </script>
</head>
<style>
    .voltar {
        margin-bottom: 15px;
        font: inherit;
        background-color: #f0f0f0;
        border: 0;
        color: #242424;
        border-radius: 0.5em;
        font-size: 1.35rem;
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


    .content-img {
        width: 100px;
        height: auto;
        margin-top: -10px;
        margin-bottom: 10px;
        flex: 0 0 100px;

        img {
            width: 100%;
            max-width: 75px;
            height: auto;
            margin: 0 auto;
            display: block;
        }
    }

    .naime {
        font-family: var(--font-family);
        font-size: 14px;
        font-weight: 600;
        margin: 0;
        color: var(--color-light);
    }
    
    /* Estilos para o sistema de avatares */
    .nivel-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
    }
    
    .nivel-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .xp-info {
        color: #666;
        font-size: 11px;
    }
    
    .product.unlocked {
        border: 2px solid #28a745;
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    .product.available {
        border: 2px solid #ffc107;
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
    }
    
    .product.locked {
        border: 2px solid #dc3545;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        opacity: 0.7;
    }
    
    .product.locked:hover {
        opacity: 1;
        transform: scale(1.02);
    }
    
    .grayscale {
        filter: grayscale(100%);
    }
    
    .avatar-status {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    
    .status-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: bold;
        color: white;
    }
    
    .status-badge.unlocked {
        background: #28a745;
    }
    
    .status-badge.available {
        background: #ffc107;
        color: #333;
    }
    
    .status-badge.locked {
        background: #dc3545;
    }
    
    .avatar-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin: 5px 0;
    }
    
    .categoria-badge {
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: bold;
        color: white;
        text-align: center;
    }
    
    .categoria-badge.comum {
        background: #6c757d;
    }
    
    .categoria-badge.raro {
        background: #007bff;
    }
    
    .categoria-badge.epico {
        background: #6f42c1;
    }
    
    .categoria-badge.lendario {
        background: #fd7e14;
    }
    
    .requisito {
        font-size: 10px;
        color: #666;
    }
    
    .preco {
        font-size: 12px;
        font-weight: bold;
        color: #28a745;
    }
</style>

<body>


    <div id="wrapper">
        <header>
            <div class="container-fluid">
                <div class="row">

                    <div class="col-4-sm center">
                        <h1 class="page-title">Personalizar</h1>
                    </div>
                    <div class="col-4-sm right">
                        <button type="button" class="voltar" onclick="window.location.href='aluno.php?id=<?php echo urlencode($aluno_id); ?>'">
                            <
                                </button>
                    </div>
                </div>
            </div>
        </header>
        <section>
            <div class="container-fluid">
                <!-- HERO CARD -->
                <div class="row">
                    <div class="col-12">
                        <div class="hero-card">
                            <div class="content-image">
                                <img src="<?= htmlspecialchars($current_avatar); ?>" alt="">
                            </div>
                            <div class="card-content">
                                <h3>Personalize sua</h3>
                                <h3> página!</h3>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- Category title -->
                <div class="row margin-vertical">
                    <div class="col-6-sm">
                        <h3 class="segment-title left">Escolha seu avatar</h3>
                    </div>
                    <div class="col-6-sm right">
                        <div class="nivel-info">
                            <span class="nivel-badge">Nível <?= $nivel_atual; ?> - <?= $titulo_nivel; ?></span>
                            <span class="xp-info"><?= $aluno['xp_total']; ?> XP</span>
                        </div>
                    </div>
                </div>
                <!-- Products grid -->
                <div class="row">
                    <?php foreach ($avatares as $avatar): ?>
                        <div class="col-6-sm">
                            <div class="product <?= $avatar['desbloqueado'] ? 'unlocked' : ($avatar['nivel_suficiente'] ? 'available' : 'locked'); ?>" 
                                 onclick="<?= $avatar['desbloqueado'] || $avatar['nivel_suficiente'] ? 'savePreference(\'avatar\', \'' . $avatar['id'] . '\')' : 'showAvatarInfo(' . $avatar['id'] . ', \'' . addslashes($avatar['nome']) . '\', ' . $avatar['nivel_requerido'] . ', ' . $avatar['xp_requerido'] . ', \'' . $avatar['categoria'] . '\', ' . $avatar['desbloqueado'] . ', ' . $avatar['nivel_suficiente'] . ')' ?>">
                                <img src="asset/img/avatar/<?= htmlspecialchars($avatar['arquivo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="Avatar <?= htmlspecialchars($avatar['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                     class="<?= $avatar['desbloqueado'] ? '' : 'grayscale'; ?>">
                                
                                <!-- Indicador de status -->
                                <div class="avatar-status">
                                    <?php if ($avatar['desbloqueado']): ?>
                                        <span class="status-badge unlocked">✅ Desbloqueado</span>
                                    <?php elseif ($avatar['nivel_suficiente']): ?>
                                        <span class="status-badge available">🔓 Disponível</span>
                                    <?php else: ?>
                                        <span class="status-badge locked">🔒 Nível <?= $avatar['nivel_requerido']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="detail">
                                    <h4 class="naime"><?= htmlspecialchars($avatar['nome']); ?></h4>
                                    <div class="avatar-info">
                                        <span class="categoria-badge <?= $avatar['categoria']; ?>"><?= ucfirst($avatar['categoria']); ?></span>
                                        <span class="requisito">Nível <?= $avatar['nivel_requerido']; ?> (<?= $avatar['xp_requerido']; ?> XP)</span>
                                    </div>
                                    <div class="detail-footer">
                                        <?php if ($avatar['preco_moedas'] > 0): ?>
                                            <span class="preco"><?= $avatar['preco_moedas']; ?> 🪙</span>
                                        <?php else: ?>
                                            <span class="preco">Grátis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="star">
                                    <img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Star">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>


                </div>
                <!-- Category title -->
                <div class="row margin-vertical">
                    <div class="col-6-sm">
                        <h3 class="segment-title left">Fundos</h3>
                    </div>
                    <div class="col-6-sm right">
                        <a href="#" class="btn btn-primary">Populares</a>
                    </div>
                </div>
                <!-- Feature Product -->
                <div class="row">

                    <?php foreach ($fundos as $fundo): ?>
                        <div class="col-6-sm">
                            <div class="featured-product">
                                <div class="content-img <?= htmlspecialchars($fundo); ?> fundo-option"
                                    data-fundo="<?= htmlspecialchars($fundo); ?>">
                                </div>
                                <div class="product-detail">
                                    <h4 class="product-name">Fundo <?= ucfirst($fundo); ?></h4>
                                    <p class="price">Grátis</p>
                                </div>
                                <div class="star"
                                    onclick="savePreference('fundo', '<?= htmlspecialchars($fundo); ?>')">
                                    <img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Avaliação">
                                    <span class="review"></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

</body>

</html>