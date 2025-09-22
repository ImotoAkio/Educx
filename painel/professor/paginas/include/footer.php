        </div>
      </div>
      
      <!-- Footer -->
      <footer class="footer">
        <div class="container-fluid">
          <nav>
            <ul class="footer-menu">
              <li>
                <a href="dashboard.php">
                  <i class="fa fa-home"></i>
                  Dashboard
                </a>
              </li>
              <li>
                <a href="missoes.php">
                  <i class="fa fa-tasks"></i>
                  Missões
                </a>
              </li>
              <li>
                <a href="criar_quiz.php">
                  <i class="fa fa-plus"></i>
                  Quiz
                </a>
              </li>
            </ul>
            <p class="copyright text-center">
              © <script>document.write(new Date().getFullYear())</script> 
              <a href="#">Educx</a>, sistema de gamificação educacional
            </p>
          </nav>
        </div>
      </footer>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>
  <script src="../assets/js/paper-dashboard.js"></script>
  
  <!-- Mobile Menu Script -->
  <script src="../assets/js/mobile-menu.js"></script>
  
  
  <!-- Scripts customizados -->
  <script>
    // Inicializar componentes quando o DOM estiver carregado
    document.addEventListener('DOMContentLoaded', function() {
      // Inicializar tooltips
      $('[data-toggle="tooltip"]').tooltip();
      
      // Inicializar popovers
      $('[data-toggle="popover"]').popover();
      
      // Inicializar scrollbar perfeito
      if ($('.sidebar .sidebar-wrapper').length) {
        $('.sidebar .sidebar-wrapper').perfectScrollbar();
      }
      
      // Inicializar notificações
      if (typeof showNotification === 'function') {
        showNotification();
      }
      
      // Configurar auto-hide para alertas
      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
    });
    
    // Função para mostrar notificações
    function showNotification() {
      // Implementar lógica de notificações aqui
      console.log('Sistema de notificações carregado');
    }
    
    // Função para atualizar contador de notificações
    function updateNotificationCount() {
      if (typeof fetch !== 'undefined') {
        fetch('../ajax/contar_notificacoes.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const badges = document.querySelectorAll('.mobile-notification-badge');
            badges.forEach(badge => {
              if (data.total > 0) {
                badge.textContent = data.total;
                badge.style.display = 'flex';
              } else {
                badge.style.display = 'none';
              }
            });
          }
        })
        .catch(error => {
          console.error('Erro ao atualizar contador:', error);
        });
      }
    }
    
    // Atualizar contador a cada 30 segundos
    setInterval(updateNotificationCount, 30000);
    
    // Função para fechar menu mobile ao redimensionar
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        if (window.mobileMenu) {
          window.mobileMenu.closeMenuExternal();
        }
      }
    });
  </script>
</body>
</html>
