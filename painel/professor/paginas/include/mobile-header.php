<?php
// Buscar dados do professor
$professor_nome = $_SESSION['usuario_nome'] ?? 'Professor';
$professor_avatar = 'assets/img/default-avatar.png'; // Avatar padrão
?>

<!-- Mobile Header -->
<header class="mobile-header" id="mobileHeader">
    <div class="mobile-header-container">
        <!-- Logo e Título -->
        <a href="dashboard.php" class="mobile-header-brand">
            <img src="../assets/img/logo-small.png" alt="Logo" onerror="this.src='../assets/img/default-avatar.png'">
            <span>Painel Professor</span>
        </a>
        
        <!-- Botões de Ação -->
        <div class="mobile-header-actions">
            <!-- Botão de Notificações -->
            
            <!-- Botão de Perfil -->
            <button class="mobile-profile-btn" id="mobileProfileBtn" title="Perfil">
                👤
            </button>
            
            <!-- Botão de Menu Hambúrguer -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" title="Menu">
                <div class="hamburger"></div>
                <div class="hamburger"></div>
                <div class="hamburger"></div>
            </button>
        </div>
    </div>
</header>

<!-- Overlay para fechar menu -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Menu Lateral Mobile -->
<nav class="mobile-sidebar" id="mobileSidebar">
    <!-- Seção de Perfil -->
    <div class="mobile-profile-section">
        <img src="<?= $professor_avatar ?>" alt="Avatar" class="mobile-profile-avatar" onerror="this.src='../assets/img/default-avatar.png'">
        <h3 class="mobile-profile-name"><?= htmlspecialchars($professor_nome) ?></h3>
        <p class="mobile-profile-role">Professor</p>
    </div>
    
    <!-- Lista de Navegação -->
    <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
            <a href="dashboard.php" class="mobile-nav-link <?= $pagina_ativa === 'dashboard' ? 'active' : '' ?>">
                <i class="mobile-nav-icon">📊</i>
                <span class="mobile-nav-text">Dashboard</span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="missoes.php" class="mobile-nav-link <?= $pagina_ativa === 'missoes' ? 'active' : '' ?>">
                <i class="mobile-nav-icon">✅</i>
                <span class="mobile-nav-text">Aprovar Missões</span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="editar_missoes.php" class="mobile-nav-link <?= $pagina_ativa === 'editar_missoes' ? 'active' : '' ?>">
                <i class="mobile-nav-icon">✏️</i>
                <span class="mobile-nav-text">Editar Missões</span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="criar_quiz.php" class="mobile-nav-link <?= $pagina_ativa === 'criar_quiz' ? 'active' : '' ?>">
                <i class="mobile-nav-icon">📝</i>
                <span class="mobile-nav-text">Criar Quiz</span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="editar_quiz.php" class="mobile-nav-link <?= $pagina_ativa === 'editar_quiz' ? 'active' : '' ?>">
                <i class="mobile-nav-icon">📋</i>
                <span class="mobile-nav-text">Editar Quiz</span>
            </a>
        </li>
        
    </ul>
    
    <!-- Ações do Menu -->
    <div class="mobile-menu-actions">
        <a href="../professor_dashboard.php" class="mobile-action-btn">
            <i class="mobile-action-icon">👥</i>
            Gerenciar Alunos
        </a>
        
        
        <a href="../logout.php" class="mobile-action-btn" style="color: #dc3545;">
            <i class="mobile-action-icon">🚪</i>
            Sair
        </a>
    </div>
</nav>

