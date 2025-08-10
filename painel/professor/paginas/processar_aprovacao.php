<?php
require '../../../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitacao_id = isset($_POST['solicitacao_id']) ? (int)$_POST['solicitacao_id'] : null;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : null;

    // Verifica se os dados necessários foram enviados
    if (!$solicitacao_id || !in_array($acao, ['aprovar', 'rejeitar'])) {
        header('Location: painel_secretaria.php?erro=dados_invalidos');
        exit;
    }

    if ($acao === 'aprovar') {
        try {
            // Inicia uma transação para garantir integridade
            $pdo->beginTransaction();

            // Busca os detalhes da solicitação
            $stmt = $pdo->prepare("
                SELECT s.aluno_id, s.missao_id, m.xp, m.moedas 
                FROM solicitacoes_missoes s
                JOIN missoes m ON s.missao_id = m.id
                WHERE s.id = :id
            ");
            $stmt->execute([':id' => $solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($solicitacao) {
                // Atualiza o XP total e as moedas do aluno
                $stmt = $pdo->prepare("
                    UPDATE alunos 
                    SET 
                        xp_total = xp_total + :xp, 
                        moedas = moedas + :moedas 
                    WHERE id = :aluno_id
                ");
                $stmt->execute([
                    ':xp' => $solicitacao['xp'],
                    ':moedas' => $solicitacao['moedas'],
                    ':aluno_id' => $solicitacao['aluno_id']
                ]);

                // Atualiza o status da solicitação para 'aprovado'
                $stmt = $pdo->prepare("UPDATE solicitacoes_missoes SET status = 'aprovado' WHERE id = :id");
                $stmt->execute([':id' => $solicitacao_id]);

                // Confirma a transação
                $pdo->commit();
            } else {
                // Caso a solicitação não seja encontrada, reverte a transação
                $pdo->rollBack();
                header('Location: painel_secretaria.php?erro=solicitacao_nao_encontrada');
                exit;
            }
        } catch (Exception $e) {
            // Em caso de erro, reverte a transação
            $pdo->rollBack();
            header('Location: painel_secretaria.php?erro=erro_ao_aprovar');
            exit;
        }
    } elseif ($acao === 'rejeitar') {
        try {
            // Atualiza o status da solicitação para 'rejeitado'
            $stmt = $pdo->prepare("UPDATE solicitacoes_missoes SET status = 'rejeitado' WHERE id = :id");
            $stmt->execute([':id' => $solicitacao_id]);

            // Redireciona de volta
            header('Location: missoes.php?status=rejeitado');
            exit;
        } catch (Exception $e) {
            header('Location: painel_secretaria.php?erro=erro_ao_rejeitar');
            exit;
        }
    }

    // Redireciona para a página anterior (painel da secretaria)
    header('Location: missoes.php');
    exit;
}
