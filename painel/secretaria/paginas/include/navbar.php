<?php
// Sidebar centralizada para o painel da secretaria
// A variável $pagina_ativa deve ser definida em cada página antes de incluir este arquivo
?>

<!-- Mobile Header -->
<?php include 'mobile-header.php'; ?>

<div class="wrapper">
  <!-- Desktop Sidebar -->
  <div class="sidebar d-none d-md-block" data-color="white" data-active-color="danger">
    <div class="logo">
      <a href="dashboard.php" class="simple-text logo-mini">
        <div class="logo-image-small">
          <img src="../assets/img/logo-small.png" alt="Logo">
        </div>
      </a>
      <a href="dashboard.php" class="simple-text logo-normal">
        Painel da Secretaria
      </a>
    </div>
    <div class="sidebar-wrapper">
      <ul class="nav">
        <li class="<?= $pagina_ativa === 'dashboard' ? 'active' : ''; ?>">
          <a href="./dashboard.php">
            <i class="nc-icon nc-bank"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'tables' ? 'active' : ''; ?>">
          <a href="./tables.php">
            <i class="nc-icon nc-lock-circle-open"></i>
            <p>Aprovar Compras</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'missoes' ? 'active' : ''; ?>">
          <a href="./missoes.php">
            <i class="nc-icon nc-user-run"></i>
            <p>Aprovar Missões</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'editar_professor' ? 'active' : ''; ?>">
          <a href="./editar_professor.php">
            <i class="nc-icon nc-glasses-2"></i>
            <p>Editar Professores</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'editar_aluno' ? 'active' : ''; ?>">
          <a href="./editar_aluno.php">
            <i class="nc-icon nc-single-02"></i>
            <p>Editar Alunos</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'editar_secretaria' ? 'active' : ''; ?>">
          <a href="./editar_secretaria.php">
            <i class="nc-icon nc-badge"></i>
            <p>Editar Secretários</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'editar_loja' ? 'active' : ''; ?>">
          <a href="./editar_loja.php">
            <i class="nc-icon nc-basket"></i>
            <p>Editar Loja</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'editar_missoes' ? 'active' : ''; ?>">
          <a href="./editar_missoes.php">
            <i class="nc-icon nc-controller-modern"></i>
            <p>Editar Missões</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'turmas' ? 'active' : ''; ?>">
          <a href="./turmas.php">
            <i class="nc-icon nc-chart-bar-32"></i>
            <p>Gerenciar Turmas</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'gerenciar_alunos_turmas' ? 'active' : ''; ?>">
          <a href="./gerenciar_alunos_turmas.php">
            <i class="nc-icon nc-single-02"></i>
            <p>Alunos e Turmas</p>
          </a>
        </li>
        <li class="<?= $pagina_ativa === 'gerenciar_professores_turmas' ? 'active' : ''; ?>">
          <a href="./gerenciar_professores_turmas.php">
            <i class="nc-icon nc-single-02"></i>
            <p>Professores e Turmas</p>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Painel principal -->
  <div class="main-panel">
    <!-- Desktop Navbar -->
    <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent d-none d-md-block">
      <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-end" id="navigation">
          <ul class="navbar-nav">
            <li class="nav-item btn-rotate dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="nc-icon nc-bell-55"></i>
                <p>
                  <span class="d-lg-none d-md-block">Notificações</span>
                </p>
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" href="tables.php">Trocas Pendentes</a>
                <a class="dropdown-item" href="missoes.php">Missões Pendentes</a>
                <a class="dropdown-item" href="dashboard.php">Dashboard</a>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link btn-rotate" href="javascript:;" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                <i class="nc-icon nc-settings-gear-65"></i>
                <p>
                  <span class="d-lg-none d-md-block">Configurações</span>
                </p>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
