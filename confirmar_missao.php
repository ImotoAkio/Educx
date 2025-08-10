<?php
require 'db.php';

// Verificar se os IDs foram fornecidos
$aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
$missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;

if (!$aluno_id || !$missao_id) {
    die("Erro: ID do aluno ou da missão não foi fornecido.");
}

try {
    // Consulta os dados do aluno
    $stmt = $pdo->prepare("SELECT id, nome, moedas, xp_atual, xp_total FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("Erro: Aluno não encontrado.");
    }

    // Consulta os dados da missão
    $stmt = $pdo->prepare("SELECT id, nome, descricao, xp, moedas, link FROM missoes WHERE id = :id");
    $stmt->execute([':id' => $missao_id]);
    $missao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$missao) {
        die("Erro: Missão não encontrada.");
    }
} catch (PDOException $e) {
    die("Erro ao consultar o banco de dados: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
    $missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;

    if (!$aluno_id || !$missao_id) {
        die("Erro: Dados inválidos.");
    }

    // Verificar se o aluno já realizou ou solicitou essa missão
    $stmt = $pdo->prepare("
        SELECT * 
        FROM solicitacoes_missoes 
        WHERE aluno_id = :aluno_id 
          AND missao_id = :missao_id 
          AND status IN ('pendente', 'aprovado')
    ");
    $stmt->execute([':aluno_id' => $aluno_id, ':missao_id' => $missao_id]);
    $solicitacao_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redireciona para blok.php caso já exista uma solicitação
    if ($solicitacao_existente) {
        header("Location: blok.php?aluno_id=$aluno_id&missao_id=$missao_id");
        exit;
    }

    // Insere a nova solicitação
    $stmt = $pdo->prepare("INSERT INTO solicitacoes_missoes (aluno_id, missao_id) VALUES (:aluno_id, :missao_id)");
    $stmt->execute([':aluno_id' => $aluno_id, ':missao_id' => $missao_id]);
}

// Código existente para GET continua aqui...
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Troca</title>
    <link rel="stylesheet" href="asset/loja.css">
    <style>
        img {
            width: 150px; /* Tamanho da imagem */
            height: auto; /* Mantém a proporção da imagem */
            margin-left: 20%; /* Espaço abaixo da imagem */
            margin-bottom: 10px;
        }
        .branco{
    color: #ffffff;
}
        /* From Uiverse.io by adamgiebl */ 
button {
  color: #090909;
  padding: 0.7em 1.7em;
  font-size: 18px;
  border-radius: 0.5em;
  background: #e8e8e8;
  cursor: pointer;
  border: 1px solid #e8e8e8;
  transition: all 0.3s;
  box-shadow: 6px 6px 12px #c5c5c5, -6px -6px 12px #ffffff;
}

button:hover {
  border: 1px solid white;
}

button:active {
  box-shadow: 4px 4px 12px #c5c5c5, -4px -4px 12px #ffffff;
}
.branco{
    color: #ffffff;
}

    </style>
</head>
<body>
<div id="wrapper">
		<!-- Cabeçalho -->
		<header>
			<div class="container-fluid">
				<div class="row">
				<a href="aluno.php?id=<?= $aluno['id']; ?>" class="btn" style="color: #ffffff;">
    			<i class="fas fa-chevron-left"></i>
				</a>

					<div class="col-4-sm center">
						<h1 class="page-title">Confirmação de Missão</h1> <!-- Título  -->
					</div>


				</div>
			</div>
		</header>

		<section>
			<div class="container-fluid">
				<!-- Cartão principal -->
				<div class="row">
					<div class="col-12">
						<div class="hero-card1">

							<div class="card-content">
								<h3 class="branco">Parabéns! Você acabou de realizar a missão <?= htmlspecialchars($missao['nome']); ?></h3> 
								<img src="asset/img/professor.png" alt="Parabens";>
                                <p class="branco">Vá até a secretaria ou professor responsável e apresente a comprovação da sua missão! </p> <!-- Descrição  -->
                                <div>
                                <br>
                            <form action="aluno.php?id=<?= $aluno_id;?>" method="POST">
                                <input type="hidden" name="produto_id" value="<?= $produtoId; ?>">
                                <input type="hidden" name="aluno_id" value="<?= $alunoId; ?>">
                                <button type="submit">Voltar pagina inicial</button>
                            </form>
                                    
									
								</div>
							</div>
						</div>
					</div>
				</div>



</body>
</html>