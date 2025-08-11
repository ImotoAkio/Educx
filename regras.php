<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regras do Jogo</title>
    <link rel="stylesheet" href="asset/button.css">
    <link rel="stylesheet" href="asset/style.css">
    <style>
        body {
            background: #17171f;
            color: #fff;
            font-family: 'Orbitron', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #23233a;
            border-radius: 20px;
            box-shadow: 0 4px 24px #0008;
            padding: 32px 24px 24px 24px;
        }
        h1 {
            font-family: 'Press Start 2P', cursive;
            font-size: 2em;
            margin-bottom: 24px;
            text-align: center;
        }
        .accordion {
            margin-bottom: 16px;
        }
        .accordion-item {
            background: #222;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px #0004;
        }
        .accordion-title {
            padding: 16px;
            cursor: pointer;
            font-weight: bold;
            background: #29294d;
            transition: background 0.2s;
        }
        .accordion-title:hover {
            background: #34346a;
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            background: #23233a;
            transition: max-height 0.3s ease;
            padding: 0 16px;
        }
        .accordion-item.active .accordion-content {
            max-height: 300px;
            padding: 16px;
        }
        .back-btn {
            display: block;
            margin: 32px auto 0 auto;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manual Completo do Aluno: A Jornada Educx</h1>
        <p style="text-align:center; font-size:1.1em; margin-bottom:24px;">Transformando atitudes em conquistas!</p>

        <div class="accordion">
            <div class="accordion-item" id="cap1">
                <div class="accordion-title">Capítulo 1: A Aventura Vai Começar!</div>
                <div class="accordion-content">
                    <strong>1.1. O que é a Jornada Educx?</strong>
                    <p>Bem-vindo, aventureiro! A "Jornada Educx" é o nome do nosso programa de gamificação. Nós transformamos o ambiente escolar em um grande jogo, onde suas atitudes, seu esforço e sua colaboração se transformam em conquistas visíveis. Aqui, cada dia é uma chance de evoluir, ganhar recompensas e, o mais importante, se tornar uma versão melhor de si mesmo.</p>
                    <strong>1.2. Por que transformamos a escola em um jogo?</strong>
                    <ul>
                        <li>Reconhecer seu esforço: Não apenas nas notas, mas em tudo o que você faz.</li>
                        <li>Tornar o aprendizado divertido: Missões e desafios tornam as tarefas mais empolgantes.</li>
                        <li>Fortalecer a comunidade: Incentivamos o trabalho em equipe e o respeito mútuo.</li>
                        <li>Celebrar o progresso: Cada pequena vitória é um passo na sua jornada.</li>
                    </ul>
                    <strong>1.3. Seus Objetivos Principais</strong>
                    <ul>
                        <li>Acumular XP: Para subir de nível e desbloquear títulos de prestígio.</li>
                        <li>Ganhar Moedas Educx: Para trocar por recompensas incríveis.</li>
                        <li>Colaborar: Para alcançar objetivos maiores junto com seus colegas.</li>
                    </ul>
                </div>
            </div>
            <div class="accordion-item" id="cap2">
                <div class="accordion-title">Capítulo 2: As Ferramentas do Herói</div>
                <div class="accordion-content">
                    <strong>2.1. Seu Crachá: A Chave para o Universo Educx</strong>
                    <p>Seu crachá de estudante é mais importante do que nunca! Ele contém um QR Code mágico. Este código é sua identidade secreta no jogo, a chave que abre seu perfil. Cuide bem dele!</p>
                    <strong>2.2. A Plataforma Web: Seu Painel de Controle</strong>
                    <ol>
                        <li>Abra a câmera do seu celular ou de um tablet da escola.</li>
                        <li>Aponte para o QR Code do seu crachá.</li>
                        <li>Um link aparecerá. Clique nele.</li>
                        <li>Pronto! Você está no seu painel de controle.</li>
                    </ol>
                    <strong>2.3. Entendendo seu Perfil</strong>
                    <ul>
                        <li><b>XP (Pontos de Experiência):</b> Uma barra de progresso que mostra o quão perto você está do próximo nível. O XP só aumenta, mostrando todo o seu crescimento acumulado.</li>
                        <li><b>Moedas Educx (💰):</b> O seu "dinheiro" no jogo. É com elas que você adquire as Cartas de Poder. Elas podem aumentar ou diminuir.</li>
                        <li><b>Nível:</b> Seu título atual na escola (Iniciante, Explorador, etc.). Mostra a todos o seu prestígio e dedicação.</li>
                    </ul>
                </div>
            </div>
            <div class="accordion-item" id="cap3">
                <div class="accordion-title">Capítulo 3: Como Acumular Conquistas (XP & Moedas)</div>
                <div class="accordion-content">
                    <strong>3.1. Ações do Dia a Dia que Geram XP</strong>
                    <p>Você ganha XP automaticamente ao realizar ações positivas que fortalecem nossa comunidade. Elas são a base da sua evolução. Para uma lista completa, consulte o Anexo I no final deste manual.</p>
                    <strong>3.2. Missões: O Caminho para as Moedas Educx</strong>
                    <p>As Moedas são o prêmio por ir além! Elas são ganhas ao completar Missões. Fique de olho no seu painel na plataforma e nos murais da sala, pois os professores e a coordenação podem lançar novas missões a qualquer momento!</p>
                    <strong>3.3. Tipos de Missões</strong>
                    <ul>
                        <li><b>Missões Individuais:</b> Desafios só para você. Ex: "Crie um resumo criativo sobre a aula de História".</li>
                        <li><b>Missões de Grupo:</b> Desafios para fazer com sua equipe. Ex: "Apresentem o melhor trabalho sobre o ciclo da água".</li>
                        <li><b>Missões Relâmpago:</b> Desafios rápidos que aparecem e duram pouco tempo. Ex: "Os 5 primeiros alunos que resolverem este enigma matemático ganham uma recompensa".</li>
                        <li><b>Missões da Comunidade:</b> Desafios para a turma toda. Ex: "Se a turma inteira mantiver a sala organizada por uma semana, todos ganham uma recompensa".</li>
                    </ul>
                    <strong>3.4. O Poder do Professor: Bônus e Penalidades</strong>
                    <ul>
                        <li>Conceder Moedas Bônus: Por uma atitude excepcional que não estava prevista em nenhuma missão.</li>
                        <li>Remover Moedas: Em casos de infração às regras, como forma de aprendizado.</li>
                    </ul>
                </div>
            </div>
            <div class="accordion-item" id="cap4">
                <div class="accordion-title">Capítulo 4: A Lojinha de Recompensas</div>
                <div class="accordion-content">
                    <strong>4.1. O que são as Cartas de Poder?</strong>
                    <p>São recompensas simbólicas que te dão pequenas vantagens e privilégios na escola, tornando sua experiência mais divertida. Exemplos: ser o DJ do intervalo, ter mais tempo de recreio, etc.</p>
                    <strong>4.2. Como Funciona a Lojinha na Prática</strong>
                    <ol>
                        <li>Consulte os Itens: Acesse seu perfil na plataforma web e clique na seção "Lojinha".</li>
                        <li>Verifique os Preços: Os custos em Moedas de cada Carta de Poder são dinâmicos. O preço que você vê hoje pode não ser o mesmo de amanhã!</li>
                        <li>Faça a Troca: Se tiver moedas suficientes, basta clicar em "Resgatar".</li>
                        <li>Use seu Poder: A plataforma informará seu professor sobre o resgate. Combine com ele o melhor momento para usar sua recompensa.</li>
                    </ol>
                    <strong>4.3. Estratégias: Guardar ou Gastar suas Moedas?</strong>
                    <p>A decisão é sua! Você pode gastar suas moedas em recompensas menores assim que puder, ou pode economizar para aquela Carta de Poder mais rara e valiosa que pode aparecer futuramente.</p>
                </div>
            </div>
            <div class="accordion-item" id="cap5">
                <div class="accordion-title">Capítulo 5: Um Dia na Jornada Educx (Exemplo Prático)</div>
                <div class="accordion-content">
                    <p><b>8:00:</b> Ana chega na escola no horário e ganha +10 XP. Ela escaneia seu crachá e vê seu perfil.</p>
                    <p><b>10:15:</b> Durante a aula de Ciências, a professora lança uma Missão de Grupo: "O grupo que construir o modelo de célula mais criativo ganha 80 Moedas cada!" Ana e sua equipe se esforçam e vencem! Ela ganha +80 Moedas.</p>
                    <p><b>11:00:</b> No seu painel, Ana vê que agora tem moedas suficientes para resgatar a "Carta de +10 min de intervalo". Ela resgata.</p>
                    <p><b>12:00:</b> Antes do intervalo, ela avisa ao professor, que autoriza o uso da carta. Ana e um amigo aproveitam o tempo extra.</p>
                    <p><b>14:00:</b> Durante a aula, Ana ajuda um colega que estava com dificuldade em matemática. O professor percebe a atitude proativa e concede a ela um bônus de +10 XP e +5 Moedas.</p>
                    <p><b>Resultado do dia da Ana:</b> +20 XP e +85 Moedas. Ela se divertiu, colaborou e foi reconhecida.</p>
                </div>
            </div>
            <div class="accordion-item" id="cap6">
                <div class="accordion-title">Capítulo 6: As Regras de Ouro da Convivência</div>
                <div class="accordion-content">
                    <ul>
                        <li>O professor é seu guia: Ele tem a palavra final sobre a pontuação para garantir que o jogo seja justo e educativo.</li>
                        <li>Trabalho em equipe vale ouro: Em missões de grupo, o sucesso de um é o sucesso de todos! Cada integrante da equipe recebe a pontuação total.</li>
                        <li>Respeito acima de tudo: A gamificação segue todas as regras de convivência da escola.</li>
                        <li>Evoluir, não competir: O objetivo é a sua evolução pessoal e a colaboração com a turma.</li>
                    </ul>
                </div>
            </div>
            <div class="accordion-item" id="cap7">
                <div class="accordion-title">Capítulo 7: Perguntas Frequentes (FAQ)</div>
                <div class="accordion-content">
                    <b>P: O que acontece se eu perder meu crachá?</b>
                    <p>R: Avise a secretaria imediatamente para que possam providenciar um novo e vincular ao seu perfil.</p>
                    <b>P: Um professor pode tirar meus XP?</b>
                    <p>R: Não. O XP representa seu progresso e conquistas passadas, ele só acumula. Apenas as Moedas, que são uma moeda de troca, podem ser removidas em casos de infração.</p>
                    <b>P: E se eu faltar no dia de uma missão?</b>
                    <p>R: Você não será penalizado, mas perderá a oportunidade de ganhar as moedas daquela missão específica. Fique atento às novas missões que surgem todos os dias!</p>
                    <b>P: Posso dar minhas moedas para um amigo?</b>
                    <p>R: Não. Suas moedas são fruto do seu esforço pessoal e são intransferíveis. Mas você pode usar suas recompensas, como "tempo extra de intervalo", para beneficiar um amigo.</p>
                </div>
            </div>
            <div class="accordion-item" id="anexo">
                <div class="accordion-title">Anexo I: Catálogo de Ações da Jornada Educx</div>
                <div class="accordion-content">
                    <b>✅ Ações que Impulsionam sua Jornada (Atitudes Positivas)</b>
                    <ul>
                        <li><b>Responsabilidade e Compromisso com os Estudos</b>
                            <ul>
                                <li>Ser pontual, chegando às aulas no horário.</li>
                                <li>Usar o fardamento escolar completo e de forma adequada.</li>
                                <li>Entregar todas as tarefas e trabalhos dentro do prazo.</li>
                                <li>Trazer todos os materiais necessários para a aula.</li>
                                <li>Manter seus cadernos e materiais organizados e em dia.</li>
                                <li>Prestar atenção e se esforçar para participar das explicações.</li>
                            </ul>
                        </li>
                        <li><b>Colaboração, Empatia e Respeito</b>
                            <ul>
                                <li>Ajudar um colega que está com dificuldade em uma matéria ou tarefa.</li>
                                <li>Oferecer ajuda a um professor ou funcionário sem que seja solicitado.</li>
                                <li>Trabalhar de forma construtiva e respeitosa em atividades de grupo.</li>
                                <li>Compartilhar seus materiais com um colega que esqueceu os seus.</li>
                                <li>Ouvir com atenção e respeito a opinião dos colegas, mesmo que discorde.</li>
                                <li>Dar as boas-vindas e integrar alunos novos na turma.</li>
                            </ul>
                        </li>
                        <li><b>Iniciativa e Proatividade</b>
                            <ul>
                                <li>Fazer perguntas inteligentes e pertinentes para aprofundar o entendimento da aula.</li>
                                <li>Trazer voluntariamente pesquisas, notícias ou curiosidades relacionadas ao conteúdo estudado.</li>
                                <li>Oferecer-se para ajudar em tarefas da sala (ex: apagar o quadro, organizar a estante, distribuir materiais).</li>
                                <li>Sugerir ideias para projetos, eventos ou melhorias para a turma e a escola.</li>
                                <li>Buscar conhecimento extra sobre os assuntos de que mais gosta.</li>
                            </ul>
                        </li>
                        <li><b>Cuidado com o Ambiente e a Comunidade Escolar</b>
                            <ul>
                                <li>Manter sua carteira, cadeira e o espaço ao seu redor sempre limpos e organizados.</li>
                                <li>Não jogar lixo no chão da sala ou do pátio.</li>
                                <li>Ajudar a manter os espaços comuns (biblioteca, laboratórios, quadra) limpos e em ordem.</li>
                                <li>Cuidar dos livros, tanto os didáticos quanto os da biblioteca.</li>
                                <li>Informar a um professor ou à coordenação caso veja algum material ou equipamento da escola danificado.</li>
                            </ul>
                        </li>
                        <li><b>Engajamento Digital Positivo</b>
                            <ul>
                                <li>Utilizar o celular ou tablet para pesquisa e atividades pedagógicas, quando autorizado pelo professor.</li>
                                <li>Participar de forma respeitosa e construtiva nos fóruns de discussão da plataforma da escola.</li>
                                <li>Com autorização dos pais, interagir positivamente com as publicações nas redes sociais da escola.</li>
                            </ul>
                        </li>
                    </ul>
                    <b>❌ Ações que Prejudicam sua Jornada (Atitudes a Evitar)</b>
                    <ul>
                        <li><b>Desrespeito e Conflitos</b>
                            <ul>
                                <li>Praticar qualquer forma de bullying (apelidos maldosos, agressão física, exclusão social, cyberbullying).</li>
                                <li>Desrespeitar as ordens e orientações de professores e funcionários.</li>
                                <li>Usar palavras de baixo calão ou linguagem ofensiva.</li>
                                <li>Interromper a aula com conversas paralelas ou brincadeiras fora de hora.</li>
                                <li>Criar ou espalhar fofocas e boatos sobre colegas ou professores.</li>
                            </ul>
                        </li>
                        <li><b>Falta de Compromisso com os Estudos</b>
                            <ul>
                                <li>Deixar de entregar tarefas e trabalhos de forma recorrente e sem justificativa.</li>
                                <li>Conversar ou se distrair durante as explicações, atrapalhando a si mesmo e aos outros.</li>
                                <li>Praticar qualquer tipo de desonestidade acadêmica (colar em provas, plagiar trabalhos da internet ou de colegas).</li>
                                <li>Recusar-se a participar das atividades propostas em sala de aula.</li>
                            </ul>
                        </li>
                        <li><b>Mau Uso de Materiais e do Espaço Físico</b>
                            <ul>
                                <li>Usar o celular, fones de ouvido ou outros dispositivos eletrônicos sem a permissão explícita do professor.</li>
                                <li>Danificar o patrimônio da escola (pichar ou riscar carteiras, paredes, portas).</li>
                                <li>Danificar ou usar de forma inadequada os materiais de colegas.</li>
                                <li>Deixar lixo ou desorganização no seu espaço ao final da aula.</li>
                            </ul>
                        </li>
                        <li><b>Faltas e Atrasos</b>
                            <ul>
                                <li>Chegar atrasado às aulas com frequência e sem justificativa plausível.</li>
                                <li>Faltar às aulas sem apresentar atestado médico ou justificativa dos responsáveis.</li>
                                <li>Estar no ambiente escolar mas não comparecer à sala de aula ("matar aula").</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    // Tenta obter o id do aluno via GET ou SESSION
    $id_aluno = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : '');
    ?>
    <a href="aluno.php<?php echo $id_aluno ? '?id=' . urlencode($id_aluno) : ''; ?>" class="gradient-button back-btn">Voltar</a>
    </div>
    <script>
        // Interatividade do acordeão
        document.querySelectorAll('.accordion-title').forEach(function(title) {
            title.addEventListener('click', function() {
                var item = this.parentElement;
                item.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
