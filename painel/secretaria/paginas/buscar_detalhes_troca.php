<?php
// Arquivo para buscar detalhes da troca via AJAX
session_start();
require '../../../db.php';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

// Verifica se o ID da troca foi fornecido
if (!isset($_GET['troca_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da troca não fornecido']);
    exit;
}

$troca_id = (int)$_GET['troca_id'];

try {
    // Busca detalhes completos da troca
    $sql = "SELECT 
                t.id,
                t.data_troca,
                t.status,
                a.id AS aluno_id,
                a.nome AS aluno_nome,
                a.moedas AS aluno_moedas,
                a.xp_total AS aluno_xp,
                p.id AS produto_id,
                p.nome AS produto_nome,
                p.descricao AS produto_descricao,
                p.moeda AS produto_moeda
            FROM trocas t
            JOIN alunos a ON t.aluno_id = a.id
            JOIN produtos p ON t.produto_id = p.id
            WHERE t.id = :troca_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['troca_id' => $troca_id]);
    $troca = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$troca) {
        http_response_code(404);
        echo json_encode(['error' => 'Troca não encontrada']);
        exit;
    }
    
    // Formata os dados para exibição
    $dados = [
        'id' => $troca['id'],
        'data_troca' => date('d/m/Y H:i:s', strtotime($troca['data_troca'])),
        'status' => $troca['status'],
        'aluno' => [
            'id' => $troca['aluno_id'],
            'nome' => $troca['aluno_nome'],
            'moedas' => $troca['aluno_moedas'],
            'xp_total' => $troca['aluno_xp']
        ],
        'produto' => [
            'id' => $troca['produto_id'],
            'nome' => $troca['produto_nome'],
            'descricao' => $troca['produto_descricao'],
            'moeda' => $troca['produto_moeda']
        ]
    ];
    
    // Retorna os dados em JSON
    header('Content-Type: application/json');
    echo json_encode($dados);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
