<?php
session_start();
require '../../db.php'; // Conexão com o banco

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

try {
    // Busca o nome do professor usando o ID da sessão
    $stmt = $pdo->prepare("SELECT nome FROM professores WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($professor) {
        $professor_nome = htmlspecialchars($professor['nome']);
    } else {
        // Caso o professor não seja encontrado, redireciona para login
        header("Location: ../login.php");
        exit;
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar informações do professor: " . $e->getMessage();
    exit;
}

try {
    // Consulta para buscar todos os alunos ordenados por XP total
    $query = "SELECT id, nome, xp_total, avatar, GREATEST(FLOOR(xp_total / 1000), 1) AS nivel FROM alunos ORDER BY xp_total DESC";
    $stmt = $pdo->query($query);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}

// Inicializa variáveis
$alunos = [];
$erro = "";
$professor_nome = htmlspecialchars($_SESSION['usuario_nome']);
$current_avatar = $alunoEncontrado['avatar'] ?? 'asset/img/default.gif';





// Processa a pesquisa do professor
if (isset($_GET['pesquisa'])) {
    $pesquisa = $_GET['pesquisa'];
    $stmt = $pdo->prepare("SELECT * FROM alunos WHERE nome LIKE :pesquisa");
    $stmt->execute([':pesquisa' => "%$pesquisa%"]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($alunos)) {
        $erro = "Nenhum aluno encontrado com o nome '$pesquisa'.";
    }
}

// Processa a adição de moedas ao aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno_id']) && isset($_POST['valor'])) {
    $aluno_id = $_POST['aluno_id'];
    $valor = $_POST['valor'];  // Valor a ser adicionado ou removido
    $descricao = $_POST['descricao'];

    // Verifica se estamos adicionando ou removendo moedas
    if ($valor > 0) {
        // Adiciona moedas
        try {
            $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas + :valor WHERE id = :aluno_id");
            $stmt->execute([':valor' => $valor, ':aluno_id' => $aluno_id]);
        } catch (Exception $e) {
            $erro = "Erro ao adicionar moedas: " . $e->getMessage();
        }
    } else {
        // Remove moedas (se valor for negativo)
        try {
            $stmt = $pdo->prepare("UPDATE alunos SET moedas = GREATEST(moedas + :valor, 0) WHERE id = :aluno_id");
            // Usando GREATEST para garantir que o valor não fique negativo
            $stmt->execute([':valor' => $valor, ':aluno_id' => $aluno_id]);
        } catch (Exception $e) {
            $erro = "Erro ao remover moedas: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor</title>
    <link rel="stylesheet" href="../../asset/loja.css"> <!-- Link para o CSS -->
     <link rel="stylesheet" href="../../asset/button.css"> <!-- Link para o CSS -->

     <style>
/* From Uiverse.io by adamgiebl */ 
.botao-verde {
 position: relative;
 padding: 10px 40px;
 margin: 0px 10px 10px 0px;
 float: left;
 border-radius: 3px;
 font-size: 20px;
 color: #FFF;
 text-decoration: none;
 background-color: #2ecc71;
 border: none;
 border-bottom: 5px solid #27ae60;
 text-shadow: 0px -2px #27ae60;
 -webkit-transition: all 0.1s;
 transition: all 0.1s;
}

.botao-verde:hover, button:active {
 -webkit-transform: translate(0px,5px);
 -ms-transform: translate(0px,5px);
 transform: translate(0px,5px);
 border-bottom: 1px solid #2ecc71;
}
.botao-vermelho {
 position: relative;
 padding: 10px 40px;
 margin: 0px 10px 10px 0px;
 float: left;
 border-radius: 3px;
 font-size: 20px;
 color: #FFF;
 text-decoration: none;
 background-color: #cc552e;
 border: none;
 border-bottom: 5px solid #ae3727;
 text-shadow: 0px -2px #ae3727;
 -webkit-transition: all 0.1s;
 transition: all 0.1s;
}

.botao-vermelho:hover, button:active {
 -webkit-transform: translate(0px,5px);
 -ms-transform: translate(0px,5px);
 transform: translate(0px,5px);
 border-bottom: 1px solid #cc552e;
}

.voltar {

	margin-bottom: 15px;
  font: inherit;
  background-color: #f0f0f0;
  border: 0;
  color: #242424;
  border-radius: 0.5em;
  font-size: 1rem;
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

    </style>
</head>

<body>
    <div id="wrapper">
        <!-- Cabeçalho -->
        <header>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-4-sm"><a href="paginas/dashboard.php" class="voltar">Painel principal</a></div>
                    <br>
                    <div class="col-4-sm center">
                        <h1 class="page-title">Painel do Professor</h1>
                    </div>
                </div>
            </div>
        </header>

        <section>
            <div class="container-fluid">
                <!-- Cartão principal -->
                <div class="row">
                    <div class="col-12">
                        <div class="hero-card">
                            <div class="content-image">
                                <img src="../../asset/img/professor.png" alt="Imagem do professor">
                            </div>
                            <div class="card-content">
                                <h3>Olá, <?= $professor_nome; ?>!</h3> <!-- Saudação -->
                                <p>Pesquisar aluno</p> <!-- Descrição -->
                                <form method="GET" action="professor_dashboard.php">
                                    <div class="content-input">
                                        <input type="text" id="pesquisa" name="pesquisa" placeholder="Pesquisar aluno" required>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Título da categoria -->
                <div class="row margin-vertical">
                    <div class="col-6-sm">
                        <h3 class="segment-title left">Lista de Alunos</h3>
                    </div>
                </div>

                <!-- Alunos encontrados -->
                <?php if (!empty($erro)): ?>
                    <div class="row">
                        <div class="col-12">
                            <p class="erro"><?= htmlspecialchars($erro) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                <?php foreach ($alunos as $aluno): ?>
<div class="col-6-sm">
    <div class="product">

    <img src="/game/<?= htmlspecialchars($aluno['avatar'] ?: 'asset/img/default.gif'); ?>" alt="Avatar do Aluno">

        <form method="POST" style="display:inline;">
            <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
            <input type="hidden" name="valor" value="5"> <!-- Valor de +5 moedas -->
            <input type="hidden" name="descricao" value="Fez a atividade">
            <button type="submit" class="botao-verde">+5</button>
        </form>

        <form method="POST" style="display:inline;">
            <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
            <input type="hidden" name="valor" value="-5"> <!-- Valor de -5 moedas -->
            <input type="hidden" name="descricao" value="Remoção de moedas">
            <button type="submit" class="botao-vermelho"> -5</button>
        </form>


        
        <div class="detail">
            <h4 class="name"><?= htmlspecialchars($aluno['nome']) ?></h4>
            <div class="detail-footer">
                <div class="price left">ID: <?= $aluno['id'] ?></div>
                <div class="review right">
                    <img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Estrelas de avaliação">
                    <?= $aluno['moedas'] ?> moedas
                </div>
            </div>
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
