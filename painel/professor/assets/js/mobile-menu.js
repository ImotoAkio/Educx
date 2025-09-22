/**
 * Mobile Menu JavaScript - Painel do Professor
 * Funcionalidades modernas para navegação mobile
 */

class MobileMenu {
    constructor() {
        this.menuToggle = document.getElementById('mobileMenuToggle');
        this.sidebar = document.getElementById('mobileSidebar');
        this.overlay = document.getElementById('mobileOverlay');
        this.notificationBtn = document.getElementById('mobileNotificationBtn');
        this.profileBtn = document.getElementById('mobileProfileBtn');
        this.isMenuOpen = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupNotifications();
        this.setupProfile();
        this.handleResize();
    }
    
    bindEvents() {
        // Toggle do menu
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleMenu());
        }
        
        // Fechar menu ao clicar no overlay
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeMenu());
        }
        
        // Fechar menu ao clicar em links
        const navLinks = document.querySelectorAll('.mobile-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => this.closeMenu());
        });
        
        // Fechar menu ao redimensionar para desktop
        window.addEventListener('resize', () => this.handleResize());
        
        // Fechar menu com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Swipe para fechar menu (mobile)
        this.setupSwipeGestures();
    }
    
    toggleMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        if (!this.sidebar || !this.overlay) return;
        
        this.sidebar.classList.add('active');
        this.overlay.classList.add('active');
        this.menuToggle.classList.add('active');
        this.isMenuOpen = true;
        
        // Animar hambúrguer
        this.animateHamburger(true);
        
        // Prevenir scroll do body
        document.body.style.overflow = 'hidden';
        
        // Focar no primeiro link do menu para acessibilidade
        const firstLink = this.sidebar.querySelector('.mobile-nav-link');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 300);
        }
    }
    
    closeMenu() {
        if (!this.sidebar || !this.overlay) return;
        
        this.sidebar.classList.remove('active');
        this.overlay.classList.remove('active');
        this.menuToggle.classList.remove('active');
        this.isMenuOpen = false;
        
        // Animar hambúrguer
        this.animateHamburger(false);
        
        // Restaurar scroll do body
        document.body.style.overflow = '';
    }
    
    animateHamburger(open) {
        const hamburgers = this.menuToggle.querySelectorAll('.hamburger');
        hamburgers.forEach(hamburger => {
            if (open) {
                hamburger.classList.add('active');
            } else {
                hamburger.classList.remove('active');
            }
        });
    }
    
    setupSwipeGestures() {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let endY = 0;
        
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });
        
        document.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            endY = e.changedTouches[0].clientY;
            
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Swipe da esquerda para direita para abrir menu
            if (diffX < -50 && Math.abs(diffY) < 100 && startX < 50) {
                this.openMenu();
            }
            
            // Swipe da direita para esquerda para fechar menu
            if (diffX > 50 && Math.abs(diffY) < 100 && this.isMenuOpen) {
                this.closeMenu();
            }
        });
    }
    
    setupNotifications() {
        if (!this.notificationBtn) return;
        
        this.notificationBtn.addEventListener('click', () => {
            // Aqui você pode implementar a lógica de notificações
            // Por exemplo, abrir um modal ou redirecionar
            console.log('Notificações clicadas');
            
            // Exemplo: mostrar notificações em um modal
            this.showNotificationModal();
        });
    }
    
    setupProfile() {
        if (!this.profileBtn) return;
        
        this.profileBtn.addEventListener('click', () => {
            // Aqui você pode implementar a lógica do perfil
            // Por exemplo, abrir um dropdown ou modal
            console.log('Perfil clicado');
            
            // Exemplo: mostrar opções do perfil
            this.showProfileDropdown();
        });
    }
    
    showNotificationModal() {
        // Implementar modal de notificações
        // Por enquanto, apenas um alert
        // Sistema de notificações implementado - abrir modal
        $('#modalNotificacoes').modal('show');
    }
    
    showProfileDropdown() {
        // Implementar dropdown do perfil
        // Por enquanto, apenas um alert
        alert('Opções do perfil em desenvolvimento');
    }
    
    handleResize() {
        // Fechar menu se redimensionar para desktop
        if (window.innerWidth > 768 && this.isMenuOpen) {
            this.closeMenu();
        }
    }
    
    // Método público para fechar menu externamente
    closeMenuExternal() {
        this.closeMenu();
    }
    
    // Método público para abrir menu externamente
    openMenuExternal() {
        this.openMenu();
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.mobileMenu = new MobileMenu();
});

// Funções globais para compatibilidade
function toggleMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.toggleMenu();
    }
}

function closeMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.closeMenuExternal();
    }
}

function openMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.openMenuExternal();
    }
}

// Exportar para uso em outros scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileMenu;
}
