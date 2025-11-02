-- ================================================
-- SCRIPT 3: Agregar campo sin_limite a hojas_ruta
-- ================================================
-- Este campo indica si una tarea tiene fecha límite o no
-- ================================================

ALTER TABLE hojas_ruta
ADD COLUMN IF NOT EXISTS sin_limite TINYINT(1) DEFAULT 0
COMMENT '0=tiene límite, 1=sin límite de tiempo'
AFTER fecha_limite;

-- Verificar que se agregó
SELECT 'Campo sin_limite agregado a hojas_ruta' AS Resultado;
DESCRIBE hojas_ruta;

-- Verificar datos actuales
SELECT numero_registro, asunto, fecha_limite, sin_limite
FROM hojas_ruta
LIMIT 5;
