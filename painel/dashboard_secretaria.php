<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../db.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    header("Location: ../login.php");
    exit;
}

// Consulta as trocas pendentes
$trocasStmt = $pdo->query("SELECT t.id, a.nome AS aluno_nome, p.nome AS produto_nome 
                           FROM trocas t
                           JOIN alunos a ON t.aluno_id = a.id
                           JOIN produtos p ON t.produto_id = p.id
                           WHERE t.status = 'pendente'");
$trocas = $trocasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Dashboard da Secretaria</h1>
<table>
    <thead>
        <tr>
            <th>Aluno</th>
            <th>Produto</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($trocas as $troca): ?>
        <tr>
            <td><?= htmlspecialchars($troca['aluno_nome']); ?></td>
            <td><?= htmlspecialchars($troca['produto_nome']); ?></td>
            <td>
                <form method="POST" action="processar_aprovacao.php">
                    <input type="hidden" name="troca_id" value="<?= $troca['id']; ?>">
                    <button type="submit" name="acao" value="aprovar">Aprovar</button>
                    <button type="submit" name="acao" value="rejeitar">Rejeitar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
