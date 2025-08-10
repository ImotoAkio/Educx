<?php
session_start();
require 'db.php'; // Inclui a conexão com o banco de dados

// Gerar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica o token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token CSRF inválido.");
    }

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
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            // Redireciona com base no tipo de usuário
            if ($usuario['tipo_usuario'] === 'professor') {
                $_SESSION['professor_id'] = $usuario['id'];
                header("Location: painel/professor/professor_dashboard.php");
            } elseif ($usuario['tipo_usuario'] === 'secretaria') {
                header("Location: painel/secretaria/paginas/dashboard.php");
            }
            exit();
        } else {
            $erro = "Senha inválida.";
        }
    } else {
        $erro = "E-mail não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow"> <!-- Instruções para indexar e seguir links -->
    <meta name="author" content="Sua Empresa ou Nome"> <!-- Autoria do site -->
    <meta name="description"
        content="Plataforma de gamificação escolar que recompensa os alunos com moedas, XP e benefícios por atitudes positivas e boas ações. Motivação e desenvolvimento pessoal e acadêmico.">
    <!-- Descrição do site para SEO -->
    <meta name="keywords"
        content="gamificação escolar, recompensas, XP, moedas, educação, motivação, comportamento positivo, plataformas educacionais, gamificação para alunos, plataforma de aprendizado">
    <!-- Palavras-chave -->
    <meta property="og:type" content="website"> <!-- Tipo de conteúdo para Open Graph -->
    <meta property="og:title" content="Plataforma de Gamificação Escolar"> <!-- Título para redes sociais -->
    <meta property="og:description"
        content="Plataforma de gamificação escolar que recompensa os alunos por atitudes positivas com moedas, XP e novos níveis.">
    <!-- Descrição para Open Graph -->
    <meta property="og:image" content="assets/img/logo.png"> <!-- Imagem para pré-visualização no Facebook -->
    <meta property="og:url" content="URL_DO_SEU_SITE"> <!-- URL para redes sociais -->
    <meta property="og:site_name" content="Plataforma de Gamificação Escolar"> <!-- Nome do site para Open Graph -->
    <meta name="twitter:card" content="summary_large_image"> <!-- Card para Twitter -->
    <meta name="twitter:site" content="@SeuTwitter"> <!-- Twitter handle -->
    <meta name="twitter:title" content="Plataforma de Gamificação Escolar"> <!-- Título para Twitter -->
    <meta name="twitter:description"
        content="Plataforma de gamificação escolar que motiva os alunos com recompensas por atitudes positivas.">
    <!-- Descrição para Twitter -->
    <meta name="twitter:image" content="assets/img/logo.png"> <!-- Imagem para Twitter -->
    <link rel="icon" href="assets/img/favicon.png" sizes="20x20" type="image/png"> <!-- Ícone do site -->
    <link rel="canonical" href="URL_DO_SEU_SITE"> <!-- URL canônica para SEO -->

    <!-- Título da Página -->
    <title>Login</title> <!-- Título da página -->

    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.min.css">
    <link rel="stylesheet" href="assets/css/magnific.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- cursor -->
    <div class="cursor"></div>
    <div class="cursor-follower"></div>
    <!-- cursor End -->
     
    <!-- search popup start-->


    <!-- navbar start -->
    <nav class="navbar navbar-area navbar-area-1 navbar-border navbar-expand-lg">
        <div class="container nav-container px-lg-0">
            <div class="responsive-mobile-menu">
                <button class="menu toggle-btn d-block d-lg-none" data-target="#xdyat" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="icon-left"></span>
                    <span class="icon-right"></span>
                </button>
            </div>
            <div class="logo">
                <a href="index.html"><img src="assets/img/logo.png" alt="Logo da Plataforma"></a>
            </div>
            <div class="collapse navbar-collapse" id="xdyat_main_menu">
                <ul class="navbar-nav menu-open ps-lg-5 pe-xl-4 text-end">
                    <!-- Home -->
                    <li class="menu-item-has-children">
                        <a class="active" href="#">Início</a>
                        <ul class="sub-menu">
                            <li><a href="index.html">Página Inicial</a></li>
                        </ul>
                    </li>
            
                    <!-- Missões -->
                    <li><a href="#missoes-box">Missões</a></li>
            
                    <!-- Recompensas -->
                    <li><a href="#recompensas-box">Recompensas</a></li>
            
                    <!-- Sobre -->
                    <li><a href="#sobre-box">Sobre</a></li>
            
                    <!-- Contato 
                    <li><a href="#contato-box">Contato</a></li>-->
                </ul>
            </div>

            <!-- Itens da navegação para desktop -->
            <div class="nav-right-part nav-right-part-desktop d-lg-inline-flex align-item-center">
                <!-- Busca -->




                <!-- Botão de Conexão -->
                <div class="btn-box d-inline-block">
                    <button class="me-3 header-menu-toggle bg-transparent border-0 shadow-0" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasright" aria-controls="offcanvasright">
                        <svg width="24" height="21" viewBox="0 0 24 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0V3H24V0H0ZM24 21H16V18H24V21ZM24 12H8V9H24V12Z" fill="#fff" />
                        </svg>
                    </button>
                    <a class="btn btn-main style-small" href="#">
                        <span>
                            <span>
                                <img src="assets/img/btn-arrow.png" alt="img">
                                ~
                            </span>
                            <span>Conectar</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- navbar end -->

    <!-- off canvas -->
    <div class="offcanvas offcanvas-menu-wrap offcanvas-end" tabindex="-1" id="offcanvasright">
        <div class="offcanvas-menu-inner">
            <button type="button" class="offcanvas-icon" data-bs-dismiss="offcanvas" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.9997 10.5865L16.9495 5.63672L18.3637 7.05093L13.4139 12.0007L18.3637 16.9504L16.9495 18.3646L11.9997 13.4149L7.04996 18.3646L5.63574 16.9504L10.5855 12.0007L5.63574 7.05093L7.04996 5.63672L11.9997 10.5865Z">
                    </path>
                </svg>
            </button>
            <div class="sidebar-inner">
                <!-- Logo -->
                <div class="thumb">
                    <img src="assets/img/logo.png" alt="Logo da Plataforma">
                </div>

                <!-- Descrição -->
                <p>Bem-vindo à nossa plataforma de gamificação escolar! Aqui, valorizamos cada atitude positiva e
                    esforço de nossos alunos. A cada missão cumprida, você conquista recompensas e avança no
                    aprendizado!</p>

                <!-- Informações de contato -->
                <div class="sidebar-address">
                    <h4 class="mb-3">Contato</h4>
                    <ul>
                        <li><i class="fa fa-map-marker-alt"></i> Avenida 01, nº 86 - Quati, Petrolina - PE</li>
                        <li><i class="fa fa-envelope"></i> ccontato@echotec.site</li>
                        <li><i class="fa fa-phone"></i> (87) 9 9168-2773</li>
                    </ul>
                </div>

                <!-- Redes sociais -->
                <ul class="social-media social-media-light">
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-pinterest"></i></a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- off canvas end -->



    <!-- product-cart start -->
    <div class="product-cart-area bg-color-50 pd-top-120 pd-bottom-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 pe-xl-5">
                    <div class="pe-xl-5 pe-lg-4">
                        <div class="section-title">
                            <div class="row">
                                <h6 class="subtitle tt-uppercase">Seja bem vindo!</h6>
                                <h2 class="title">Fazer <span>Login</span></h2>
                                <p class="mb-0 mt-3">Ainda não é membro? <a class="color-base" href="#">Contato</a></p>
                            </div>
                        </div>
                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="login-form-inner">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <div class="single-input-inner style-border">
                                <label for="email" class="visually-hidden">E-mail</label>
                                <input name="email" id="email" type="email" placeholder="E-mail" required>
                                <span><img src="assets/img/icon/17.png" alt="Ícone de e-mail"></span>
                            </div>
                            <div class="single-input-inner style-border">
                                <label for="senha" class="visually-hidden">Senha</label>
                                <input name="senha" id="senha" type="password" placeholder="Senha" required>
                                <span><img src="assets/img/icon/18.png" alt="Ícone de senha"></span>
                            </div>
                            <button class="btn btn-base tt-uppercase w-100" type="submit">Log in</button>
                            <div class="text-md-end mt-4 tt-uppercase">
                                <a href="#" class="text-white">Esqueci minha senha</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6 ps-xl-5 mt-lg-0 mt-5">
                    <div class="ps-xl-5 ps-lg-4">
                        <img class="w-100" src="assets/img/login.png" alt="img">
                        <div class="login-social-btn mt-4">
                            <a class="tt-uppercase w-100 border-1 d-block w-100 border-radius-6 p-4" href="#">
                                <i class="fab fa-facebook me-2"></i>
                                login  
                                <i class="fa fa-angle-right float-right"></i>
                            </a>
                            <a class="tt-uppercase w-100 border-1 d-block w-100 border-radius-6 p-4" href="#">
                                <i class="fab fa-apple me-2"></i>
                                login  
                                <i class="fa fa-angle-right float-right"></i>
                            </a>
                            <a class="tt-uppercase w-100 border-1 d-block w-100 border-radius-6 p-4" href="#">
                                <i class="fab fa-google me-2"></i>
                                login  
                                <i class="fa fa-angle-right float-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- product-cart end -->

    <!-- footer area start -->
    <footer class="footer-area footer-area-1 pd-top-110" style="background-image: url('./assets/img/footer/bg.png');">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="widget widget_about">
                        <div class="thumb">
                            <img src="assets/img/logo.png" alt="img">
                        </div>
                        <div class="details">
                            <p class="mb-3">
                                EducX - Avenida 01, nº 86 - Quati, Petrolina - PE <br>
                                +55 (87) 9 9168-2773 <br>
                                ccontato@echotec.site
                            </p>
                            <h5>Horário de Atendimento</h5>
                            <p><strong>Segunda a Sexta <span class="color-base">08:00-18:00</span></strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="widget widget_nav_menu ps-xl-5">
                        <h4 class="widget-title">Links Importantes</h4>
                        <ul>
                            <li><a href="sobre.html">Sobre Nós</a></li>
                            <li><a href="como-funciona.html">Como Funciona</a></li>
                            <li><a href="login.html">Minha Conta</a></li>
                            <li><a href="contato.html">Contato</a></li>
                            <li><a href="faq.html">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="widget widget_subscribe">
                        <h4 class="widget-title">Inscreva-se</h4>
                        <p class="mb-3">Receba dicas e novidades sobre gamificação e educação diretamente no seu e-mail!</p>
                        <form action="#">
                            <div class="single-input-inner">
                                <input type="email" placeholder="seuemail@dominio.com">
                            </div>
                            <div class="btn-box d-inline-block">
                                <button class="btn btn-main style-small">
                                    <span>
                                        <span>Inscrever</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="widget widget_instagram ps-xl-5">
                        <h4 class="widget-title">Nos Siga no Instagram</h4>
                        <div class="widget widget_contact">
                            <ul>
                                <li>
                                    <img src="assets/img/footer/1.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li>
                                    <img src="assets/img/footer/2.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li>
                                    <img src="assets/img/footer/3.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li>
                                    <img src="assets/img/footer/4.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li>
                                    <img src="assets/img/footer/5.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                                <li>
                                    <img src="assets/img/footer/6.png" alt="img">
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 align-self-center">
                        <p>© 2025 EducX, Todos os Direitos Reservados</p>
                    </div>
                    <div class="col-lg-6 text-lg-end">
                        <img src="assets/img/footer/7.png" alt="img">
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- footer area end -->

    <!-- back to top area start -->
    <div class="back-to-top">
        <span class="back-top"><i class="fa fa-angle-up"></i></span>
    </div>
    <!-- back to top area end -->

    <!-- all plugins here -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/fontawesome.min.js"></script>
    <script src="assets/js/magnific.min.js"></script>
    <script src="assets/js/nice-select.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/counter-up.min.js"></script>
    <script src="assets/js/waypoint.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/SplitText.min.js"></script>
    <script src="assets/js/ScrollTrigger.min.js"></script>
    <script src="assets/js/gsap.min.js"></script>
    <script src="assets/js/lenis.min.js"></script>
    
    <!-- main js  -->
    <script src="assets/js/main.js"></script>
    
</body>
</html>