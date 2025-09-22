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
        <a href="dashboard.php" class="menu-item <?= $pagina_ativa === 'dashboard' ? 'active' : ''; ?>">
            <i class="nc-icon nc-bank"></i>
            <p>Dashboard</p>
        </a>
        
        <a href="tables.php" class="menu-item <?= $pagina_ativa === 'tables' ? 'active' : ''; ?>">
            <i class="nc-icon nc-lock-circle-open"></i>
            <p>Aprovar Compras</p>
        </a>
        
        <a href="missoes.php" class="menu-item <?= $pagina_ativa === 'missoes' ? 'active' : ''; ?>">
            <i class="nc-icon nc-user-run"></i>
            <p>Aprovar Missões</p>
        </a>
        
        <a href="editar_professor.php" class="menu-item <?= $pagina_ativa === 'editar_professor' ? 'active' : ''; ?>">
            <i class="nc-icon nc-glasses-2"></i>
            <p>Editar Professores</p>
        </a>
        
        <a href="editar_aluno.php" class="menu-item <?= $pagina_ativa === 'editar_aluno' ? 'active' : ''; ?>">
            <i class="nc-icon nc-single-02"></i>
            <p>Editar Alunos</p>
        </a>
        
        <a href="editar_secretaria.php" class="menu-item <?= $pagina_ativa === 'editar_secretaria' ? 'active' : ''; ?>">
            <i class="nc-icon nc-badge"></i>
            <p>Editar Secretários</p>
        </a>
        
        <a href="editar_loja.php" class="menu-item <?= $pagina_ativa === 'editar_loja' ? 'active' : ''; ?>">
            <i class="nc-icon nc-basket"></i>
            <p>Editar Loja</p>
        </a>
        
        <a href="editar_missoes.php" class="menu-item <?= $pagina_ativa === 'editar_missoes' ? 'active' : ''; ?>">
            <i class="nc-icon nc-controller-modern"></i>
            <p>Editar Missões</p>
        </a>
        
        <a href="turmas.php" class="menu-item <?= $pagina_ativa === 'turmas' ? 'active' : ''; ?>">
            <i class="nc-icon nc-chart-bar-32"></i>
            <p>Gerenciar Turmas</p>
        </a>
        
        <a href="gerenciar_alunos_turmas.php" class="menu-item <?= $pagina_ativa === 'gerenciar_alunos_turmas' ? 'active' : ''; ?>">
            <i class="nc-icon nc-single-02"></i>
            <p>Alunos e Turmas</p>
        </a>
        
        <a href="gerenciar_professores_turmas.php" class="menu-item <?= $pagina_ativa === 'gerenciar_professores_turmas' ? 'active' : ''; ?>">
            <i class="nc-icon nc-single-02"></i>
            <p>Professores e Turmas</p>
        </a>
    </div>
    
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
