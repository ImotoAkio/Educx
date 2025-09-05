<?php
/**
 * Sistema de Feedback para Painel da Secretaria
 * Funções para exibir mensagens de sucesso, erro, aviso e informação
 */

// Função para exibir mensagem de sucesso
function exibirSucesso($mensagem) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagem de erro
function exibirErro($mensagem) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagem de aviso
function exibirAviso($mensagem) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagem de informação
function exibirInfo($mensagem) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fa fa-info-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir loading
function exibirLoading($mensagem = 'Carregando...') {
    echo '<div class="alert alert-secondary" role="alert">
            <i class="fa fa-spinner fa-spin"></i> ' . htmlspecialchars($mensagem) . '
          </div>';
}

// Função para exibir toast notification
function exibirToast($tipo, $titulo, $mensagem) {
    $icon = '';
    $bgClass = '';
    
    switch($tipo) {
        case 'success':
            $icon = 'fa-check-circle';
            $bgClass = 'bg-success';
            break;
        case 'error':
            $icon = 'fa-exclamation-circle';
            $bgClass = 'bg-danger';
            break;
        case 'warning':
            $icon = 'fa-exclamation-triangle';
            $bgClass = 'bg-warning';
            break;
        case 'info':
            $icon = 'fa-info-circle';
            $bgClass = 'bg-info';
            break;
    }
    
    echo '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast ' . $bgClass . ' text-white" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="toast-header">
                <i class="fa ' . $icon . ' me-2"></i>
                <strong class="me-auto">' . htmlspecialchars($titulo) . '</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
              <div class="toast-body">
                ' . htmlspecialchars($mensagem) . '
              </div>
            </div>
          </div>';
}

// Função para confirmar ação
function confirmarAcao($mensagem, $urlConfirmacao, $urlCancelamento = 'javascript:history.back()') {
    echo '<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Confirmar Ação</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>' . htmlspecialchars($mensagem) . '</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <a href="' . $urlConfirmacao . '" class="btn btn-danger">Confirmar</a>
                </div>
              </div>
            </div>
          </div>';
}

// Função para redirecionar com mensagem
function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $mensagem
    ];
    header("Location: $url");
    exit;
}

// Função para exibir mensagem da sessão
function exibirMensagemSessao() {
    if (isset($_SESSION['mensagem'])) {
        $tipo = $_SESSION['mensagem']['tipo'];
        $texto = $_SESSION['mensagem']['texto'];
        
        switch($tipo) {
            case 'success':
                exibirSucesso($texto);
                break;
            case 'error':
                exibirErro($texto);
                break;
            case 'warning':
                exibirAviso($texto);
                break;
            case 'info':
                exibirInfo($texto);
                break;
        }
        
        unset($_SESSION['mensagem']);
    }
}
?>
