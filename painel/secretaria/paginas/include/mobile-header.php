<?php
/**
 * Mobile Header Component - Secretaria
 * Componente reutilizável para o header mobile do painel da secretaria
 */
?>

<!-- Mobile Header -->
<div class="mobile-header">
    <div class="mobile-header-content">
        <!-- Menu Toggle Button -->
        <button class="menu-toggle" type="button" aria-label="Abrir menu">
            <i class="nc-icon nc-bullet-list-67"></i>
        </button>
        
        <!-- Logo -->
        <a href="dashboard.php" class="mobile-logo">
            <img src="../assets/img/logo-small.png" alt="Logo Secretaria">
            <span>Secretaria</span>
        </a>
        
        <!-- Profile Button -->
        <button class="profile-btn" type="button" aria-label="Perfil" onclick="showProfileMenu()">
            <i class="nc-icon nc-single-02"></i>
        </button>
    </div>
</div>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay"></div>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <!-- Menu Header -->
    <div class="menu-header">
        <a href="dashboard.php" class="menu-logo">
            <i class="nc-icon nc-bank"></i>
            Painel da Secretaria
        </a>
        <button class="menu-close" type="button" aria-label="Fechar menu">
            <i class="nc-icon nc-simple-remove"></i>
        </button>
    </div>
    
    <!-- Menu Items -->
    <div class="menu-items">
        <!-- Dashboard -->
        <a href="dashboard.php" class="menu-item <?= $pagina_ativa === 'dashboard' ? 'active' : ''; ?>">
            <i class="nc-icon nc-bank"></i>
            <p>Dashboard</p>
        </a>
        
        <!-- Grupo: Aprovações -->
        <div class="menu-section-title" data-toggle="collapse" data-target="#mobile-aprovacoes" aria-expanded="<?= (in_array($pagina_ativa, ['tables', 'missoes'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down menu-section-arrow"></i>
            <span>APROVAÇÕES</span>
        </div>
        <div id="mobile-aprovacoes" class="collapse <?= (in_array($pagina_ativa, ['tables', 'missoes'])) ? 'show' : ''; ?>">
            <a href="tables.php" class="menu-item menu-subitem <?= $pagina_ativa === 'tables' ? 'active' : ''; ?>">
                <i class="nc-icon nc-lock-circle-open"></i>
                <p>Aprovar Compras</p>
            </a>
            <a href="missoes.php" class="menu-item menu-subitem <?= $pagina_ativa === 'missoes' ? 'active' : ''; ?>">
                <i class="nc-icon nc-user-run"></i>
                <p>Aprovar Missões</p>
            </a>
        </div>
        
        <!-- Grupo: Usuários -->
        <div class="menu-section-title" data-toggle="collapse" data-target="#mobile-usuarios" aria-expanded="<?= (in_array($pagina_ativa, ['editar_professor', 'editar_aluno', 'editar_secretaria'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down menu-section-arrow"></i>
            <span>USUÁRIOS</span>
        </div>
        <div id="mobile-usuarios" class="collapse <?= (in_array($pagina_ativa, ['editar_professor', 'editar_aluno', 'editar_secretaria'])) ? 'show' : ''; ?>">
            <a href="editar_professor.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_professor' ? 'active' : ''; ?>">
                <i class="nc-icon nc-glasses-2"></i>
                <p>Professores</p>
            </a>
            <a href="editar_aluno.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_aluno' ? 'active' : ''; ?>">
                <i class="nc-icon nc-single-02"></i>
                <p>Alunos</p>
            </a>
            <a href="editar_secretaria.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_secretaria' ? 'active' : ''; ?>">
                <i class="nc-icon nc-badge"></i>
                <p>Secretários</p>
            </a>
        </div>
        
        <!-- Grupo: Conteúdo -->
        <div class="menu-section-title" data-toggle="collapse" data-target="#mobile-conteudo" aria-expanded="<?= (in_array($pagina_ativa, ['editar_loja', 'editar_missoes', 'editar_atitudes'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down menu-section-arrow"></i>
            <span>CONTEÚDO</span>
        </div>
        <div id="mobile-conteudo" class="collapse <?= (in_array($pagina_ativa, ['editar_loja', 'editar_missoes', 'editar_atitudes'])) ? 'show' : ''; ?>">
            <a href="editar_loja.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_loja' ? 'active' : ''; ?>">
                <i class="nc-icon nc-basket"></i>
                <p>Loja</p>
            </a>
            <a href="editar_missoes.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_missoes' ? 'active' : ''; ?>">
                <i class="nc-icon nc-controller-modern"></i>
                <p>Missões</p>
            </a>
            <a href="editar_atitudes.php" class="menu-item menu-subitem <?= $pagina_ativa === 'editar_atitudes' ? 'active' : ''; ?>">
                <i class="nc-icon nc-bullet-list-67"></i>
                <p>Atitudes</p>
            </a>
        </div>
        
        <!-- Grupo: Turmas -->
        <div class="menu-section-title" data-toggle="collapse" data-target="#mobile-turmas" aria-expanded="<?= (in_array($pagina_ativa, ['turmas', 'gerenciar_alunos_turmas', 'gerenciar_professores_turmas'])) ? 'true' : 'false'; ?>">
            <i class="nc-icon nc-minimal-down menu-section-arrow"></i>
            <span>TURMAS</span>
        </div>
        <div id="mobile-turmas" class="collapse <?= (in_array($pagina_ativa, ['turmas', 'gerenciar_alunos_turmas', 'gerenciar_professores_turmas'])) ? 'show' : ''; ?>">
            <a href="turmas.php" class="menu-item menu-subitem <?= $pagina_ativa === 'turmas' ? 'active' : ''; ?>">
                <i class="nc-icon nc-chart-bar-32"></i>
                <p>Gerenciar Turmas</p>
            </a>
            <a href="gerenciar_alunos_turmas.php" class="menu-item menu-subitem <?= $pagina_ativa === 'gerenciar_alunos_turmas' ? 'active' : ''; ?>">
                <i class="nc-icon nc-circle-09"></i>
                <p>Alunos e Turmas</p>
            </a>
            <a href="gerenciar_professores_turmas.php" class="menu-item menu-subitem <?= $pagina_ativa === 'gerenciar_professores_turmas' ? 'active' : ''; ?>">
                <i class="nc-icon nc-tie-bow"></i>
                <p>Professores e Turmas</p>
            </a>
        </div>
    </div>

    <style>
        .menu-section-title {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(0, 0, 0, 0.6);
            margin: 20px 20px 8px 20px;
            padding: 10px 0 6px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            user-select: none;
            transition: all 0.3s ease;
        }

        .menu-section-title:hover {
            color: rgba(0, 0, 0, 0.8);
            background: rgba(0, 0, 0, 0.02);
            padding-left: 10px;
            border-radius: 4px;
        }

        .menu-section-title:first-of-type {
            margin-top: 10px;
        }

        .menu-section-arrow {
            font-size: 12px;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .menu-section-title[aria-expanded="true"] .menu-section-arrow {
            transform: rotate(0deg);
        }

        .menu-section-title[aria-expanded="false"] .menu-section-arrow {
            transform: rotate(-90deg);
        }

        .menu-subitem {
            padding-left: 50px !important;
            font-size: 13px;
        }

        .menu-subitem i {
            font-size: 18px;
        }
    </style>

    <script>
      (function() {
        function initMobileCollapse() {
          // Salvar estado dos menus mobile no localStorage
          const collapseElements = document.querySelectorAll('.mobile-menu [data-toggle="collapse"]');
          
          collapseElements.forEach(function(element) {
            const target = element.getAttribute('data-target');
            const collapseEl = document.querySelector(target);
            
            if (!collapseEl) return;
            
            // Verificar estado salvo
            const storedState = localStorage.getItem('mobile_' + target);
            const currentState = collapseEl.classList.contains('show');
            const isActive = collapseEl.querySelector('.active');
            
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
              localStorage.setItem('mobile_' + target, 'true');
              element.setAttribute('aria-expanded', 'true');
            });
            
            collapseEl.addEventListener('hide.bs.collapse', function() {
              localStorage.setItem('mobile_' + target, 'false');
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
          document.addEventListener('DOMContentLoaded', initMobileCollapse);
        } else {
          if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(initMobileCollapse);
          } else {
            initMobileCollapse();
          }
        }
      })();
    </script>
    
    <!-- Menu Footer -->
    <div class="menu-footer">
        <a href="../../logout.php" class="logout-btn">
            <i class="nc-icon nc-button-power"></i>
            Sair
        </a>
    </div>
</div>

<script>
// Profile menu functionality
function showProfileMenu() {
    // Simple profile menu - can be enhanced later
    if (window.MobileMenuSecretaria) {
        window.MobileMenuSecretaria.showNotification('Menu de perfil em desenvolvimento', 'info');
    }
}

// Initialize mobile menu when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add any additional initialization here
    console.log('Mobile header da secretaria carregado');
});
</script>
