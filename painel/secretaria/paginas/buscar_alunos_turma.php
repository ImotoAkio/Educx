<?php
// Arquivo para buscar alunos de uma turma via AJAX
session_start();

// Usar caminho baseado no diretório atual do script
$script_dir = dirname(__FILE__);
require $script_dir . '/../../../db.php';

// Verifica se a secretaria está logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

// Verifica se o ID da turma foi fornecido
if (!isset($_POST['turma_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da turma não fornecido']);
    exit;
}

$turma_id = (int)$_POST['turma_id'];

try {
    // Busca informações da turma
    $sql_turma = "SELECT nome, descricao, ano_letivo FROM turmas WHERE id = :turma_id";
    $stmt_turma = $pdo->prepare($sql_turma);
    $stmt_turma->execute(['turma_id' => $turma_id]);
    $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);
    
    if (!$turma) {
        http_response_code(404);
        echo json_encode(['error' => 'Turma não encontrada']);
        exit;
    }
    
    // Busca alunos da turma
    $sql_alunos = "
        SELECT 
            a.id AS aluno_id,
            a.nome AS aluno_nome,
            a.moedas,
            a.xp_total,
            a.nivel
        FROM alunos a
        JOIN alunos_turmas at ON a.id = at.aluno_id
        WHERE at.turma_id = :turma_id
        ORDER BY a.nome ASC
    ";
    
    $stmt_alunos = $pdo->prepare($sql_alunos);
    $stmt_alunos->execute(['turma_id' => $turma_id]);
    $alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca professores da turma
    $sql_professores = "
        SELECT 
            p.id AS professor_id,
            p.nome AS professor_nome
        FROM professores p
        JOIN turmas_professores tp ON p.id = tp.professor_id
        WHERE tp.turma_id = :turma_id
        ORDER BY p.nome ASC
    ";
    
    $stmt_professores = $pdo->prepare($sql_professores);
    $stmt_professores->execute(['turma_id' => $turma_id]);
    $professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);
    
    // Formata os dados para exibição
    $dados = [
        'turma' => [
            'id' => $turma_id,
            'nome' => $turma['nome'],
            'descricao' => $turma['descricao'],
            'ano_letivo' => $turma['ano_letivo']
        ],
        'alunos' => array_map(function($aluno) {
            return [
                'id' => $aluno['aluno_id'],
                'nome' => $aluno['aluno_nome'],
                'moedas' => $aluno['moedas'],
                'xp_total' => $aluno['xp_total'],
                'nivel' => $aluno['nivel']
            ];
        }, $alunos),
        'professores' => array_map(function($professor) {
            return [
                'id' => $professor['professor_id'],
                'nome' => $professor['professor_nome']
            ];
        }, $professores),
        'estatisticas' => [
            'total_alunos' => count($alunos),
            'total_professores' => count($professores),
            'media_moedas' => count($alunos) > 0 ? round(array_sum(array_column($alunos, 'moedas')) / count($alunos), 2) : 0,
            'media_xp' => count($alunos) > 0 ? round(array_sum(array_column($alunos, 'xp_total')) / count($alunos), 2) : 0
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
