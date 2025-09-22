<?php
require 'db.php';
session_start();

if (!isset($_GET['troca_id'], $_GET['produto_id'], $_GET['aluno_id'])) {
	die("Erro: Dados insuficientes para processar a troca.");
}

$produtoId = (int)$_GET['produto_id'];
$alunoId = (int)$_GET['aluno_id'];
$trocaId = (int)$_GET['troca_id'];

// Recuperar informações do produto e do aluno
$stmtProduto = $pdo->prepare("SELECT * FROM produtos WHERE id = :produto_id");
$stmtProduto->execute([':produto_id' => $produtoId]);
$produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

$stmtAluno = $pdo->prepare("SELECT * FROM alunos WHERE id = :aluno_id");
$stmtAluno->execute([':aluno_id' => $alunoId]);
$aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

if (!$produto || !$aluno) {
	die("Produto ou aluno não encontrado.");
}

// Verificar saldo e processar troca
$moedas_necessarias = (int)$produto['moeda'];
$moedas_aluno = (int)$aluno['moedas'];
$erro = false;
$mensagem = '';

// Verificar se a troca já foi processada
$stmtTroca = $pdo->prepare("SELECT status FROM trocas WHERE id = :troca_id");
$stmtTroca->execute([':troca_id' => $trocaId]);
$statusTroca = $stmtTroca->fetch(PDO::FETCH_ASSOC);

