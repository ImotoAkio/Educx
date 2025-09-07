<?php
// Configuração do banco de dados
$host = 'localhost'; // Nome do servidor (localhost para XAMPP)
$dbname = 'u894209272_coinz'; // Nome do banco de dados
$username = 'root'; // Usuário do banco de dados (padrão é root no XAMPP)
$password = ''; // Senha do banco de dados (padrão é vazio no XAMPP)

try {
    // Cria a conexão com o banco de dados usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exibe erro caso a conexão falhe
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
