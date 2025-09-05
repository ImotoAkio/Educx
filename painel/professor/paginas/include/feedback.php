<?php
// Sistema de Feedback para o Painel do Professor

// Função para exibir mensagens de sucesso
function exibirSucesso($mensagem) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagens de erro
function exibirErro($mensagem) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagens de aviso
function exibirAviso($mensagem) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir mensagens de informação
function exibirInfo($mensagem) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fa fa-info-circle"></i> ' . htmlspecialchars($mensagem) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

// Função para exibir loading
function exibirLoading($mensagem = "Carregando...") {
    echo '<div class="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div class="spinner-border text-light" role="status">
                <span class="sr-only">' . htmlspecialchars($mensagem) . '</span>
            </div>
          </div>';
}

// Função para mostrar toast notification
function exibirToast($tipo, $titulo, $mensagem) {
    $icone = '';
    $classe = '';
    
    switch($tipo) {
        case 'success':
            $icone = 'fa-check-circle';
            $classe = 'bg-success';
            break;
        case 'error':
            $icone = 'fa-exclamation-circle';
            $classe = 'bg-danger';
            break;
        case 'warning':
            $icone = 'fa-exclamation-triangle';
            $classe = 'bg-warning';
            break;
        case 'info':
            $icone = 'fa-info-circle';
            $classe = 'bg-info';
            break;
    }
    
    echo '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast ' . $classe . ' text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fa ' . $icone . ' me-2"></i>
                    <strong class="me-auto">' . htmlspecialchars($titulo) . '</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ' . htmlspecialchars($mensagem) . '
                </div>
            </div>
          </div>';
}

// Função para confirmar ações
function confirmarAcao($mensagem, $acaoConfirmada, $acaoCancelada = '') {
    echo '<script>
            if (confirm("' . addslashes($mensagem) . '")) {
                ' . $acaoConfirmada . '
            } else {
                ' . $acaoCancelada . '
            }
          </script>';
}

// Função para redirecionar com mensagem
function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION['feedback'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
    header("Location: $url");
    exit;
}

// Função para exibir mensagens da sessão
function exibirMensagemSessao() {
    if (isset($_SESSION['feedback'])) {
        $tipo = $_SESSION['feedback']['tipo'];
        $mensagem = $_SESSION['feedback']['mensagem'];
        
        switch($tipo) {
            case 'success':
                exibirSucesso($mensagem);
                break;
            case 'error':
                exibirErro($mensagem);
                break;
            case 'warning':
                exibirAviso($mensagem);
                break;
            case 'info':
                exibirInfo($mensagem);
                break;
        }
        
        unset($_SESSION['feedback']);
    }
}
?>
