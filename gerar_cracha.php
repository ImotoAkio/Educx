<?php
require 'db.php'; // Inclui a conexão com o banco de dados

// Função para gerar o crachá
function gerarCracha($aluno_id) {
    global $pdo;

    // Consulta os dados do aluno
    $sql = "SELECT nome, qr_code_link FROM alunos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("Aluno não encontrado.");
    }

    $nome_aluno = $aluno['nome'];
    $qr_code_url = $aluno['qr_code_link'];

    // Caminho para o template da imagem do crachá
    $template_path = 'template_cracha.png';

    // Carrega o template
    $template = imagecreatefrompng($template_path);
    if (!$template) {
        die("Erro ao carregar o template.");
    }

    // Define as cores para o texto
    $cor_texto = imagecolorallocate($template, 0, 0, 0); // Preto

    // Define a fonte para o texto (use uma fonte TTF)
    $font_path = 'arial.ttf'; // Certifique-se de ter uma fonte TTF disponível

    // Posição do QR Code no template (ajuste conforme necessário)
    $qr_code_x = 50; // Coordenada X
    $qr_code_y = 100; // Coordenada Y
    $qr_code_width = 200; // Largura do QR Code
    $qr_code_height = 200; // Altura do QR Code

    // Gera o QR Code temporário usando uma API externa (ex.: Google Charts)
    $qr_code_image_url = "https://chart.googleapis.com/chart?cht=qr&chs={$qr_code_width}x{$qr_code_height}&chl=" . urlencode($qr_code_url);
    $qr_code_temp = imagecreatefromstring(file_get_contents($qr_code_image_url));
    if (!$qr_code_temp) {
        die("Erro ao gerar o QR Code.");
    }

    // Insere o QR Code no template
    imagecopy($template, $qr_code_temp, $qr_code_x, $qr_code_y, 0, 0, $qr_code_width, $qr_code_height);

    // Insere o nome do aluno no template
    $nome_x = 50; // Coordenada X
    $nome_y = 320; // Coordenada Y
    $tamanho_fonte = 20;
    imagettftext($template, $tamanho_fonte, 0, $nome_x, $nome_y, $cor_texto, $font_path, $nome_aluno);

    // Salva ou exibe a imagem gerada
    header('Content-Type: image/png');
    imagepng($template);

    // Libera a memória
    imagedestroy($template);
    imagedestroy($qr_code_temp);
}

// Exemplo de uso: Gera o crachá para o aluno com ID 1
if (isset($_GET['id'])) {
    $aluno_id = (int) $_GET['id'];
    gerarCracha($aluno_id);
}
?>