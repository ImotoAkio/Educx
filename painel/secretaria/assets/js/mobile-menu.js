/**
 * Mobile Menu JavaScript - Secretaria
 * Sistema de menu mobile responsivo para o painel da secretaria
 */

class MobileMenuSecretaria {
    constructor() {
        this.menuToggle = document.querySelector('.menu-toggle');
        this.mobileMenu = document.querySelector('.mobile-menu');
        this.menuOverlay = document.querySelector('.mobile-menu-overlay');
        this.menuClose = document.querySelector('.menu-close');
        this.menuItems = document.querySelectorAll('.menu-item');
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.handleResize();
        this.setupSwipeGestures();
    }

    bindEvents() {
        // Toggle menu
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMenu();
            });
        }

        // Close menu
        if (this.menuClose) {
            this.menuClose.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeMenu();
            });
        }

        // Close menu when clicking overlay
        if (this.menuOverlay) {
            this.menuOverlay.addEventListener('click', (e) => {
                if (e.target === this.menuOverlay) {
                    this.closeMenu();
                }
            });
        }

        // Close menu when clicking menu items
        this.menuItems.forEach(item => {
            item.addEventListener('click', () => {
                this.closeMenu();
            });
        });

        // Close menu with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen()) {
                this.closeMenu();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResize();
            }, 100);
        });
    }

    setupSwipeGestures() {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let endY = 0;

        // Touch start
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        // Touch end
        document.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            endY = e.changedTouches[0].clientY;
            
            this.handleSwipe(startX, startY, endX, endY);
        }, { passive: true });
    }

    handleSwipe(startX, startY, endX, endY) {
        const deltaX = endX - startX;
        const deltaY = endY - startY;
        const minSwipeDistance = 50;

        // Check if it's a horizontal swipe
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            // Swipe right to open menu (from left edge)
            if (deltaX > 0 && startX < 50 && !this.isMenuOpen()) {
                this.openMenu();
            }
            // Swipe left to close menu
            else if (deltaX < 0 && this.isMenuOpen()) {
                this.closeMenu();
            }
        }
    }

    toggleMenu() {
        if (this.isMenuOpen()) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }

    openMenu() {
        if (this.mobileMenu && this.menuOverlay) {
            this.mobileMenu.classList.add('active');
            this.menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Add loading state
            if (this.menuToggle) {
                this.menuToggle.parentElement.classList.add('loading');
                setTimeout(() => {
                    this.menuToggle.parentElement.classList.remove('loading');
                }, 300);
            }
        }
    }

    closeMenu() {
        if (this.mobileMenu && this.menuOverlay) {
            this.mobileMenu.classList.remove('active');
            this.menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    isMenuOpen() {
        return this.mobileMenu && this.mobileMenu.classList.contains('active');
    }

    handleResize() {
        const isMobile = window.innerWidth <= 768;
        
        if (!isMobile) {
            this.closeMenu();
        }
    }

    // Public methods for external use
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="nc-icon nc-bell-55"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add to mobile header
        const mobileHeader = document.querySelector('.mobile-header');
        if (mobileHeader) {
            mobileHeader.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MobileMenuSecretaria();
});

// Export for global access
window.MobileMenuSecretaria = MobileMenuSecretaria;
