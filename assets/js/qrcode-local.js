/**
 * QRCode.js - Biblioteca para gerar QR Codes
 * Versão simplificada e local
 */
(function() {
    'use strict';
    
    // QRCode namespace
    window.QRCode = {
        toCanvas: function(canvas, text, options, callback) {
            try {
                // Opções padrão
                var opts = {
                    width: options.width || 200,
                    margin: options.margin || 2,
                    color: options.color || { dark: '#000000', light: '#FFFFFF' }
                };
                
                // Criar um QR code simples usando uma API online
                var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + opts.width + 'x' + opts.width + '&data=' + encodeURIComponent(text);
                
                // Criar imagem
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    // Configurar canvas
                    canvas.width = opts.width;
                    canvas.height = opts.width;
                    var ctx = canvas.getContext('2d');
                    
                    // Desenhar imagem no canvas
                    ctx.drawImage(img, 0, 0, opts.width, opts.width);
                    
                    if (callback) callback(null);
                };
                img.onerror = function() {
                    if (callback) callback(new Error('Erro ao carregar QR Code'));
                };
                img.src = qrUrl;
                
            } catch (error) {
                if (callback) callback(error);
            }
        }
    };
})();
