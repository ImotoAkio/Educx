-- Script para adicionar a coluna 'tipo' na tabela produtos
-- Execute este script no seu banco de dados MySQL

ALTER TABLE produtos
ADD COLUMN tipo ENUM('produto', 'powercard') DEFAULT 'produto' AFTER quantidade;

-- Opcional: Atualizar produtos existentes
-- Por padrão, todos os produtos existentes serão definidos como 'produto'
UPDATE produtos
SET tipo = 'produto'
WHERE tipo IS NULL OR tipo = '';

