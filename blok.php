<?php
require 'db.php';

$aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
$missao_id = isset($_GET['missao_id']) ? (int)$_GET['missao_id'] : null;



// Consultar os detalhes da missão no banco de dados
$stmt = $pdo->prepare("SELECT id, nome, descricao, xp, moedas, link FROM missoes WHERE id = :id");
$stmt->execute([':id' => $missao_id]);
$missao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$missao) {
    die("Erro: Missão não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missão Bloqueada</title>
    <link rel="stylesheet" href="asset/loja.css">
    <style>
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
img {
            width: 150px; /* Tamanho da imagem */
            height: auto; /* Mantém a proporção da imagem */
            margin-left: 20%; /* Espaço abaixo da imagem */
            margin-bottom: 10px;
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
						<h1 class="page-title">Missão Bloqueada</h1> <!-- Título  -->
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
								<h3 class="branco">Parece que você ja realizou a missão <?= htmlspecialchars($missao['nome']); ?></h3> 
                                <img src="asset/img/blok.gif" alt="Toma um block ai";>
								<p class="branco">Sua solicitação esta sendo processada, assim que concluida sua recompensa será consedida!</p> <!-- Descrição  -->
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