if ($statusTroca && $statusTroca['status'] !== 'pendente') {
	// Troca já foi processada
	if ($statusTroca['status'] === 'aprovado') {
		$mensagem = "Sua troca foi <strong>aprovada</strong> pela secretaria! Você já pode retirar seu <strong>" . htmlspecialchars($produto['nome']) . "</strong>! 🎉";
	} else {
		$mensagem = "Sua troca foi <strong>rejeitada</strong> pela secretaria. As moedas foram devolvidas para sua conta.";
	}
} else {
	// Processar nova troca
	if ($moedas_aluno >= $moedas_necessarias) {
		// Subtrai moedas do aluno
		$novo_saldo = $moedas_aluno - $moedas_necessarias;
		$stmtUpdate = $pdo->prepare("UPDATE alunos SET moedas = :novo_saldo WHERE id = :aluno_id");
		$stmtUpdate->execute([':novo_saldo' => $novo_saldo, ':aluno_id' => $alunoId]);
		$mensagem = "Solicitação criada! Agora você deve ir até a secretaria para solicitar seu <strong>" . htmlspecialchars($produto['nome']) . "</strong>. As moedas foram reservadas! 🛒";
	} else {
		$erro = true;
		$mensagem = "<span style='color: #ff4d4d;'>Saldo insuficiente! Você precisa de <strong>" . $moedas_necessarias . " moedas</strong>, mas só tem <strong>" . $moedas_aluno . " moedas</strong>.";
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troca Confirmada</title>
    <link rel="stylesheet" href="asset/loja.css">
    <script>
        async function verificarStatusTroca() {
            const response = await fetch(`verificar_status.php?troca_id=<?= $trocaId; ?>`);
            const status = await response.text();

            if (status === 'aprovado') {
                window.location.href = 'aluno.php';
            } else if (status === 'rejeitado') {
                alert('Sua troca foi rejeitada.');
                window.location.href = 'aluno.php';
            }
        }

        setInterval(verificarStatusTroca, 500);
    </script>
</head>
<body>
	<div class="checkout-wrapper">
		<div class="checkout-card">
			<div class="checkout-success">
				<img src="asset/img/coin.gif" alt="Sucesso" class="center-img-success">
			</div>
			<h2>Resultado da Troca</h2>
			<div class="checkout-product">
				<img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="Imagem do produto" class="center-img-produto">
				<h3><?= htmlspecialchars($produto['nome']); ?></h3>
				<p><?= htmlspecialchars($produto['descricao']); ?></p>
			</div>
			<div class="checkout-info">
				<p><strong>Valor:</strong> <span style="color:gold; font-size:1.2em;"><?= htmlspecialchars($produto['moeda']); ?> moedas</span></p>
				<p><strong>Seu saldo:</strong> <span style="color:gold; font-size:1.2em;">
					<?php 
					if ($erro) {
						echo $moedas_aluno;
					} elseif (isset($novo_saldo)) {
						echo $novo_saldo;
					} else {
						// Buscar saldo atual do aluno
						$stmtSaldo = $pdo->prepare("SELECT moedas FROM alunos WHERE id = :aluno_id");
						$stmtSaldo->execute([':aluno_id' => $alunoId]);
						$saldoAtual = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
						echo $saldoAtual['moedas'];
					}
					?> moedas</span></p>
			</div>
			<div style="margin-top:18px;">
				<p class="branco" style="font-size:1.1em;"><?= $mensagem; ?></p>
			</div>
			
			<?php if (!$erro && $statusTroca && $statusTroca['status'] === 'aprovado'): ?>
			<div style="margin-top:20px; padding:15px; background:rgba(40,167,69,0.1); border:2px solid #28a745; border-radius:10px;">
				<h4 style="color:#28a745; margin-bottom:10px; font-size:1.2em;">
					<i class="fa fa-check-circle" style="margin-right:8px;"></i>Troca Aprovada!
				</h4>
				<p style="color:#fff; font-size:1em; line-height:1.4; margin:0;">
					<strong>✅</strong> Você já foi até a secretaria<br>
					<strong>✅</strong> Sua solicitação foi aprovada<br>
					<strong>✅</strong> Seu <strong><?= htmlspecialchars($produto['nome']); ?></strong> está pronto para retirada!<br>
					<strong>🎉</strong> Parabéns pela sua conquista!
				</p>
				<div style="margin-top:10px; padding:8px; background:rgba(40,167,69,0.2); border-radius:5px;">
					<small style="color:#28a745;">
						<i class="fa fa-info-circle" style="margin-right:5px;"></i>
						<strong>Status:</strong> Aprovado e pronto para retirada na secretaria.
					</small>
				</div>
			</div>
			<?php elseif (!$erro && (!$statusTroca || $statusTroca['status'] === 'pendente')): ?>
			<div style="margin-top:20px; padding:15px; background:rgba(255,165,0,0.1); border:2px solid #ffa500; border-radius:10px;">
				<h4 style="color:#ffa500; margin-bottom:10px; font-size:1.2em;">
					<i class="fa fa-walking" style="margin-right:8px;"></i>Próximos Passos Obrigatórios
				</h4>
				<p style="color:#fff; font-size:1em; line-height:1.4; margin:0;">
					<strong>1.</strong> Vá até a secretaria da escola<br>
					<strong>2.</strong> Fale com um responsável<br>
					<strong>3.</strong> Informe que você fez uma troca online<br>
					<strong>4.</strong> Mostre esta confirmação<br>
					<strong>5.</strong> A secretaria aprovará sua solicitação<br>
					<strong>6.</strong> Retire seu <strong><?= htmlspecialchars($produto['nome']); ?></strong>! 🎉
				</p>
				<div style="margin-top:10px; padding:8px; background:rgba(255,165,0,0.2); border-radius:5px;">
					<small style="color:#ffa500;">
						<i class="fa fa-exclamation-triangle" style="margin-right:5px;"></i>
						<strong>Importante:</strong> Você DEVE ir até a secretaria para que sua solicitação seja aprovada. A aprovação só acontece presencialmente!
					</small>
				</div>
			</div>
			<?php endif; ?>
			<div style="display:flex; gap:10px; margin-top:18px;">
				<a href="loja.php?id=<?= $alunoId; ?>" class="gradient-button" style="flex:1; text-align:center;">Voltar para Loja</a>
				<a href="missoes.php?id=<?= $alunoId; ?>" class="gradient-button" style="flex:1; text-align:center;">Ver Missões</a>
			</div>
		</div>
	</div>
	<style>
		.checkout-wrapper {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #17171f;
		}
		.checkout-card {
			background: #23233a;
			border-radius: 18px;
			box-shadow: 0 4px 24px #0008;
			padding: 32px 24px;
			text-align: center;
			color: #fff;
			font-family: 'Orbitron', Arial, sans-serif;
			max-width: 350px;
			width: 100%;
		}
		.checkout-success img {
			animation: bounce 1s infinite alternate;
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 60px;
			margin-bottom: 12px;
		}
		@keyframes bounce {
			0% { transform: translateY(0); }
			100% { transform: translateY(-10px); }
		}
		.gradient-button {
			background: linear-gradient(90deg, #f3e600 0%, #ffd700 100%);
			color: #23233a;
			border: none;
			border-radius: 8px;
			padding: 12px 0;
			font-weight: bold;
			font-size: 1em;
			cursor: pointer;
			box-shadow: 0 2px 8px #0002;
			transition: background 0.2s;
			text-decoration: none;
			display: block;
		}
		.gradient-button:hover {
			background: linear-gradient(90deg, #ffd700 0%, #f3e600 100%);
		}
		.center-img-produto {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 120px;
			border-radius: 12px;
			margin-bottom: 10px;
			box-shadow: 0 2px 8px #0004;
		}
		.checkout-info {
			margin: 18px 0 0 0;
		}
	</style>
</body>
</html>
