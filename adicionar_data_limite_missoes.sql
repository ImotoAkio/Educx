-- Script para adicionar campo de data limite nas missões
-- Execute este script no seu banco de dados MySQL

ALTER TABLE missoes 
ADD COLUMN data_limite DATE NULL AFTER status;

-- Atualizar missões existentes sem data limite para terem validade de 30 dias a partir de hoje
UPDATE missoes 
SET data_limite = DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
WHERE data_limite IS NULL;

-- Opcional: Tornar o campo obrigatório para novas missões (descomente se desejar)
-- ALTER TABLE missoes 
-- MODIFY COLUMN data_limite DATE NOT NULL;

