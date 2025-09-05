<?php
require '../../../db.php';
session_start();

// Incluir sistema de feedback
require 'include/feedback.php';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    redirecionarComMensagem('dashboard.php', 'error', 'Acesso negado. Faça login como secretaria.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitacao_id = isset($_POST['solicitacao_id']) ? (int)$_POST['solicitacao_id'] : null;
    $troca_id = isset($_POST['troca_id']) ? (int)$_POST['troca_id'] : null;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : null;

    // Verifica se os dados necessários foram enviados
    if (!in_array($acao, ['aprovar', 'rejeitar'])) {
        redirecionarComMensagem('dashboard.php', 'error', 'Dados inválidos fornecidos.');
    }

    // Processar missões
    if ($solicitacao_id) {
        try {
            // Busca os detalhes da solicitação
            $stmt = $pdo->prepare("
                SELECT s.*, a.nome AS aluno_nome, m.nome AS missao_nome, m.xp, m.moedas
                FROM solicitacoes_missoes s
                JOIN alunos a ON s.aluno_id = a.id
                JOIN missoes m ON s.missao_id = m.id
                WHERE s.id = :id
            ");
            $stmt->execute([':id' => $solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$solicitacao) {
                redirecionarComMensagem('missoes.php', 'error', 'Solicitação não encontrada.');
            }

            if ($acao === 'aprovar') {
                // Atualiza o status da solicitação para 'aprovado'
                $stmt = $pdo->prepare("UPDATE solicitacoes_missoes SET status = 'aprovado' WHERE id = :id");
                $stmt->execute([':id' => $solicitacao_id]);

                // Adiciona XP e moedas ao aluno
                $stmt = $pdo->prepare("UPDATE alunos SET xp_total = xp_total + :xp, moedas = moedas + :moedas WHERE id = :aluno_id");
                $stmt->execute([
                    ':xp' => $solicitacao['xp'],
                    ':moedas' => $solicitacao['moedas'],
                    ':aluno_id' => $solicitacao['aluno_id']
                ]);

                redirecionarComMensagem('missoes.php', 'success', "Missão '{$solicitacao['missao_nome']}' aprovada para {$solicitacao['aluno_nome']}!");
            } elseif ($acao === 'rejeitar') {
                // Atualiza o status da solicitação para 'rejeitado'
                $stmt = $pdo->prepare("UPDATE solicitacoes_missoes SET status = 'rejeitado' WHERE id = :id");
                $stmt->execute([':id' => $solicitacao_id]);

                redirecionarComMensagem('missoes.php', 'warning', "Missão '{$solicitacao['missao_nome']}' rejeitada para {$solicitacao['aluno_nome']}.");
            }
        } catch (Exception $e) {
            redirecionarComMensagem('missoes.php', 'error', 'Erro ao processar solicitação: ' . $e->getMessage());
        }
    }

    // Processar trocas
    if ($troca_id) {
        try {
            // Busca os detalhes da troca
            $stmt = $pdo->prepare("
                SELECT t.*, a.nome AS aluno_nome, p.nome AS produto_nome, p.moeda
                FROM trocas t
                JOIN alunos a ON t.aluno_id = a.id
                JOIN produtos p ON t.produto_id = p.id
                WHERE t.id = :id
            ");
            $stmt->execute([':id' => $troca_id]);
            $troca = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$troca) {
                redirecionarComMensagem('tables.php', 'error', 'Troca não encontrada.');
            }

            if ($acao === 'aprovar') {
                // Verifica se o aluno tem moedas suficientes
                if ($troca['moeda'] > 0) {
                    $stmt = $pdo->prepare("SELECT moedas FROM alunos WHERE id = :aluno_id");
                    $stmt->execute([':aluno_id' => $troca['aluno_id']]);
                    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($aluno['moedas'] < $troca['moeda']) {
                        redirecionarComMensagem('tables.php', 'error', "O aluno {$troca['aluno_nome']} não tem moedas suficientes para esta troca.");
                    }
                    
                    // Desconta as moedas do aluno
                    $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas - :moeda WHERE id = :aluno_id");
                    $stmt->execute([
                        ':moeda' => $troca['moeda'],
                        ':aluno_id' => $troca['aluno_id']
                    ]);
                }
                
                // Atualiza o status da troca para 'aprovado'
                $stmt = $pdo->prepare("UPDATE trocas SET status = 'aprovado' WHERE id = :id");
                $stmt->execute([':id' => $troca_id]);

                redirecionarComMensagem('tables.php', 'success', "Troca do produto '{$troca['produto_nome']}' aprovada para {$troca['aluno_nome']}!");
            } elseif ($acao === 'rejeitar') {
                // Estorna as moedas para o aluno
                $stmt = $pdo->prepare("UPDATE alunos SET moedas = moedas + :moeda WHERE id = :aluno_id");
                $stmt->execute([
                    ':moeda' => $troca['moeda'],
                    ':aluno_id' => $troca['aluno_id']
                ]);

                // Atualiza o status da troca para 'rejeitado'
                $stmt = $pdo->prepare("UPDATE trocas SET status = 'rejeitado' WHERE id = :id");
                $stmt->execute([':id' => $troca_id]);

                redirecionarComMensagem('tables.php', 'warning', "Troca do produto '{$troca['produto_nome']}' rejeitada para {$troca['aluno_nome']}. Moedas estornadas.");
            }
        } catch (Exception $e) {
            redirecionarComMensagem('tables.php', 'error', 'Erro ao processar troca: ' . $e->getMessage());
        }
    }
}
