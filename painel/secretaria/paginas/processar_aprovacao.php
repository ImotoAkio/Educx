<?php
require '../../../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $troca_id = isset($_POST['troca_id']) ? (int)$_POST['troca_id'] : null;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : null;

    // Verifica se os dados necessários foram enviados
    if (!$troca_id || !in_array($acao, ['aprovar', 'rejeitar'])) {
        header('Location: tables.php?erro=dados_invalidos');
        exit;
    }

    if ($acao === 'aprovar') {
        try {
            // Atualiza o status da troca para 'aprovado'
            $stmt = $pdo->prepare("UPDATE trocas SET status = 'aprovado' WHERE id = :id");
            $stmt->execute([':id' => $troca_id]);

            header('Location: tables.php?status=aprovado');
            exit;
        } catch (Exception $e) {
            header('Location: tables.php?erro=erro_ao_aprovar');
            exit;
        }
    } elseif ($acao === 'rejeitar') {
        try {
            // Busca os detalhes da troca para estornar moedas
            $stmt = $pdo->prepare("
                SELECT t.aluno_id, t.produto_id, p.moeda 
                FROM trocas t
                JOIN produtos p ON t.produto_id = p.id
                WHERE t.id = :id
            ");
            $stmt->execute([':id' => $troca_id]);
            $troca = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($troca) {
                // Estorna as moedas para o aluno
                $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas + :moeda WHERE id = :aluno_id");
                $stmt->execute([
                    ':moeda' => $troca['moeda'],
                    ':aluno_id' => $troca['aluno_id']
                ]);
            }

            // Atualiza o status da troca para 'rejeitado'
            $stmt = $pdo->prepare("UPDATE trocas SET status = 'rejeitado' WHERE id = :id");
            $stmt->execute([':id' => $troca_id]);

            header('Location: tables.php?status=rejeitado');
            exit;
        } catch (Exception $e) {
            header('Location: tables.php?erro=erro_ao_rejeitar');
            exit;
        }
    }
}
