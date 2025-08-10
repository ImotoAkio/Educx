<?php
// Conexão com o banco de dados

session_start();
require '../db.php'; // Conexão com o banco

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'secretaria') {
    header("Location: ../login.php");
    exit;
}

// Verifica se o diretório existe e cria, se necessário
if (!is_dir('../asset/loja/img')) {
    mkdir('../asset/loja/img', 0755, true); // Cria a pasta com as permissões adequadas
}

// Função para adicionar um item
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $moeda = $_POST['moeda'];
    $descricao = $_POST['descricao'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemTmp = $_FILES['imagem']['tmp_name'];
        $imagemNome = basename($_FILES['imagem']['name']);
        $destino = '../asset/loja/img/' . $imagemNome; // Caminho para salvar a imagem
        $caminhoParaBanco = 'asset/loja/img/' . $imagemNome; // Caminho relativo para salvar no banco

        // Move o arquivo enviado
        if (move_uploaded_file($imagemTmp, $destino)) {
            $sql = "INSERT INTO Produtos (nome, moeda, descricao, imagem) VALUES (:nome, :moeda, :descricao, :imagem)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nome' => $nome, 'moeda' => $moeda, 'descricao' => $descricao, 'imagem' => $caminhoParaBanco]);
        } else {
            echo "Erro ao mover o arquivo para o diretório: $destino.";
        }
    } else {
        echo "Erro no upload da imagem.";
    }
}

// Função para editar um item
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $moeda = $_POST['moeda'];
    $descricao = $_POST['descricao'];
    $imagemAtual = $_POST['imagem_atual'];

    $destino = $imagemAtual; // Caminho atual da imagem por padrão

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemTmp = $_FILES['imagem']['tmp_name'];
        $imagemNome = basename($_FILES['imagem']['name']);
        $destino = '../asset/loja/img/' . $imagemNome; // Novo caminho para a imagem
        $caminhoParaBanco = 'asset/loja/img/' . $imagemNome;

        if (move_uploaded_file($imagemTmp, $destino)) {
            $destino = $caminhoParaBanco;
        }
    }

    $sql = "UPDATE Produtos SET nome = :nome, moeda = :moeda, descricao = :descricao, imagem = :imagem WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id, 'nome' => $nome, 'moeda' => $moeda, 'descricao' => $descricao, 'imagem' => $destino]);
}

// Função para remover um item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "SELECT imagem FROM Produtos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        $imagemPath = '../' . $produto['imagem'];
        if (file_exists($imagemPath)) {
            unlink($imagemPath); // Remove o arquivo da imagem
        }
    }

    $sql = "DELETE FROM Produtos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
}

// Recupera os produtos
$sql = "SELECT * FROM Produtos";
$stmt = $pdo->query($sql);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Loja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Gerenciar Loja</h1>

        <!-- Formulário para adicionar um produto -->
        <div class="card mb-4">
            <div class="card-header">Adicionar Produto</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="moeda" class="form-label">Moedas</label>
                        <input type="number" step="0.01" class="form-control" id="moeda" name="moeda" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Imagem</label>
                        <input type="file" class="form-control" id="imagem" name="imagem" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
                </form>
            </div>
        </div>

        <!-- Lista de produtos -->
        <div class="card">
            <div class="card-header">Produtos</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Moedas</th>
                            <th>Descrição</th>
                            <th>Imagem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?= $produto['id'] ?></td>
                                <td><?= $produto['nome'] ?></td>
                                <td><?= $produto['moeda'] ?></td>
                                <td><?= $produto['descricao'] ?></td>
                                <td><img src="../<?= $produto['imagem'] ?>" alt="<?= $produto['nome'] ?>" style="width: 50px;"></td>
                                <td>
                                    <form method="POST" enctype="multipart/form-data" style="display: inline-block;">
                                        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                        <input type="hidden" name="imagem_atual" value="<?= $produto['imagem'] ?>">
                                        <input type="text" name="nome" value="<?= $produto['nome'] ?>" class="form-control mb-2" required>
                                        <input type="number" step="0.01" name="moeda" value="<?= $produto['moeda'] ?>" class="form-control mb-2" required>
                                        <textarea name="descricao" class="form-control mb-2" required><?= $produto['descricao'] ?></textarea>
                                        <input type="file" name="imagem" class="form-control mb-2">
                                        <button type="submit" name="edit" class="btn btn-success">Editar</button>
                                    </form>
                                    <a href="?delete=<?= $produto['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
