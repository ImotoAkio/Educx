<?php
session_start();
require '../db.php';

// Verificar se é professor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se todos os campos necessários foram enviados
if (!isset($_POST['tipo']) || !isset($_POST['operacao']) || !isset($_POST['aluno_id'])) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não fornecidos']);
    exit;
}

$tipo = $_POST['tipo'];
$operacao = $_POST['operacao'];
$aluno_id = (int)$_POST['aluno_id'];
$professor_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    
    // Buscar dados do aluno
    $stmt = $pdo->prepare("SELECT * FROM alunos WHERE id = :aluno_id");
    $stmt->execute([':aluno_id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aluno) {
        throw new Exception('Aluno não encontrado');
    }
    
    if ($tipo === 'xp') {
        $valor = (int)$_POST['valor'];
        $motivo = $_POST['motivo'] ?? '';
        
        if ($valor < 1 || $valor > 1000) {
            throw new Exception('Valor de XP deve estar entre 1 e 1000');
        }
        
        if ($operacao === 'adicionar') {
            $novo_xp = $aluno['xp_total'] + $valor;
            $stmt = $pdo->prepare("UPDATE alunos SET xp_total = :novo_xp WHERE id = :aluno_id");
            $stmt->execute([':novo_xp' => $novo_xp, ':aluno_id' => $aluno_id]);
            
            // Registrar no log de ações
            $stmt = $pdo->prepare("
                INSERT INTO log_acoes (professor_id, aluno_id, tipo_acao, descricao, xp_alterado, moedas_alteradas, data_acao) 
                VALUES (:professor_id, :aluno_id, 'adicionar_xp', :motivo, :valor, 0, NOW())
            ");
            $stmt->execute([
                ':professor_id' => $professor_id,
                ':aluno_id' => $aluno_id,
                ':valor' => $valor,
                ':motivo' => $motivo
            ]);
            
        } elseif ($operacao === 'remover') {
            $novo_xp = max(0, $aluno['xp_total'] - $valor);
            $stmt = $pdo->prepare("UPDATE alunos SET xp_total = :novo_xp WHERE id = :aluno_id");
            $stmt->execute([':novo_xp' => $novo_xp, ':aluno_id' => $aluno_id]);
            
            // Registrar no log de ações
            $stmt = $pdo->prepare("
                INSERT INTO log_acoes (professor_id, aluno_id, tipo_acao, descricao, xp_alterado, moedas_alteradas, data_acao) 
                VALUES (:professor_id, :aluno_id, 'remover_xp', :motivo, :valor, 0, NOW())
            ");
            $stmt->execute([
                ':professor_id' => $professor_id,
                ':aluno_id' => $aluno_id,
                ':valor' => $valor,
                ':motivo' => $motivo
            ]);
        }
        
    } elseif ($tipo === 'moedas') {
        $valor = (int)$_POST['valor'];
        $motivo = $_POST['motivo'] ?? '';
        
        if ($valor < 1 || $valor > 1000) {
            throw new Exception('Valor de moedas deve estar entre 1 e 1000');
        }
        
        if ($operacao === 'adicionar') {
            $novas_moedas = $aluno['moedas'] + $valor;
            $stmt = $pdo->prepare("UPDATE alunos SET moedas = :novas_moedas WHERE id = :aluno_id");
            $stmt->execute([':novas_moedas' => $novas_moedas, ':aluno_id' => $aluno_id]);
            
            // Registrar no log de ações
            $stmt = $pdo->prepare("
                INSERT INTO log_acoes (professor_id, aluno_id, tipo_acao, descricao, xp_alterado, moedas_alteradas, data_acao) 
                VALUES (:professor_id, :aluno_id, 'adicionar_moedas', :motivo, 0, :valor, NOW())
            ");
            $stmt->execute([
                ':professor_id' => $professor_id,
                ':aluno_id' => $aluno_id,
                ':valor' => $valor,
                ':motivo' => $motivo
            ]);
            
        } elseif ($operacao === 'remover') {
            if ($aluno['moedas'] < $valor) {
                throw new Exception('Aluno não possui moedas suficientes');
            }
            
            $novas_moedas = $aluno['moedas'] - $valor;
            $stmt = $pdo->prepare("UPDATE alunos SET moedas = :novas_moedas WHERE id = :aluno_id");
            $stmt->execute([':novas_moedas' => $novas_moedas, ':aluno_id' => $aluno_id]);
            
            // Registrar no log de ações
            $stmt = $pdo->prepare("
                INSERT INTO log_acoes (professor_id, aluno_id, tipo_acao, descricao, xp_alterado, moedas_alteradas, data_acao) 
                VALUES (:professor_id, :aluno_id, 'remover_moedas', :motivo, 0, :valor, NOW())
            ");
            $stmt->execute([
                ':professor_id' => $professor_id,
                ':aluno_id' => $aluno_id,
                ':valor' => $valor,
                ':motivo' => $motivo
            ]);
        }
        
    } elseif ($tipo === 'missao') {
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $xp = (int)($_POST['xp'] ?? 0);
        $moedas = (int)($_POST['moedas'] ?? 0);
        
        if (empty($nome) || empty($descricao)) {
            throw new Exception('Nome e descrição da missão são obrigatórios');
        }
        
        if ($xp < 1 || $xp > 1000 || $moedas < 1 || $moedas > 1000) {
            throw new Exception('Valores de recompensa devem estar entre 1 e 1000');
        }
        
        // Criar missão rápida
        $stmt = $pdo->prepare("
            INSERT INTO missoes (nome, descricao, xp, moedas, turma_id, status, data_criacao) 
            VALUES (:nome, :descricao, :xp, :moedas, NULL, 'ativa', NOW())
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':xp' => $xp,
            ':moedas' => $moedas
        ]);
        
        $missao_id = $pdo->lastInsertId();
        
        // Criar solicitação de missão para o aluno
        $stmt = $pdo->prepare("
            INSERT INTO solicitacoes_missoes (aluno_id, missao_id, status, data_solicitacao) 
            VALUES (:aluno_id, :missao_id, 'aprovado', NOW())
        ");
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':missao_id' => $missao_id
        ]);
        
        // Aplicar recompensas imediatamente
        $novo_xp = $aluno['xp_total'] + $xp;
        $novas_moedas = $aluno['moedas'] + $moedas;
        
        $stmt = $pdo->prepare("UPDATE alunos SET xp_total = :novo_xp, moedas = :novas_moedas WHERE id = :aluno_id");
        $stmt->execute([
            ':novo_xp' => $novo_xp,
            ':novas_moedas' => $novas_moedas,
            ':aluno_id' => $aluno_id
        ]);
        
        // Registrar no log de ações
        $stmt = $pdo->prepare("
            INSERT INTO log_acoes_professor (professor_id, aluno_id, tipo_acao, valor, motivo, data_acao) 
            VALUES (:professor_id, :aluno_id, 'criar_missao', :xp, :motivo, NOW())
        ");
        $stmt->execute([
            ':professor_id' => $professor_id,
            ':aluno_id' => $aluno_id,
            ':xp' => $xp,
            ':motivo' => "Missão: $nome"
        ]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Ação executada com sucesso']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
