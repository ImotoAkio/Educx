<?php
// Inclui o arquivo de conexão com o banco de dados
include 'db.php';

// ID do aluno (deve ser obtido da sessão ou URL)
$aluno_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Verifica se o ID foi fornecido
if (!$aluno_id) {
    die("ID do aluno não fornecido.");
}

// Lista de fundos disponíveis (nomes das classes CSS)
$fundos = ['cubos', 'rosa', 'dark', 'wave', 'especial'];

// Atualiza avatar ou fundo no banco de dados (somente via POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false];

    // Atualizar avatar
    if (isset($_POST['avatar'])) {
        $avatar_path = 'asset/img/avatar/' . $_POST['avatar'];
        $stmt = $pdo->prepare("UPDATE alunos SET avatar = :avatar WHERE id = :id");
        $stmt->execute([':avatar' => $avatar_path, ':id' => $aluno_id]);
        $response['success'] = true;
    }

    // Atualizar fundo
    if (isset($_POST['fundo']) && in_array($_POST['fundo'], $fundos)) {
        $stmt = $pdo->prepare("UPDATE alunos SET fundo = :fundo WHERE id = :id");
        $stmt->execute([':fundo' => $_POST['fundo'], ':id' => $aluno_id]);
        $response['success'] = true;
    }

    echo json_encode($response);
    exit;
}

// Obtém o avatar e fundo atuais do aluno
$stmt = $pdo->prepare("SELECT avatar, fundo FROM alunos WHERE id = :id");
$stmt->execute([':id' => $aluno_id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

// Define avatar e fundo atuais ou padrões
$current_avatar = $aluno['avatar'] ?? 'asset/img/avatar/default.gif';
$current_fundo = $aluno['fundo'] ?? 'wrapper-snow';
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
                // Redireciona para aluno.php com o ID do aluno
                const alunoId = <?= json_encode($aluno_id); ?>;
                window.location.href = `aluno.php?id=${alunoId}`;
            } else {
                alert('Erro ao salvar. Tente novamente.');
            }
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
                        <a href="#" class="btn btn-primary">Popular</a>
                    </div>
                </div>
                <!-- Products grid -->
                <div class="row">
                    <?php
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    foreach (scandir('asset/img/avatar') as $avatar):
                        if ($avatar !== '.' && $avatar !== '..') {
                            $file_extension = pathinfo($avatar, PATHINFO_EXTENSION);
                            if (in_array(strtolower($file_extension), $allowed_extensions)):
                    ?>
                                <div class="col-6-sm">
                                    <div class="product" onclick="savePreference('avatar', '<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>')">
                                        <img src="asset/img/avatar/<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar <?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="detail">
                                            <h4 class="naime"><?= htmlspecialchars(ucfirst(pathinfo($avatar, PATHINFO_FILENAME))); ?></h4>
                                            <div class="detail-footer">

                                            </div>
                                        </div>
                                        <div class="star"><img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Star"></div>
                                    </div>
                                </div>
                    <?php
                            endif;
                        }
                    endforeach;
                    ?>


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