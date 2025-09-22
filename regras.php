<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Regras do Jogo - Penalidades</title>
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="asset/style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            background: #17171f;
            color: #fff;
            font-family: 'Orbitron', Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        
        .container {
            max-width: 1000px;
            margin: 10px auto;
            background: #23233a;
            border-radius: 15px;
            box-shadow: 0 4px 24px #0008;
            padding: 20px 15px;
            overflow-x: hidden;
        }
        
        h1 {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.2em;
            margin-bottom: 15px;
            text-align: center;
            color: #ff6b6b;
            line-height: 1.3;
        }
        
        .subtitle {
            text-align: center;
            font-size: 0.9em;
            margin-bottom: 20px;
            color: #ffd93d;
            padding: 0 10px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            background: #1a1a2e;
            border-radius: 8px;
            padding: 3px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .tab {
            flex: 1;
            min-width: 80px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: bold;
            font-size: 0.8em;
            white-space: nowrap;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            user-select: none;
        }
        
        .tab.active {
            background: #ff6b6b;
            color: #fff;
        }
        
        .tab:not(.active) {
            color: #888;
        }
        
        .tab:not(.active):hover {
            background: #2a2a3e;
            color: #fff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .penalties-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #1a1a2e;
            border-radius: 8px;
            overflow: hidden;
            font-size: 0.85em;
        }
        
        .penalties-table th {
            background: #ff6b6b;
            color: #fff;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 0.8em;
        }
        
        .penalties-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #2a2a3e;
            vertical-align: top;
        }
        
        .penalties-table tr:hover {
            background: #2a2a3e;
        }
        
        .penalty-amount {
            font-weight: bold;
            color: #ff6b6b;
            font-size: 0.9em;
            white-space: nowrap;
        }
        
        .penalty-category {
            font-weight: bold;
            color: #ffd93d;
            font-size: 0.85em;
            line-height: 1.3;
        }
        
        .penalty-description {
            color: #ccc;
            font-size: 0.8em;
            line-height: 1.3;
        }
        
        .info-box {
            background: #1a1a2e;
            border-left: 4px solid #ffd93d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
            font-size: 0.85em;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #ffd93d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .info-box p {
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .back-btn {
            display: block;
            margin: 25px auto 0 auto;
            width: 100%;
            text-align: center;
            padding: 12px;
            font-size: 0.9em;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .severity-low { color: #ffd93d; }
        .severity-medium { color: #ff8c42; }
        .severity-high { color: #ff6b6b; }
        .severity-critical { color: #ff4757; }
        
        /* Responsividade para tablets */
        @media (min-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 30px 25px;
                border-radius: 20px;
            }
            
            h1 {
                font-size: 1.5em;
                margin-bottom: 20px;
            }
            
            .subtitle {
                font-size: 1em;
                margin-bottom: 25px;
            }
            
            .tabs {
                margin-bottom: 25px;
                padding: 4px;
            }
            
            .tab {
                padding: 12px 16px;
                font-size: 0.9em;
            }
            
            .penalties-table {
                font-size: 0.9em;
                margin-bottom: 25px;
            }
            
            .penalties-table th {
                padding: 16px 12px;
                font-size: 0.85em;
            }
            
            .penalties-table td {
                padding: 16px 12px;
            }
            
            .penalty-amount {
                font-size: 1em;
            }
            
            .penalty-category {
                font-size: 0.9em;
            }
            
            .penalty-description {
                font-size: 0.85em;
            }
            
            .info-box {
                padding: 20px;
                margin-bottom: 25px;
                font-size: 0.9em;
            }
            
            .info-box h3 {
                font-size: 1em;
            }
        }
        
        /* Responsividade para desktop */
        @media (min-width: 1024px) {
            .container {
                margin: 30px auto;
                padding: 35px 30px;
            }
            
            h1 {
                font-size: 1.8em;
                margin-bottom: 25px;
            }
            
            .subtitle {
                font-size: 1.1em;
                margin-bottom: 30px;
            }
            
            .tabs {
                margin-bottom: 30px;
            }
            
            .tab {
                padding: 14px 20px;
                font-size: 1em;
            }
            
            .penalties-table {
                font-size: 1em;
                margin-bottom: 30px;
            }
            
            .penalties-table th {
                padding: 18px 16px;
                font-size: 0.9em;
            }
            
            .penalties-table td {
                padding: 18px 16px;
            }
            
            .penalty-amount {
                font-size: 1.1em;
            }
            
            .penalty-category {
                font-size: 1em;
            }
            
            .penalty-description {
                font-size: 0.9em;
            }
            
            .info-box {
                padding: 25px;
                margin-bottom: 30px;
                font-size: 1em;
            }
            
            .info-box h3 {
                font-size: 1.1em;
            }
        }
        
        /* Melhorias para telas muito pequenas */
        @media (max-width: 480px) {
            .container {
                margin: 5px;
                padding: 15px 10px;
                border-radius: 10px;
            }
            
            h1 {
                font-size: 1em;
                margin-bottom: 12px;
            }
            
            .subtitle {
                font-size: 0.8em;
                margin-bottom: 15px;
            }
            
            .tabs {
                margin-bottom: 15px;
                padding: 2px;
            }
            
            .tab {
                padding: 8px 6px;
                font-size: 0.75em;
                min-width: 70px;
            }
            
            .penalties-table {
                font-size: 0.8em;
                margin-bottom: 15px;
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .penalties-table thead {
                display: none;
            }
            
            .penalties-table tbody {
                display: block;
            }
            
            .penalties-table tr {
                display: block;
                background: #1a1a2e;
                margin-bottom: 10px;
                border-radius: 8px;
                padding: 12px;
                border: 1px solid #2a2a3e;
            }
            
            .penalties-table td {
                display: block;
                padding: 5px 0;
                border: none;
                text-align: left;
            }
            
            .penalties-table td:first-child {
                font-weight: bold;
                color: #ffd93d;
                font-size: 0.9em;
                margin-bottom: 5px;
            }
            
            .penalties-table td:nth-child(2) {
                font-weight: bold;
                color: #ff6b6b;
                font-size: 1em;
                margin-bottom: 5px;
            }
            
            .penalties-table td:last-child {
                color: #ccc;
                font-size: 0.8em;
                line-height: 1.3;
            }
            
            .penalty-amount {
                font-size: 1em;
            }
            
            .penalty-category {
                font-size: 0.9em;
            }
            
            .penalty-description {
                font-size: 0.8em;
            }
            
            .info-box {
                padding: 12px;
                margin-bottom: 15px;
                font-size: 0.8em;
            }
            
            .info-box h3 {
                font-size: 0.85em;
                margin-bottom: 8px;
            }
            
            .back-btn {
                margin: 20px auto 0 auto;
                padding: 10px;
                font-size: 0.85em;
            }
        }
        
        /* Layout de cartão para telas pequenas */
        @media (max-width: 600px) {
            .penalties-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .penalties-table thead {
                display: none;
            }
            
            .penalties-table tbody {
                display: block;
            }
            
            .penalties-table tr {
                display: block;
                background: #1a1a2e;
                margin-bottom: 12px;
                border-radius: 10px;
                padding: 15px;
                border: 1px solid #2a2a3e;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            
            .penalties-table td {
                display: block;
                padding: 6px 0;
                border: none;
                text-align: left;
            }
            
            .penalties-table td:first-child {
                font-weight: bold;
                color: #ffd93d;
                font-size: 0.9em;
                margin-bottom: 6px;
            }
            
            .penalties-table td:nth-child(2) {
                font-weight: bold;
                color: #ff6b6b;
                font-size: 1.1em;
                margin-bottom: 6px;
            }
            
            .penalties-table td:last-child {
                color: #ccc;
                font-size: 0.85em;
                line-height: 1.4;
            }
        }
        
        /* Scroll suave para abas */
        .tabs::-webkit-scrollbar {
            height: 4px;
        }
        
        .tabs::-webkit-scrollbar-track {
            background: #1a1a2e;
        }
        
        .tabs::-webkit-scrollbar-thumb {
            background: #ff6b6b;
            border-radius: 2px;
        }
        
        /* Melhorias de performance para mobile */
        .penalties-table tr {
            will-change: transform;
        }
        
        .tab {
            will-change: background-color, color;
        }
        
        /* Prevenção de zoom em inputs */
        input, select, textarea {
            font-size: 16px;
        }
        
        /* Melhorias de acessibilidade */
        .tab:focus {
            outline: 2px solid #ffd93d;
            outline-offset: 2px;
        }
        
        .back-btn:focus {
            outline: 2px solid #ffd93d;
            outline-offset: 2px;
        }
        
        /* Otimização para dispositivos com tela pequena */
        @media (max-width: 360px) {
            .container {
                margin: 2px;
                padding: 12px 8px;
            }
            
            h1 {
                font-size: 0.9em;
            }
            
            .subtitle {
                font-size: 0.75em;
            }
            
            .tab {
                padding: 6px 4px;
                font-size: 0.7em;
                min-width: 60px;
            }
            
            .penalties-table tr {
                padding: 10px;
            }
            
            .penalty-category {
                font-size: 0.85em;
            }
            
            .penalty-amount {
                font-size: 0.9em;
            }
            
            .penalty-description {
                font-size: 0.75em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 Penalidades - Sistema de Moedas</h1>
        <p class="subtitle">Conheça as ações que podem fazer você perder moedas Educx</p>

        <div class="info-box">
            <h3>ℹ️ Como Funciona o Sistema de Penalidades</h3>
            <p>As moedas Educx são removidas quando você comete infrações às regras escolares. Lembre-se: o objetivo é sempre o aprendizado e a melhoria do convívio escolar. As penalidades são proporcionais à gravidade da infração e consideram que a média de moedas ganhas por dia é de 10-20.</p>
        </div>

        <div class="tabs">
            <div class="tab active" onclick="showTab('leves')">Leves</div>
            <div class="tab" onclick="showTab('moderadas')">Moderadas</div>
            <div class="tab" onclick="showTab('graves')">Graves</div>
            <div class="tab" onclick="showTab('criticas')">Críticas</div>
        </div>

        <!-- TAB LEVES -->
        <div id="leves" class="tab-content active">
            <table class="penalties-table">
                <thead>
                    <tr>
                        <th>Comportamento</th>
                        <th>Penalidade</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="penalty-category">Atraso leve (até 5 min)</td>
                        <td class="penalty-amount severity-low">-2 moedas</td>
                        <td class="penalty-description">Chegar atrasado sem justificativa válida</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Material esquecido</td>
                        <td class="penalty-amount severity-low">-1 moeda</td>
                        <td class="penalty-description">Esquecer material necessário para a aula</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Conversa paralela</td>
                        <td class="penalty-amount severity-low">-2 moedas</td>
                        <td class="penalty-description">Conversar durante explicações do professor</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Fardamento inadequado</td>
                        <td class="penalty-amount severity-low">-1 moeda</td>
                        <td class="penalty-description">Não usar o uniforme completo ou adequadamente</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Desorganização do espaço</td>
                        <td class="penalty-amount severity-low">-1 moeda</td>
                        <td class="penalty-description">Deixar carteira ou espaço pessoal desorganizado</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Uso inadequado do celular</td>
                        <td class="penalty-amount severity-low">-3 moedas</td>
                        <td class="penalty-description">Usar celular sem autorização do professor</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TAB MODERADAS -->
        <div id="moderadas" class="tab-content">
            <table class="penalties-table">
                <thead>
                    <tr>
                        <th>Comportamento</th>
                        <th>Penalidade</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="penalty-category">Atraso recorrente</td>
                        <td class="penalty-amount severity-medium">-5 moedas</td>
                        <td class="penalty-description">Atrasos frequentes sem justificativa</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Não participar das atividades</td>
                        <td class="penalty-amount severity-medium">-4 moedas</td>
                        <td class="penalty-description">Recusar-se a participar de atividades propostas</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Trabalho não entregue</td>
                        <td class="penalty-amount severity-medium">-6 moedas</td>
                        <td class="penalty-description">Não entregar tarefas ou trabalhos no prazo</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Interrupção constante</td>
                        <td class="penalty-amount severity-medium">-4 moedas</td>
                        <td class="penalty-description">Interromper a aula repetidamente</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Linguagem inadequada</td>
                        <td class="penalty-amount severity-medium">-5 moedas</td>
                        <td class="penalty-description">Usar palavras de baixo calão</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Desrespeito a funcionários</td>
                        <td class="penalty-amount severity-medium">-7 moedas</td>
                        <td class="penalty-description">Desrespeitar orientações de funcionários</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TAB GRAVES -->
        <div id="graves" class="tab-content">
            <table class="penalties-table">
                <thead>
                    <tr>
                        <th>Comportamento</th>
                        <th>Penalidade</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="penalty-category">Desonestidade acadêmica</td>
                        <td class="penalty-amount severity-high">-15 moedas</td>
                        <td class="penalty-description">Colar em provas ou plagiar trabalhos</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Danificar patrimônio</td>
                        <td class="penalty-amount severity-high">-12 moedas</td>
                        <td class="penalty-description">Pichar, riscar ou danificar materiais da escola</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Faltas sem justificativa</td>
                        <td class="penalty-amount severity-high">-10 moedas</td>
                        <td class="penalty-description">Faltar aulas sem atestado ou justificativa</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">"Matar aula"</td>
                        <td class="penalty-amount severity-high">-8 moedas</td>
                        <td class="penalty-description">Estar na escola mas não ir para a sala</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Desrespeito a professores</td>
                        <td class="penalty-amount severity-high">-12 moedas</td>
                        <td class="penalty-description">Desrespeitar ordens ou orientações de professores</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Criar conflitos</td>
                        <td class="penalty-amount severity-high">-10 moedas</td>
                        <td class="penalty-description">Provocar brigas ou conflitos com colegas</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TAB CRÍTICAS -->
        <div id="criticas" class="tab-content">
            <table class="penalties-table">
                <thead>
                    <tr>
                        <th>Comportamento</th>
                        <th>Penalidade</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="penalty-category">Bullying</td>
                        <td class="penalty-amount severity-critical">-25 moedas</td>
                        <td class="penalty-description">Praticar qualquer forma de bullying</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Agressão física</td>
                        <td class="penalty-amount severity-critical">-30 moedas</td>
                        <td class="penalty-description">Agressão física contra colegas ou funcionários</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Cyberbullying</td>
                        <td class="penalty-amount severity-critical">-20 moedas</td>
                        <td class="penalty-description">Praticar bullying através de meios digitais</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Danos graves ao patrimônio</td>
                        <td class="penalty-amount severity-critical">-20 moedas</td>
                        <td class="penalty-description">Causar danos significativos à escola</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Ameaças</td>
                        <td class="penalty-amount severity-critical">-25 moedas</td>
                        <td class="penalty-description">Ameaçar colegas, professores ou funcionários</td>
                    </tr>
                    <tr>
                        <td class="penalty-category">Uso de substâncias</td>
                        <td class="penalty-amount severity-critical">-35 moedas</td>
                        <td class="penalty-description">Uso de álcool, drogas ou substâncias ilícitas</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="info-box">
            <h3>💡 Dicas Importantes</h3>
            <ul>
                <li><strong>Arrependimento:</strong> Se você se arrepender e demonstrar mudança de comportamento, o professor pode reduzir ou cancelar a penalidade.</li>
                <li><strong>Reincidência:</strong> Comportamentos repetidos podem ter penalidades maiores.</li>
                <li><strong>Contexto:</strong> O professor sempre considera o contexto e as circunstâncias antes de aplicar uma penalidade.</li>
                <li><strong>Recuperação:</strong> Lembre-se que você sempre pode recuperar moedas através de boas ações e missões!</li>
            </ul>
        </div>

        <?php
        // Tenta obter o id do aluno via GET ou SESSION
        $id_aluno = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : '');
        ?>
        <a href="aluno.php<?php echo $id_aluno ? '?id=' . urlencode($id_aluno) : ''; ?>" class="gradient-button back-btn">Voltar ao Painel</a>
    </div>

    <script>
        function showTab(tabName) {
            // Esconder todas as abas
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remover classe active de todas as abas
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostrar a aba selecionada
            document.getElementById(tabName).classList.add('active');
            
            // Adicionar classe active à aba clicada
            event.target.classList.add('active');
            
            // Scroll suave para o topo da tabela
            setTimeout(() => {
                const activeContent = document.getElementById(tabName);
                if (activeContent) {
                    activeContent.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }
            }, 100);
        }
        
        // Melhorar experiência de toque em dispositivos móveis
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar feedback tátil para abas
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                tab.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
                
                tab.addEventListener('touchcancel', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Otimizar scroll para dispositivos móveis
            const tables = document.querySelectorAll('.penalties-table');
            tables.forEach(table => {
                table.addEventListener('touchstart', function() {
                    this.style.overflowX = 'auto';
                });
            });
        });
    </script>
</body>
</html>
