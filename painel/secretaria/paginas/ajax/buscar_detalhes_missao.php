<?php
// Iniciar sessão
session_start();

// Verificar se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Incluir conexão com banco de dados
require __DIR__ . '/../../../../db.php';

// Verificar se foi enviado o ID da solicitação
if (!isset($_POST['solicitacao_id']) || empty($_POST['solicitacao_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da solicitação não fornecido']);
    exit;
}

$solicitacao_id = $_POST['solicitacao_id'];

try {
    // Buscar detalhes completos da solicitação
    $stmt = $pdo->prepare("
        SELECT 
            s.id AS solicitacao_id,
            s.data_solicitacao,
            s.status,
            a.id AS aluno_id,
            a.nome AS aluno_nome,
            a.xp_total AS aluno_xp,
            a.moedas AS aluno_moedas,
            a.nivel AS aluno_nivel,
            a.avatar AS aluno_avatar,
            m.id AS missao_id,
            m.nome AS missao_nome,
            m.descricao AS missao_descricao,
            m.xp AS missao_xp,
            m.moedas AS missao_moedas,
            m.link AS missao_link,
            m.status AS missao_status,
            t.nome AS turma_nome
        FROM solicitacoes_missoes s
        JOIN alunos a ON s.aluno_id = a.id
        JOIN missoes m ON s.missao_id = m.id
        LEFT JOIN turmas t ON a.id IN (SELECT aluno_id FROM alunos_turmas WHERE turma_id = t.id)
        WHERE s.id = :solicitacao_id
    ");
    
    $stmt->execute(['solicitacao_id' => $solicitacao_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dados) {
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
        exit;
    }
    
    // Formatar dados para o modal
    $dados_formatados = [
        'solicitacao_id' => $dados['solicitacao_id'],
        'aluno_id' => $dados['aluno_id'],
        'aluno_nome' => htmlspecialchars($dados['aluno_nome']),
        'aluno_xp' => number_format($dados['aluno_xp']),
        'aluno_moedas' => number_format($dados['aluno_moedas']),
        'aluno_nivel' => $dados['aluno_nivel'] ?? 'N/A',
        'turma_nome' => $dados['turma_nome'] ? htmlspecialchars($dados['turma_nome']) : null,
        'missao_id' => $dados['missao_id'],
        'missao_nome' => htmlspecialchars($dados['missao_nome']),
        'missao_descricao' => htmlspecialchars($dados['missao_descricao']),
        'missao_xp' => number_format($dados['missao_xp']),
        'missao_moedas' => number_format($dados['missao_moedas']),
        'missao_link' => $dados['missao_link'] ? htmlspecialchars($dados['missao_link']) : null,
        'data_solicitacao_formatada' => date('d/m/Y', strtotime($dados['data_solicitacao'])),
        'hora_solicitacao' => date('H:i:s', strtotime($dados['data_solicitacao'])),
        'tempo_decorrido' => calcularTempoDecorrido($dados['data_solicitacao'])
    ];
    
    echo json_encode(['success' => true, 'data' => $dados_formatados]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}

// Função para calcular tempo decorrido
function calcularTempoDecorrido($data_solicitacao) {
    $agora = new DateTime();
    $solicitacao_data = new DateTime($data_solicitacao);
    $diferenca = $agora->diff($solicitacao_data);
    
    if ($diferenca->days > 0) {
        return $diferenca->days . ' dia(s) atrás';
    } elseif ($diferenca->h > 0) {
        return $diferenca->h . ' hora(s) atrás';
    } elseif ($diferenca->i > 0) {
        return $diferenca->i . ' minuto(s) atrás';
    } else {
        return 'Agora mesmo';
    }
}
?>
