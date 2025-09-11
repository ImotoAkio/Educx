<?php
require 'db.php';

// Página de teste para verificar horários
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Horário - Sistema de Presença</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .time-display {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .test-button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }
        .test-button:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🕐 Teste de Horário - Sistema de Presença</h1>
        
        <div class="time-display">
            <div>Horário Atual do Servidor:</div>
            <div id="current-time"><?= date('d/m/Y H:i:s') ?></div>
        </div>

        <?php
        // Função para verificar se está dentro do horário permitido
        function isHorarioValido($hora) {
            $horaAtual = new DateTime($hora);
            $limite = new DateTime('07:35:00');
            return $horaAtual <= $limite;
        }

        // Função para verificar se é dia útil
        function isDiaUtil($data) {
            $diaSemana = date('N', strtotime($data));
            return $diaSemana >= 1 && $diaSemana <= 5;
        }

        $dataHoje = date('Y-m-d');
        $horaAtual = date('H:i:s');
        $diaSemana = date('N'); // 1 = Segunda, 7 = Domingo
        $nomeDiaSemana = ['', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'][$diaSemana];
        
        // Testar diferentes horários
        $horariosTeste = [
            '06:00:00' => '06:00',
            '06:30:00' => '06:30',
            '07:00:00' => '07:00',
            '07:25:00' => '07:25',
            '07:30:00' => '07:30',
            '07:31:00' => '07:31',
            '08:00:00' => '08:00',
            '12:00:00' => '12:00'
        ];
        ?>

        <div class="info-box">
            <h3>📅 Informações do Dia Atual</h3>
            <p><strong>Data:</strong> <?= date('d/m/Y') ?></p>
            <p><strong>Dia da Semana:</strong> <?= $nomeDiaSemana ?> (<?= $diaSemana ?>)</p>
            <p><strong>É Dia Útil:</strong> <?= isDiaUtil($dataHoje) ? '✅ Sim' : '❌ Não' ?></p>
        </div>

        <div class="info-box">
            <h3>🕐 Horário Atual</h3>
            <p><strong>Horário do Servidor:</strong> <?= $horaAtual ?></p>
            <p><strong>Horário Limite:</strong> 07:30:00</p>
            <p><strong>Dentro do Horário:</strong> 
                <?php if (isHorarioValido($horaAtual)): ?>
                    <span class="success">✅ Sim</span>
                <?php else: ?>
                    <span class="error">❌ Não</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="info-box">
            <h3>🎯 Regras do Sistema de XP</h3>
            <p><strong>XP por Presença:</strong> 1 XP (apenas se no horário até 7:30)</p>
            <p><strong>Bônus de Streak:</strong> +5 XP (apenas se completar 5 dias consecutivos NO HORÁRIO)</p>
            <p><strong>Dias Úteis:</strong> Segunda a Sexta (fins de semana não contam)</p>
            <p><strong>Modal:</strong> Aparece apenas na primeira presença do dia</p>
        </div>

        <div class="info-box">
            <h3>🎊 Regras do Bônus de Streak</h3>
            <p><strong>Para ganhar +5 XP de bônus:</strong></p>
            <ul>
                <li>✅ Completar 5 dias consecutivos de presença</li>
                <li>✅ TODOS os 5 dias devem ser no horário (até 7:30)</li>
                <li>✅ O dia atual (5º dia) também deve ser no horário</li>
            </ul>
            <p><strong>Exemplos:</strong></p>
            <ul>
                <li>🎉 5 dias no horário = 6 XP total (1 + 5 bônus)</li>
                <li>😔 5 dias mas algum atrasado = 1 XP (sem bônus)</li>
                <li>😔 5 dias no horário mas hoje atrasado = 0 XP (sem bônus)</li>
            </ul>
        </div>

        <h3>🧪 Teste de Diferentes Horários</h3>
        <table>
            <thead>
                <tr>
                    <th>Horário</th>
                    <th>Válido?</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horariosTeste as $hora => $label): ?>
                <tr>
                    <td><?= $label ?></td>
                    <td><?= isHorarioValido($hora) ? '✅ Sim' : '❌ Não' ?></td>
                    <td>
                        <?php if (isHorarioValido($hora)): ?>
                            <span class="success">Permitido</span>
                        <?php else: ?>
                            <span class="error">Bloqueado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="info-box">
            <h3>🔧 Informações Técnicas</h3>
            <p><strong>Timezone do PHP:</strong> <?= date_default_timezone_get() ?></p>
            <p><strong>Timestamp Atual:</strong> <?= time() ?></p>
            <p><strong>Data ISO:</strong> <?= date('c') ?></p>
            <p><strong>Data UTC:</strong> <?= gmdate('Y-m-d H:i:s') ?></p>
        </div>

        <div class="info-box">
            <h3>📊 Status Geral para Presença</h3>
            <?php
            $podeRegistrarPresenca = false;
            $motivoBloqueio = '';
            
            if (!isDiaUtil($dataHoje)) {
                $motivoBloqueio = 'Fim de semana não conta para presença';
            } else            if (!isHorarioValido($horaAtual)) {
                $motivoBloqueio = 'Horário limite ultrapassado (após 7:30)';
            } else {
                $podeRegistrarPresenca = true;
                $motivoBloqueio = 'Todas as condições atendidas';
            }
            ?>
            
            <p><strong>Pode Registrar Presença:</strong> 
                <?php if ($podeRegistrarPresenca): ?>
                    <span class="success">✅ Sim</span>
                <?php else: ?>
                    <span class="error">❌ Não</span>
                <?php endif; ?>
            </p>
            <p><strong>Motivo:</strong> <?= $motivoBloqueio ?></p>
        </div>

        <div class="info-box">
            <h3>🔄 Teste de Função isHorarioValido()</h3>
            <p><strong>Código da função:</strong></p>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
function isHorarioValido($hora) {
    $horaAtual = new DateTime($hora);
    $limite = new DateTime('07:30:00');
    return $horaAtual <= $limite;
}
            </pre>
            
            <p><strong>Teste com horário atual:</strong></p>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
$horaAtual = new DateTime('<?= $horaAtual ?>');
$limite = new DateTime('07:30:00');
$resultado = $horaAtual <= $limite; // <?= isHorarioValido($horaAtual) ? 'true' : 'false' ?>
            </pre>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button class="test-button" onclick="location.reload()">🔄 Atualizar Página</button>
            <button class="test-button" onclick="window.open('aluno_app.php?id=1', '_blank')">🧪 Testar aluno_app.php</button>
        </div>

        <div class="info-box">
            <h3>💡 Possíveis Problemas</h3>
            <ul>
                <li><strong>Timezone:</strong> Servidor pode estar em timezone diferente</li>
                <li><strong>Horário de Verão:</strong> Pode afetar o cálculo</li>
                <li><strong>Configuração PHP:</strong> date_default_timezone_set() pode estar incorreto</li>
                <li><strong>Banco de Dados:</strong> Pode ter timezone diferente do PHP</li>
            </ul>
        </div>
    </div>

    <script>
        // Atualizar horário a cada segundo
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        // Atualizar a cada segundo
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
