<?php
// Conexão com o banco de dados e início da sessão
session_start();

require '../../../db.php';

// Incluir sistema de feedback
require 'include/feedback.php';

// Definir página ativa para a sidebar
$pagina_ativa = 'missoes';

// Verifica se o professor está logado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    // Redireciona para a página de login se o professor não estiver logado
    header('Location: ../../../login.php');
    exit;
}

// Buscar todas as solicitações pendentes com dados completos
$stmt = $pdo->query("
    SELECT 
        s.id AS solicitacao_id,
        s.data_solicitacao,
        s.status,
        a.id AS aluno_id,
        a.nome AS aluno_nome,
        a.xp_total AS aluno_xp,
        a.moedas AS aluno_moedas,
        a.nivel AS aluno_nivel,
        a.avatar AS aluno_avatar,
        m.id AS missao_id,
        m.nome AS missao_nome,
        m.descricao AS missao_descricao,
        m.xp AS missao_xp,
        m.moedas AS missao_moedas,
        m.link AS missao_link,
        m.status AS missao_status,
        t.nome AS turma_nome
    FROM solicitacoes_missoes s
    JOIN alunos a ON s.aluno_id = a.id
    JOIN missoes m ON s.missao_id = m.id
    LEFT JOIN turmas t ON a.id IN (SELECT aluno_id FROM alunos_turmas WHERE turma_id = t.id)
    WHERE s.status = 'pendente'
    ORDER BY s.data_solicitacao DESC
");
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>Missões Detalhadas - Professor</title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <!-- Mobile CSS -->
  <link href="../assets/css/mobile-header.css" rel="stylesheet" />
  <style>
    .bg-gradient-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .missao-detail-card {
      border: none;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .missao-detail-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .aluno-info, .missao-info, .status-info {
      border-left: 3px solid #e9ecef;
      padding-left: 15px;
    }
    
    .aluno-info {
      border-left-color: #007bff;
    }
    
    .missao-info {
      border-left-color: #28a745;
    }
    
    .status-info {
      border-left-color: #17a2b8;
    }
    
    .badge {
      font-size: 0.75em;
    }
    
    .font-weight-bold {
      font-weight: 600 !important;
    }
    
    .opacity-75 {
      opacity: 0.75;
    }
    
    .btn-group .btn {
      flex: 1;
    }
    
    @media (max-width: 768px) {
      .col-xl-4 {
        margin-bottom: 20px;
      }
      
      .card-body {
        padding: 15px;
      }
      
      .row .col-4, .row .col-6 {
        margin-bottom: 10px;
      }
    }
  </style>
</head>

<body>
<?php include 'include/navbar.php'; ?>
      <div class="content">
        <!-- Exibir mensagens de feedback -->
        <?php exibirMensagemSessao(); ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">
                  <i class="fa fa-tasks text-success"></i> Aprovar Missões
                  <span class="badge badge-success ml-2"><?= count($solicitacoes); ?></span>
                </h4>
                <div class="card-tools">
                  <input type="text" class="form-control form-control-sm" id="searchMissoes" placeholder="Buscar missões..." style="width: 250px;">
                </div>
              </div>
              <div class="card-body">
                <?php if (empty($solicitacoes)): ?>
                  <div class="text-center py-5">
                    <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhuma missão pendente</h5>
                    <p class="text-muted">Não há solicitações de missões aguardando aprovação no momento.</p>
                  </div>
                <?php else: ?>
                  <div class="row" id="missoesContainer">
                    <?php foreach ($solicitacoes as $index => $solicitacao): ?>
                      <div class="col-lg-6 col-xl-4 mb-4 missao-card-container" 
                           data-aluno="<?= strtolower($solicitacao['aluno_nome']); ?>" 
                           data-missao="<?= strtolower($solicitacao['missao_nome']); ?>">
                        <div class="card missao-detail-card h-100">
                          <!-- Header do Card -->
                          <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                <h6 class="card-title mb-0">
                                  <i class="fa fa-trophy mr-2"></i>
                                  <?= htmlspecialchars($solicitacao['missao_nome']); ?>
                                </h6>
                                <small class="opacity-75">
                                  <i class="fa fa-calendar mr-1"></i>
                                  <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])); ?>
                                </small>
                              </div>
                              <span class="badge badge-warning">
                                <i class="fa fa-clock"></i> Pendente
                              </span>
                            </div>
                          </div>

                          <!-- Corpo do Card -->
                          <div class="card-body">
                            <!-- Informações do Aluno -->
                            <div class="aluno-info mb-3">
                              <h6 class="text-primary mb-2">
                                <i class="fa fa-user mr-1"></i> Informações do Aluno
                              </h6>
                              <div class="row">
                                <div class="col-6">
                                  <small class="text-muted">Nome:</small>
                                  <div class="font-weight-bold"><?= htmlspecialchars($solicitacao['aluno_nome']); ?></div>
                                </div>
                                <div class="col-6">
                                  <small class="text-muted">ID:</small>
                                  <div class="font-weight-bold">#<?= $solicitacao['aluno_id']; ?></div>
                                </div>
                              </div>
                              <div class="row mt-2">
                                <div class="col-4">
                                  <small class="text-muted">XP Atual:</small>
                                  <div class="text-success font-weight-bold">
                                    <i class="fa fa-star"></i> <?= number_format($solicitacao['aluno_xp']); ?>
                                  </div>
                                </div>
                                <div class="col-4">
                                  <small class="text-muted">Moedas:</small>
                                  <div class="text-warning font-weight-bold">
                                    <i class="fa fa-coins"></i> <?= number_format($solicitacao['aluno_moedas']); ?>
                                  </div>
                                </div>
                                <div class="col-4">
                                  <small class="text-muted">Nível:</small>
                                  <div class="text-info font-weight-bold">
                                    <i class="fa fa-level-up"></i> <?= $solicitacao['aluno_nivel'] ?? 'N/A'; ?>
                                  </div>
                                </div>
                              </div>
                              <?php if (!empty($solicitacao['turma_nome'])): ?>
                                <div class="mt-2">
                                  <small class="text-muted">Turma:</small>
                                  <span class="badge badge-secondary"><?= htmlspecialchars($solicitacao['turma_nome']); ?></span>
                                </div>
                              <?php endif; ?>
                            </div>

                            <!-- Informações da Missão -->
                            <div class="missao-info mb-3">
                              <h6 class="text-success mb-2">
                                <i class="fa fa-tasks mr-1"></i> Detalhes da Missão
                              </h6>
                              <p class="text-muted small mb-2">
                                <?= htmlspecialchars($solicitacao['missao_descricao']); ?>
                              </p>
                              <div class="row">
                                <div class="col-6">
                                  <small class="text-muted">Recompensa XP:</small>
                                  <div class="text-success font-weight-bold">
                                    <i class="fa fa-star"></i> +<?= number_format($solicitacao['missao_xp']); ?>
                                  </div>
                                </div>
                                <div class="col-6">
                                  <small class="text-muted">Recompensa Moedas:</small>
                                  <div class="text-warning font-weight-bold">
                                    <i class="fa fa-coins"></i> +<?= number_format($solicitacao['missao_moedas']); ?>
                                  </div>
                                </div>
                              </div>
                              <?php if (!empty($solicitacao['missao_link'])): ?>
                                <div class="mt-2">
                                  <a href="<?= htmlspecialchars($solicitacao['missao_link']); ?>" 
                                     target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fa fa-external-link"></i> Ver Link da Missão
                                  </a>
                                </div>
                              <?php endif; ?>
                            </div>

                            <!-- Status e Timestamp -->
                            <div class="status-info mb-3">
                              <h6 class="text-info mb-2">
                                <i class="fa fa-info-circle mr-1"></i> Status da Solicitação
                              </h6>
                              <div class="row">
                                <div class="col-6">
                                  <small class="text-muted">Solicitado em:</small>
                                  <div class="font-weight-bold">
                                    <?= date('d/m/Y', strtotime($solicitacao['data_solicitacao'])); ?>
                                  </div>
                                </div>
                                <div class="col-6">
                                  <small class="text-muted">Hora:</small>
                                  <div class="font-weight-bold">
                                    <?= date('H:i:s', strtotime($solicitacao['data_solicitacao'])); ?>
                                  </div>
                                </div>
                              </div>
                              <div class="mt-2">
                                <small class="text-muted">Tempo decorrido:</small>
                                <div class="font-weight-bold text-primary">
                                  <?php
                                  $agora = new DateTime();
                                  $solicitacao_data = new DateTime($solicitacao['data_solicitacao']);
                                  $diferenca = $agora->diff($solicitacao_data);
                                  
                                  if ($diferenca->days > 0) {
                                    echo $diferenca->days . ' dia(s) atrás';
                                  } elseif ($diferenca->h > 0) {
                                    echo $diferenca->h . ' hora(s) atrás';
                                  } elseif ($diferenca->i > 0) {
                                    echo $diferenca->i . ' minuto(s) atrás';
                                  } else {
                                    echo 'Agora mesmo';
                                  }
                                  ?>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Footer do Card com Ações -->
                          <div class="card-footer bg-light">
                            <form method="POST" action="processar_aprovacao.php">
                              <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['solicitacao_id']; ?>">
                              <div class="btn-group w-100" role="group">
                                <button class="btn btn-success btn-sm" type="submit" name="acao" value="aprovar" 
                                        data-toggle="tooltip" title="Aprovar missão">
                                  <i class="fa fa-check mr-1"></i> Aprovar
                                </button>
                                <button class="btn btn-danger btn-sm" type="submit" name="acao" value="rejeitar" 
                                        data-toggle="tooltip" title="Rejeitar missão"
                                        onclick="return confirm('Tem certeza que deseja rejeitar esta missão?')">
                                  <i class="fa fa-times mr-1"></i> Rejeitar
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

  <!-- Footer com scripts mobile -->
  <?php include 'include/footer.php'; ?>

  <!-- Scripts -->
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>
  <script src="../assets/js/paper-dashboard.js?v=2.0.1" type="text/javascript"></script>

  <script>
    // Busca em tempo real
    document.getElementById('searchMissoes').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const cards = document.querySelectorAll('.missao-card-container');
      
      cards.forEach(card => {
        const aluno = card.getAttribute('data-aluno');
        const missao = card.getAttribute('data-missao');
        
        if (aluno.includes(searchTerm) || missao.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>
