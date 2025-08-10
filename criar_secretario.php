<?php
require 'db.php'; // Inclui a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os valores do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); // Criptografa a senha
    $tipoUsuario = 'secretaria'; // Define o tipo de usuário como 'secretaria'

    // Insere os dados na tabela professores
    $stmt = $pdo->prepare("INSERT INTO professores (nome, email, senha, tipo_usuario) VALUES (:nome, :email, :senha, :tipo_usuario)");

    try {
        $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senha, ':tipo_usuario' => $tipoUsuario]);
        $mensagem = "Secretário criado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao criar secretário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Secretário</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .container h1 {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .container button {
            width: 100%;
            padding: 10px;
            background-color: #03A9F4;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .container button:hover {
            background-color: #039BE5;
        }
        .message {
            margin-top: 10px;
            color: green;
        }
        .error {
            margin-top: 10px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Criar Secretário</h1>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Criar Secretário</button>
        </form>
        <?php if (isset($mensagem)): ?>
            <p class="<?= strpos($mensagem, 'sucesso') !== false ? 'message' : 'error' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
