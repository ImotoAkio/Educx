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
        <!-- Dashboard -->
        <li class="<?= $pagina_ativa === 'dashboard' ? 'active' : ''; ?>">
          <a href="./dashboard.php">
            <i class="nc-icon nc-bank"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Grupo: Aprovações -->
        <li class="nav-section-title">
          <a href="javascript:void(0);" class="nav-section-header" data-toggle="collapse" data-target="#nav-aprovacoes" aria-expanded="<?= (in_array($pagina_ativa, ['tables', 'missoes'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down nav-section-arrow"></i>
            <p class="nav-section-label">APROVAÇÕES</p>
          </a>
          <ul id="nav-aprovacoes" class="nav-submenu collapse <?= (in_array($pagina_ativa, ['tables', 'missoes'])) ? 'show' : ''; ?>">
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
          </ul>
        </li>

        <!-- Grupo: Usuários -->
        <li class="nav-section-title">
          <a href="javascript:void(0);" class="nav-section-header" data-toggle="collapse" data-target="#nav-usuarios" aria-expanded="<?= (in_array($pagina_ativa, ['editar_professor', 'editar_aluno', 'editar_secretaria'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down nav-section-arrow"></i>
            <p class="nav-section-label">USUÁRIOS</p>
          </a>
          <ul id="nav-usuarios" class="nav-submenu collapse <?= (in_array($pagina_ativa, ['editar_professor', 'editar_aluno', 'editar_secretaria'])) ? 'show' : ''; ?>">
            <li class="<?= $pagina_ativa === 'editar_professor' ? 'active' : ''; ?>">
              <a href="./editar_professor.php">
                <i class="nc-icon nc-glasses-2"></i>
                <p>Professores</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'editar_aluno' ? 'active' : ''; ?>">
              <a href="./editar_aluno.php">
                <i class="nc-icon nc-single-02"></i>
                <p>Alunos</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'editar_secretaria' ? 'active' : ''; ?>">
              <a href="./editar_secretaria.php">
                <i class="nc-icon nc-badge"></i>
                <p>Secretários</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Grupo: Conteúdo -->
        <li class="nav-section-title">
          <a href="javascript:void(0);" class="nav-section-header" data-toggle="collapse" data-target="#nav-conteudo" aria-expanded="<?= (in_array($pagina_ativa, ['editar_loja', 'editar_missoes', 'editar_atitudes'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down nav-section-arrow"></i>
            <p class="nav-section-label">CONTEÚDO</p>
          </a>
          <ul id="nav-conteudo" class="nav-submenu collapse <?= (in_array($pagina_ativa, ['editar_loja', 'editar_missoes', 'editar_atitudes'])) ? 'show' : ''; ?>">
            <li class="<?= $pagina_ativa === 'editar_loja' ? 'active' : ''; ?>">
              <a href="./editar_loja.php">
                <i class="nc-icon nc-basket"></i>
                <p>Loja</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'editar_missoes' ? 'active' : ''; ?>">
              <a href="./editar_missoes.php">
                <i class="nc-icon nc-controller-modern"></i>
                <p>Missões</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'editar_atitudes' ? 'active' : ''; ?>">
              <a href="./editar_atitudes.php">
                <i class="nc-icon nc-bullet-list-67"></i>
                <p>Atitudes</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Grupo: Turmas -->
        <li class="nav-section-title">
          <a href="javascript:void(0);" class="nav-section-header" data-toggle="collapse" data-target="#nav-turmas" aria-expanded="<?= (in_array($pagina_ativa, ['turmas', 'gerenciar_alunos_turmas', 'gerenciar_professores_turmas'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down nav-section-arrow"></i>
            <p class="nav-section-label">TURMAS</p>
          </a>
          <ul id="nav-turmas" class="nav-submenu collapse <?= (in_array($pagina_ativa, ['turmas', 'gerenciar_alunos_turmas', 'gerenciar_professores_turmas'])) ? 'show' : ''; ?>">
            <li class="<?= $pagina_ativa === 'turmas' ? 'active' : ''; ?>">
              <a href="./turmas.php">
                <i class="nc-icon nc-chart-bar-32"></i>
                <p>Gerenciar Turmas</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'gerenciar_alunos_turmas' ? 'active' : ''; ?>">
              <a href="./gerenciar_alunos_turmas.php">
                <i class="nc-icon nc-circle-09"></i>
                <p>Alunos e Turmas</p>
              </a>
            </li>
            <li class="<?= $pagina_ativa === 'gerenciar_professores_turmas' ? 'active' : ''; ?>">
              <a href="./gerenciar_professores_turmas.php">
                <i class="nc-icon nc-tie-bow"></i>
                <p>Professores e Turmas</p>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>

    <style>
      .nav-section-title {
        margin-top: 20px;
        padding: 0;
        cursor: pointer;
        pointer-events: auto;
      }

      .nav-section-title:first-child {
        margin-top: 0;
      }

      .nav-section-header {
        margin: 0 15px;
        padding: 10px 8px !important;
        display: flex !important;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .nav-section-header:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
      }

      .nav-section-arrow {
        font-size: 12px;
        margin-right: 8px;
        transition: transform 0.3s ease;
        width: auto !important;
        float: none !important;
      }

      .nav-section-header[aria-expanded="true"] .nav-section-arrow {
        transform: rotate(0deg);
      }

      .nav-section-header[aria-expanded="false"] .nav-section-arrow {
        transform: rotate(-90deg);
      }

      .nav-section-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #000000;
        margin: 0;
        padding: 0;
        flex: 1;
      }

      .nav-submenu {
        list-style: none;
        padding: 0;
        margin: 0 0 10px 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
      }

      .nav-submenu li {
        margin: 0;
      }

      .nav-submenu li > a {
        margin: 0 15px 0 40px !important;
        padding: 8px 8px !important;
        font-size: 11px;
      }

      .nav-submenu li > a i {
        font-size: 18px;
        margin-right: 10px;
        width: 20px;
      }

      .sidebar .nav > li.nav-section-title > a {
        opacity: 0.8;
      }

      .sidebar .nav > li.nav-section-title > a:hover {
        opacity: 1;
      }

      .sidebar .nav li:not(.nav-section-title):not(.nav-submenu li) {
        margin-top: 0;
      }

      .sidebar .nav li:not(.nav-section-title):not(.nav-submenu li) > a {
        margin: 0 15px;
        padding: 10px 8px;
      }
    </style>

    <script>
      (function() {
        // Aguardar jQuery e Bootstrap estarem prontos
        function initCollapse() {
          // Salvar estado dos menus no localStorage
          const collapseElements = document.querySelectorAll('.nav-section-header[data-toggle="collapse"]');
          
          collapseElements.forEach(function(element) {
            const target = element.getAttribute('data-target');
            const collapseEl = document.querySelector(target);
            
            if (!collapseEl) return;
            
            // Verificar estado salvo
            const storedState = localStorage.getItem('sidebar_' + target);
            const currentState = collapseEl.classList.contains('show');
            const isActive = element.closest('li').querySelector('.active');
            
            // Se a página ativa está neste grupo, expandir
            if (isActive && !currentState) {
              collapseEl.classList.add('show');
              element.setAttribute('aria-expanded', 'true');
            }
            // Se há estado salvo e não há página ativa, aplicar estado salvo
            else if (storedState === 'false' && currentState && !isActive) {
              collapseEl.classList.remove('show');
              element.setAttribute('aria-expanded', 'false');
            }
            
            // Escutar mudanças no estado via Bootstrap events
            collapseEl.addEventListener('show.bs.collapse', function() {
              localStorage.setItem('sidebar_' + target, 'true');
              element.setAttribute('aria-expanded', 'true');
            });
            
            collapseEl.addEventListener('hide.bs.collapse', function() {
              localStorage.setItem('sidebar_' + target, 'false');
              element.setAttribute('aria-expanded', 'false');
            });
            
            // Atualizar aria-expanded quando o estado mudar visualmente
            const observer = new MutationObserver(function(mutations) {
              mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                  const hasShow = collapseEl.classList.contains('show');
                  element.setAttribute('aria-expanded', hasShow.toString());
                }
              });
            });
            
            observer.observe(collapseEl, {
              attributes: true,
              attributeFilter: ['class']
            });
          });
        }
        
        // Tentar inicializar quando DOM estiver pronto
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initCollapse);
        } else {
          // Se jQuery estiver disponível, usar ready
          if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(initCollapse);
          } else {
            initCollapse();
          }
        }
      })();
    </script>
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
