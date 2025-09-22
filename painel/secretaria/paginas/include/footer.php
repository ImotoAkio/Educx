<?php
/**
 * Footer Component - Secretaria
 * Componente reutilizável para o footer do painel da secretaria
 */
?>

<!-- Footer -->
<footer class="footer footer-black footer-white">
    <div class="container-fluid">
        <div class="row">
            <div class="credits ml-auto">
                <span class="copyright">
                    © <?= date('Y'); ?> <strong>Educx</strong> - Sistema de Gamificação Educacional
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- CSS e JavaScript específicos para mobile -->
<link rel="stylesheet" href="../assets/css/mobile-header.css">
<script src="../assets/js/mobile-menu.js"></script>

<!-- Scripts adicionais para responsividade -->
<script>
// Responsive adjustments
function adjustForMobile() {
    const isMobile = window.innerWidth <= 768;
    const mainPanel = document.querySelector('.main-panel');
    
    if (isMobile && mainPanel) {
        mainPanel.style.marginTop = '64px';
        mainPanel.style.paddingTop = '20px';
    } else if (mainPanel) {
        mainPanel.style.marginTop = '';
        mainPanel.style.paddingTop = '';
    }
}

// Initialize on load and resize
document.addEventListener('DOMContentLoaded', adjustForMobile);
window.addEventListener('resize', adjustForMobile);
window.addEventListener('orientationchange', () => {
    setTimeout(adjustForMobile, 100);
});

// Mobile-specific enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add touch-friendly classes to interactive elements
    const interactiveElements = document.querySelectorAll('button, a, input, select, textarea');
    interactiveElements.forEach(element => {
        element.classList.add('touch-friendly');
    });
    
    // Improve form usability on mobile
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Show loading state on mobile
            if (window.innerWidth <= 768) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="nc-icon nc-spinner nc-spin"></i> Processando...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 3 seconds as fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 3000);
                }
            }
        });
    });
    
    // Add mobile-specific table handling
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (window.innerWidth <= 768) {
            table.classList.add('table-responsive-mobile');
        }
    });
});

// Utility functions for mobile
window.MobileUtils = {
    showToast: function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="nc-icon nc-bell-55"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    },
    
    confirmAction: function(message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    }
};
</script>

<!-- CSS adicional para mobile -->
<style>
/* Mobile-specific styles */
@media (max-width: 768px) {
    .main-panel {
        margin-top: 64px !important;
        padding-top: 20px !important;
    }
    
    .content {
        padding: 15px !important;
    }
    
    .card {
        margin-bottom: 15px !important;
    }
    
    .table-responsive-mobile {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-responsive-mobile table {
        min-width: 600px;
    }
    
    .btn {
        padding: 10px 15px !important;
        font-size: 14px !important;
    }
    
    .form-control {
        font-size: 16px !important; /* Prevents zoom on iOS */
    }
    
    .modal-dialog {
        margin: 10px !important;
        max-width: calc(100% - 20px) !important;
    }
    
    .navbar-nav .dropdown-menu {
        position: static !important;
        float: none !important;
        width: auto !important;
        margin-top: 0 !important;
        background-color: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }
}

/* Touch-friendly elements */
.touch-friendly {
    min-height: 44px;
    min-width: 44px;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

/* Toast notifications */
.toast {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #333;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 1060;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
}

.toast.show {
    transform: translateX(0);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast-success {
    background: #28a745;
}

.toast-error {
    background: #dc3545;
}

.toast-warning {
    background: #ffc107;
    color: #333;
}

.toast-info {
    background: #17a2b8;
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile menu improvements */
@media (max-width: 768px) {
    .mobile-menu {
        z-index: 1050;
    }
    
    .mobile-menu-overlay {
        z-index: 1040;
    }
    
    .mobile-header {
        z-index: 1050;
    }
}
</style>
