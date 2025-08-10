<?php
session_start(); // Inicia a sessão

// Destrói todas as variáveis de sessão
$_SESSION = [];
session_unset();
session_destroy();

// Redireciona o usuário para a página de login
header("Location: ../login.php");
exit();
?>
