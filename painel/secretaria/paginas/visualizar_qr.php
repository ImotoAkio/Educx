<?php
// Inclui o autoload do Composer para gerar o QR Code
require_once('../../../vendor/autoload.php');

// Verifica se os parâmetros necessários foram passados
if (!isset($_GET['url']) || !isset($_GET['nome'])) {
    die("Dados insuficientes para exibir o QR Code!");
}

// Decodifica a URL e o nome do aluno
$qr_code_link = urldecode($_GET['url']);
$nome_aluno = htmlspecialchars(urldecode($_GET['nome']));

// Gera o QR Code
$qrcode = (new \chillerlan\QRCode\QRCode())->render($qr_code_link);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code de <?= $nome_aluno ?></title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
        }
        img {
            margin: 20px 0;
            width: 300px; /* Define a largura máxima da imagem */
            height: auto; /* Mantém a proporção */
        }
        a {
            text-decoration: none;
            color: blue;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>QR Code de <?= $nome_aluno ?></h1>
    <p>Escaneie o código abaixo para acessar o link associado ao aluno:</p>
    <!-- Exibe o QR Code -->
    <img src="<?= $qrcode ?>" alt="QR Code">
    <p>
        <a href="<?= htmlspecialchars($qr_code_link) ?>" target="_blank"><?= htmlspecialchars($qr_code_link) ?></a>
    </p>
</body>
</html>
