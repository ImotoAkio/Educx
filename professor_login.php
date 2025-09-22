<?php
session_start();
require 'db.php';

// Se já está logado, redirecionar
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'professor') {
    $return_to = $_GET['return_to'] ?? 'painel/professor/professor_dashboard.php';
    header("Location: " . $return_to);
    exit;
}

// Gerar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica o token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $erro = "Token CSRF inválido.";
    } else {
        // Sanitiza os dados do formulário
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

        // Consulta para buscar o usuário pelo e-mail
        $stmt = $pdo->prepare("SELECT * FROM professores WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verifica a senha fornecida com o hash armazenado no banco
            if (password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido - usar as chaves corretas da sessão
                $_SESSION['user_type'] = 'professor';
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['professor_nome'] = $usuario['nome'];

                // Redirecionar para página original ou dashboard
                $return_to = $_POST['return_to'] ?? 'painel/professor/professor_dashboard.php';
                header("Location: " . $return_to);
                exit();
            } else {
                $erro = "Senha inválida.";
            }
        } else {
            $erro = "E-mail não encontrado.";
        }
    }
}

$return_to = $_GET['return_to'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Professor - Educx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 120px;
            height: auto;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-title h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-title p {
            color: #666;
            font-size: 14px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            height: auto;
        }
        
        .form-floating .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-floating label {
            padding: 12px 15px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .app-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        
        .app-info h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .app-info p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .loading {
            display: none;
        }
        
        .loading.show {
            display: inline-block;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <div class="logo">
            <img src="assets/img/logo.png" alt="Educx Logo" onerror="this.style.display='none'">
        </div>
        
        <!-- Título -->
        <div class="login-title">
            <h2>Login Professor</h2>
            <p>Acesse sua conta para gerenciar alunos</p>
        </div>
        
        <!-- Mensagem de erro -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulário de login -->
        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($return_to) ?>">
            
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" required>
                <label for="email">
                    <i class="fas fa-envelope me-2"></i>E-mail
                </label>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha">
                    <i class="fas fa-lock me-2"></i>Senha
                </label>
            </div>
            
            <button type="submit" class="btn btn-login" id="btnLogin">
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </span>
                <span class="loading">
                    <i class="fas fa-spinner fa-spin me-2"></i>Entrando...
                </span>
            </button>
        </form>
        
        <!-- Esqueci minha senha -->
        <div class="forgot-password">
            <a href="#" onclick="alert('Entre em contato com o administrador para recuperar sua senha.')">
                <i class="fas fa-question-circle me-1"></i>Esqueci minha senha
            </a>
        </div>
        
        <!-- Informações do app -->
        <div class="app-info">
            <h6><i class="fas fa-mobile-alt me-2"></i>App Móvel</h6>
            <p>Use o aplicativo móvel para escanear QR codes dos alunos e gerenciar suas recompensas de forma rápida e prática.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validação do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            const btnText = btnLogin.querySelector('.btn-text');
            const loading = btnLogin.querySelector('.loading');
            
            // Mostrar loading
            btnText.classList.add('loading');
            loading.classList.add('show');
            btnLogin.disabled = true;
            
            // Simular delay mínimo para UX
            setTimeout(() => {
                // O formulário será submetido normalmente
            }, 500);
        });
        
        // Validação em tempo real
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
                showFieldError(this, 'E-mail inválido');
            } else {
                this.classList.remove('is-invalid');
                hideFieldError(this);
            }
        });
        
        document.getElementById('senha').addEventListener('blur', function() {
            const senha = this.value;
            
            if (senha && senha.length < 6) {
                this.classList.add('is-invalid');
                showFieldError(this, 'Senha deve ter pelo menos 6 caracteres');
            } else {
                this.classList.remove('is-invalid');
                hideFieldError(this);
            }
        });
        
        function showFieldError(field, message) {
            hideFieldError(field);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
        
        function hideFieldError(field) {
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        // Auto-focus no campo de email
        document.getElementById('email').focus();
        
        // Enter para submeter
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>
