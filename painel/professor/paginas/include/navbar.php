<?php
// Definir a página ativa - deve ser definida antes de incluir este arquivo
$pagina_ativa = $pagina_ativa ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor</title>
    
    <!-- CSS do Bootstrap -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    
    <!-- CSS do Paper Dashboard -->
    <link rel="stylesheet" href="../assets/css/paper-dashboard.css">
    
    <!-- CSS do Mobile Header -->
    <link rel="stylesheet" href="../assets/css/mobile-header.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS customizado -->
    <style>
        /* Ajustes para mobile */
        @media (max-width: 768px) {
            .main-panel {
                margin-top: 60px !important;
                padding-top: 20px;
            }
            
            .sidebar {
                display: none !important;
            }
            
            .navbar-absolute {
                display: none !important;
            }
        }
        
        /* Melhorias gerais */
        .mobile-header {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .mobile-sidebar {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="">
<div class="wrapper">
    <!-- Sidebar Desktop -->
    <div class="sidebar" data-color="white" data-active-color="danger">
      <div class="logo">
        <a href="dashboard.php" class="simple-text logo-mini">
          <div class="logo-image-small">
            <img src="../assets/img/logo-small.png" alt="Logo">
          </div>
        </a>
        <a href="dashboard.php" class="simple-text logo-normal">
          Painel
        </a>
      </div>
      <div class="sidebar-wrapper">
        <ul class="nav">
          <li class="<?= $pagina_ativa === 'dashboard' ? 'active' : '' ?>">
            <a href="./dashboard.php">
              <i class="nc-icon nc-bank"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="<?= $pagina_ativa === 'missoes' ? 'active' : '' ?>">
            <a href="./missoes.php">
              <i class="nc-icon nc-user-run"></i>
              <p>Aprovar Missões</p>
            </a>
          </li>
          <li class="<?= $pagina_ativa === 'editar_missoes' ? 'active' : '' ?>">
            <a href="./editar_missoes.php">
              <i class="nc-icon nc-controller-modern"></i>
              <p>Editar Missões</p>
            </a>
          </li>
          <li class="<?= $pagina_ativa === 'criar_quiz' ? 'active' : '' ?>">
            <a href="./criar_quiz.php">
              <i class="nc-icon nc-single-copy-04"></i>
              <p>Criar Quiz</p>
            </a>
          </li>
          <li class="<?= $pagina_ativa === 'editar_quiz' ? 'active' : '' ?>">
            <a href="./editar_quiz.php">
              <i class="nc-icon nc-single-copy-04"></i>
              <p>Editar Quiz</p>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Mobile Header -->
    <?php include 'mobile-header.php'; ?>
    
    

    <!-- Painel principal para telas grandes -->
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
        <div class="container-fluid">
          <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link btn-rotate" href="javascript:;" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                  <i class="nc-icon nc-settings-gear-65"></i>
                  <p>
                    <span class="d-lg-none d-md-block">Account</span>
                  </p>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- End Navbar -->
      
      <!-- Conteúdo Principal -->
      <div class="content">
        <div class="container-fluid">