<?php
// Definir a página ativa - deve ser definida antes de incluir este arquivo
$pagina_ativa = $pagina_ativa ?? 'dashboard';
?>
<body class="">
<div class="wrapper">
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
        </ul>
      </div>
    </div>

    <!-- Painel principal para dispositivos móveis -->
    <div class="main-panel d-md-none"> <!-- Visível apenas em dispositivos móveis -->
      <!-- Aqui vai o conteúdo do painel principal -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="navbar-toggler-icon" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item <?= $pagina_ativa === 'dashboard' ? 'active' : '' ?>">
              <a class="nav-link" href="dashboard.php">Dashboard <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item <?= $pagina_ativa === 'missoes' ? 'active' : '' ?>">
              <a class="nav-link" href="./missoes.php">Aprovar Missões</a>
            </li>
            <li class="nav-item <?= $pagina_ativa === 'editar_missoes' ? 'active' : '' ?>">
              <a class="nav-link" href="./editar_missoes.php">Editar Missões</a>
            </li>
            <li class="nav-item <?= $pagina_ativa === 'criar_quiz' ? 'active' : '' ?>">
              <a class="nav-link" href="./criar_quiz.php">Criar Quiz</a>
            </li>
          </ul>
        </div>
      </nav>
    </div>

    <!-- Painel principal para telas grandes -->
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
        <div class="container-fluid">
          <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
              <li class="nav-item btn-rotate dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="nc-icon nc-bell-55"></i>
                  <p>
                    <span class="d-lg-none d-md-block">Some Actions</span>
                  </p>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                  <a class="dropdown-item" href="#">Action</a>
                  <a class="dropdown-item" href="#">Another action</a>
                  <a class="dropdown-item" href="#">Something else here</a>
                </div>
              </li>
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