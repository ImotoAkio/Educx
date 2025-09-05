<?php
require '../../../db.php';
session_start();

// Incluir sistema de feedback
require 'include/feedback.php';

// Verificação de sessão
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    redirecionarComMensagem('../../../login.php', 'error', 'Acesso negado. Faça login novamente.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitacao_id = isset($_POST['solicitacao_id']) ? (int)$_POST['solicitacao_id'] : null;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : null;

    // Verifica se os dados necessários foram enviados
    if (!$solicitacao_id || !in_array($acao, ['aprovar', 'rejeitar'])) {
        redirecionarComMensagem('dashboard.php', 'error', 'Dados inválidos fornecidos.');
    }

    if ($acao === 'aprovar') {
        try {
            // Inicia uma transação para garantir integridade
            $pdo->beginTransaction();

            // Busca os detalhes da solicitação
            $stmt = $pdo->prepare("
                SELECT s.aluno_id, s.missao_id, m.xp, m.moedas, a.nome as aluno_nome, m.nome as missao_nome
                FROM solicitacoes_missoes s
                JOIN missoes m ON s.missao_id = m.id
                JOIN alunos a ON s.aluno_id = a.id
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
                
                $mensagem = "Missão '{$solicitacao['missao_nome']}' aprovada para o aluno {$solicitacao['aluno_nome']}. XP: +{$solicitacao['xp']}, Moedas: +{$solicitacao['moedas']}";
                redirecionarComMensagem('dashboard.php', 'success', $mensagem);
            } else {
                // Caso a solicitação não seja encontrada, reverte a transação
                $pdo->rollBack();
                redirecionarComMensagem('dashboard.php', 'error', 'Solicitação não encontrada.');
            }
        } catch (Exception $e) {
            // Em caso de erro, reverte a transação
            $pdo->rollBack();
            redirecionarComMensagem('dashboard.php', 'error', 'Erro ao aprovar missão: ' . $e->getMessage());
        }
    } elseif ($acao === 'rejeitar') {
        try {
            // Busca informações da solicitação para a mensagem
            $stmt = $pdo->prepare("
                SELECT a.nome as aluno_nome, m.nome as missao_nome
                FROM solicitacoes_missoes s
                JOIN missoes m ON s.missao_id = m.id
                JOIN alunos a ON s.aluno_id = a.id
                WHERE s.id = :id
            ");
            $stmt->execute([':id' => $solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Atualiza o status da solicitação para 'rejeitado'
            $stmt = $pdo->prepare("UPDATE solicitacoes_missoes SET status = 'rejeitado' WHERE id = :id");
            $stmt->execute([':id' => $solicitacao_id]);

            $mensagem = "Missão '{$solicitacao['missao_nome']}' rejeitada para o aluno {$solicitacao['aluno_nome']}.";
            redirecionarComMensagem('dashboard.php', 'warning', $mensagem);
        } catch (Exception $e) {
            redirecionarComMensagem('dashboard.php', 'error', 'Erro ao rejeitar missão: ' . $e->getMessage());
        }
    }
} else {
    redirecionarComMensagem('dashboard.php', 'error', 'Método de requisição inválido.');
}
