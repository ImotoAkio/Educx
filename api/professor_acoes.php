<?php
session_start();
require '../db.php';

// Verificar se é professor
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'professor') {
    // Tentar verificar com user_type também (compatibilidade)
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'professor') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }
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
$professor_id = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? null;

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
        
    } elseif ($tipo === 'atitudes') {
        // Verificar se a tabela atitudes existe
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'atitudes'");
            $tableExists = $checkTable->rowCount() > 0;
        } catch (PDOException $e) {
            $tableExists = false;
        }
        
        if (!$tableExists) {
            throw new Exception('Sistema de atitudes não está configurado. Execute o script SQL criar_sistema_extrato.sql primeiro.');
        }
        
        $atitude_id = (int)($_POST['atitude_id'] ?? 0);
        $motivo = $_POST['motivo'] ?? '';
        
        if ($atitude_id < 1) {
            throw new Exception('Atitude não selecionada');
        }
        
        // Buscar dados da atitude
        $stmt = $pdo->prepare("SELECT * FROM atitudes WHERE id = :atitude_id AND status = 'ativa'");
        $stmt->execute([':atitude_id' => $atitude_id]);
        $atitude = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$atitude) {
            throw new Exception('Atitude não encontrada ou inativa');
        }
        
        // Verificar se o tipo da atitude corresponde à operação
        if ($atitude['tipo'] !== $operacao) {
            throw new Exception('Tipo de atitude não corresponde à operação selecionada');
        }
        
        $valor_moedas = (int)$atitude['valor_moedas'];
        $tipo_transacao = $atitude['tipo']; // 'ganho' ou 'perda'
        
        // Atualizar moedas do aluno
        if ($tipo_transacao === 'ganho') {
            $novas_moedas = $aluno['moedas'] + $valor_moedas;
        } else {
            if ($aluno['moedas'] < $valor_moedas) {
                throw new Exception('Aluno não possui moedas suficientes');
            }
            $novas_moedas = $aluno['moedas'] - $valor_moedas;
        }
        
        $stmt = $pdo->prepare("UPDATE alunos SET moedas = :novas_moedas WHERE id = :aluno_id");
        $stmt->execute([':novas_moedas' => $novas_moedas, ':aluno_id' => $aluno_id]);
        
        // Montar descrição completa
        $descricao_completa = $atitude['titulo'];
        if (!empty($atitude['descricao'])) {
            $descricao_completa .= ' - ' . $atitude['descricao'];
        }
        if (!empty($motivo)) {
            $descricao_completa .= ' | Motivo: ' . $motivo;
        }
        
        // Registrar no extrato de moedas
        try {
            $stmt = $pdo->prepare("
                INSERT INTO extrato_moedas (aluno_id, professor_id, atitude_id, tipo, valor, descricao, motivo, data_transacao) 
                VALUES (:aluno_id, :professor_id, :atitude_id, :tipo, :valor, :descricao, :motivo, NOW())
            ");
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':professor_id' => $professor_id,
                ':atitude_id' => $atitude_id,
                ':tipo' => $tipo_transacao,
                ':valor' => $valor_moedas,
                ':descricao' => $descricao_completa,
                ':motivo' => $motivo
            ]);
        } catch (PDOException $e) {
            // Se a tabela extrato_moedas não existir, apenas registra no log_acoes
            $stmt = $pdo->prepare("
                INSERT INTO log_acoes (professor_id, aluno_id, tipo_acao, descricao, xp_alterado, moedas_alteradas, data_acao) 
                VALUES (:professor_id, :aluno_id, :tipo_acao, :descricao, 0, :valor, NOW())
            ");
            $stmt->execute([
                ':professor_id' => $professor_id,
                ':aluno_id' => $aluno_id,
                ':tipo_acao' => $tipo_transacao === 'ganho' ? 'adicionar_moedas' : 'remover_moedas',
                ':descricao' => $descricao_completa,
                ':valor' => $valor_moedas
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
        
        // Verificar se a coluna data_limite existe
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM missoes LIKE 'data_limite'");
            $columnExists = $checkColumn->rowCount() > 0;
        } catch (PDOException $e) {
            $columnExists = false;
        }
        
        // Criar missão rápida com data limite padrão de 30 dias (se a coluna existir)
        if ($columnExists) {
            $data_limite = date('Y-m-d', strtotime('+30 days'));
            $stmt = $pdo->prepare("
                INSERT INTO missoes (nome, descricao, xp, moedas, turma_id, status, data_criacao, data_limite) 
                VALUES (:nome, :descricao, :xp, :moedas, NULL, 'ativa', NOW(), :data_limite)
            ");
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':xp' => $xp,
                ':moedas' => $moedas,
                ':data_limite' => $data_limite
            ]);
        } else {
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
        }
        
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
