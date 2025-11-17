<?php
// Inclui o arquivo de conexão com o banco de dados
include 'db.php';

// Verifica se o ID do aluno foi passado na URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$alunoEncontrado = null;
$posicaoAluno = null;
$posicaoTurma = null;
$turma_id = null;
$turma_nome = null;

// Buscar turma do aluno se o ID foi passado
if ($id) {
    try {
        $stmt_turma = $pdo->prepare("
            SELECT t.id AS turma_id, t.nome AS turma_nome
            FROM alunos_turmas at
            JOIN turmas t ON at.turma_id = t.id
            WHERE at.aluno_id = :aluno_id
            LIMIT 1
        ");
        $stmt_turma->execute([':aluno_id' => $id]);
        $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);
        
        if ($turma) {
            $turma_id = $turma['turma_id'];
            $turma_nome = $turma['turma_nome'];
        }
    } catch (PDOException $e) {
        // Se não encontrar turma, continua sem erro
    }
}

// Consulta para buscar todos os alunos ordenados por XP total
try {
    $query_todos = "SELECT id, nome, xp_total, avatar, GREATEST(FLOOR(xp_total / 1000), 1) AS nivel FROM alunos ORDER BY xp_total DESC";
    $stmt = $pdo->query($query_todos);
    $alunos_todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}

// Consulta para buscar alunos da turma ordenados por XP total
$alunos_turma = [];
if ($turma_id) {
    try {
        $query_turma = "
            SELECT a.id, a.nome, a.xp_total, a.avatar, GREATEST(FLOOR(a.xp_total / 1000), 1) AS nivel 
            FROM alunos a
            JOIN alunos_turmas at ON a.id = at.aluno_id
            WHERE at.turma_id = :turma_id
            ORDER BY a.xp_total DESC
        ";
        $stmt = $pdo->prepare($query_turma);
        $stmt->execute([':turma_id' => $turma_id]);
        $alunos_turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Se houver erro, continua sem alunos da turma
    }
}

// Determina a posição do aluno no ranking geral
if ($id) {
    foreach ($alunos_todos as $index => $aluno) {
        if ($aluno['id'] == $id) {
            $alunoEncontrado = $aluno;
            $posicaoAluno = $index + 1;
            break;
        }
    }
    
    // Determina a posição do aluno no ranking da turma
    if ($turma_id && !empty($alunos_turma)) {
        foreach ($alunos_turma as $index => $aluno) {
            if ($aluno['id'] == $id) {
                $posicaoTurma = $index + 1;
                break;
            }
        }
    }
}

$current_avatar = $alunoEncontrado['avatar'] ?? 'asset/img/avatar/default.gif';

// Determinar qual ranking mostrar (padrão: todos)
$filtro_ativo = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$alunos_exibir = ($filtro_ativo === 'turma' && !empty($alunos_turma)) ? $alunos_turma : $alunos_todos;
$posicao_exibir = ($filtro_ativo === 'turma' && $posicaoTurma) ? $posicaoTurma : $posicaoAluno;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking de Alunos</title>
    <link rel="stylesheet" href="asset/loja.css">
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        
        .coin-font {
            font-family: "Press Start 2P", cursive;
            font-size: 1.5em;
            color: gold;
        }
        
        .coins {
            display: flex;
            align-items: center;
            font-size: 1.2em;
        }

        .coins img {
            width: 24px;
            margin-right: 10px;
        }
        
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

        .filter-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
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
            text-decoration: none;
            display: inline-block;
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

        .filter-btn.active.todos {
            background: linear-gradient(135deg, #F3E600, #ffd93d);
            border-color: #F3E600;
        }

        .filter-btn.active.turma {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
        }

        .hero-card .card-content p {
            line-height: 1.6;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<!-- Please ❤ this if you like it! -->
<!-- Follow Me https://codepen.io/designfenix -->

<div id="wrapper">
	<header>
		<div class="container-fluid">
			<div class="row">
				<div class="col-4-sm">
					<button class="voltar" onclick="window.location.href='aluno.php?id=<?= $id ?? ''; ?>'">
						<i class="fas fa-arrow-left"></i>
					</button>
				</div>
				<div class="col-4-sm center">
					<h3 class="page-title">Ranking</h3>
				</div>
				<div class="col-4-sm right">
					<div class="profiale"></div>
				</div>
			</div>
		</div>
	</header>
	<section>
		<div class="container-fluid">
			<!-- HERO CARD -->
			<?php if ($alunoEncontrado): ?>
			<div class="row">
				<div class="col-12">
					<div class="hero-card">
                        <div class="content-image">
                            <img src="<?= htmlspecialchars($current_avatar); ?>" alt="">
                        </div>
						<div class="card-content">
							<h3>Olá <?= htmlspecialchars($alunoEncontrado['nome']); ?>,</h3>
                            <p>
								você está na <?= $posicao_exibir ?? $posicaoAluno; ?>ª posição
								<?php if ($filtro_ativo === 'turma' && $turma_nome): ?>
									<br>da turma <?= htmlspecialchars($turma_nome); ?>
								<?php endif; ?>
								!
							</p>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<!-- Botões de Filtro -->
			<?php if ($id && $turma_id && !empty($alunos_turma)): ?>
			<div class="filter-buttons">
				<a href="?id=<?= $id; ?>&filtro=todos" class="filter-btn todos <?= $filtro_ativo === 'todos' ? 'active' : ''; ?>">
					<i class="fas fa-globe"></i> Todos
				</a>
				<a href="?id=<?= $id; ?>&filtro=turma" class="filter-btn turma <?= $filtro_ativo === 'turma' ? 'active' : ''; ?>">
					<i class="fas fa-users"></i> Minha Turma
				</a>
			</div>
			<?php endif; ?>

			<!-- Category title -->
			<div class="row margin-vertical">
				<div class="col-6-sm">
					<h3 class="segment-title left">
						<?php if ($filtro_ativo === 'turma' && $turma_nome): ?>
							TOP Jogadores - <?= htmlspecialchars($turma_nome); ?>
						<?php else: ?>
							TOP Jogadores
						<?php endif; ?>
					</h3>
				</div>
			</div>
			
			<!-- Feature Product -->
			<div class="row">
				<div class="col-12">
					<!-- Exibição do ranking -->
					<?php if (!empty($alunos_exibir)): ?>
						<?php foreach ($alunos_exibir as $index => $aluno): ?>
						<div class="featured-product">
							<div class="content-img">
								<img src="<?= htmlspecialchars($aluno['avatar'] ?: 'asset/img/avatar/default.gif'); ?>" alt="Foto do Aluno">
							</div>
							<div class="product-detail">
								<h4 class="product-name"><?= htmlspecialchars($aluno['nome']); ?></h4>
								<p class="price">XP Total: <?= number_format($aluno['xp_total'], 0, ',', '.'); ?></p>
							</div>
							<div class="star">
								<img src="https://design-fenix.com.ar/codepen/ui-store/stars.png" alt="Estrelas">
								<span class="review"><?= ($index + 1); ?>° lugar</span>
							</div>
						</div>
						<?php endforeach; ?>
					<?php else: ?>
						<p>Nenhum aluno encontrado no ranking.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</div>

</body>
</html>

