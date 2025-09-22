<?php
session_start();

// Verificar se o usuário está logado como professor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'professor') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalar App Professor - Educx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            padding-top: 50px;
            padding-bottom: 50px;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-body {
            padding: 40px;
        }
        .app-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 5px solid #667eea;
        }
        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .step-content h5 {
            color: #333;
            margin-bottom: 10px;
        }
        .step-content p {
            color: #666;
            margin: 0;
        }
        .btn-download {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-download:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        .btn-back {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
            color: white;
            transform: translateY(-1px);
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box .fa-exclamation-triangle {
            color: #856404;
            margin-right: 10px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list .fa-check {
            color: #28a745;
            margin-right: 15px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="app-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h2 class="mb-0">App Professor Educx</h2>
                        <p class="mb-0">Aplicativo móvel para gerenciamento rápido de alunos</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <a href="App_Professor.apk" class="btn-download" download>
                                <i class="fas fa-download"></i>
                                Baixar App Professor
                            </a>
                        </div>

                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Este é um arquivo APK para Android. Certifique-se de que seu dispositivo permite instalação de fontes desconhecidas.
                        </div>

                        <h4 class="mb-4">
                            <i class="fas fa-list-ol text-primary"></i>
                            Como Instalar
                        </h4>

                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Baixe o arquivo APK</h5>
                                <p>Clique no botão "Baixar App Professor" acima para baixar o arquivo App_Professor.apk para seu dispositivo Android.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Habilite fontes desconhecidas</h5>
                                <p>Vá em <strong>Configurações > Segurança > Fontes desconhecidas</strong> e habilite a instalação de aplicativos de fontes desconhecidas.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Instale o aplicativo</h5>
                                <p>Abra o arquivo baixado e siga as instruções na tela para instalar o aplicativo.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Faça login</h5>
                                <p>Abra o aplicativo e faça login com suas credenciais de professor para começar a usar.</p>
                            </div>
                        </div>

                        <h4 class="mb-4 mt-5">
                            <i class="fas fa-star text-warning"></i>
                            Funcionalidades do App
                        </h4>

                        <ul class="feature-list">
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>Escaneamento de QR Code:</strong> Escaneie QR codes dos alunos para acesso rápido
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>Gerenciamento de Alunos:</strong> Visualize e edite informações dos alunos
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>Ações Rápidas:</strong> Adicione/remova XP e moedas instantaneamente
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>Histórico Completo:</strong> Acompanhe todas as ações realizadas
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <strong>Interface Otimizada:</strong> Design responsivo para dispositivos móveis
                            </li>
                        </ul>

                        <div class="text-center mt-5">
                            <a href="painel/professor/professor_dashboard.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i>
                                Voltar ao Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Verificar se o download foi iniciado
        document.querySelector('.btn-download').addEventListener('click', function() {
            // Mostrar mensagem de sucesso
            setTimeout(() => {
                alert('Download iniciado! Verifique a pasta de downloads do seu dispositivo.');
            }, 1000);
        });

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
