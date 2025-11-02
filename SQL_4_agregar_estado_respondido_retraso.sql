-- ================================================
-- SCRIPT 4: Agregar estado 'respondido_retraso'
-- ================================================
-- Este estado marca tareas que fueron respondidas
-- después de su fecha límite
-- ================================================

ALTER TABLE documentos_oficiales
MODIFY COLUMN estado
ENUM('delegado','en_progreso','completado','respondido_retraso','archivado','conocimiento')
DEFAULT 'delegado'
COMMENT 'Estados del documento: delegado=asignado, en_progreso=usuario lo vió, completado=a tiempo, respondido_retraso=tarde, archivado=finalizado';

-- Verificar que se agregó
SELECT 'Estado respondido_retraso agregado' AS Resultado;

-- Ver la definición completa de la columna
SHOW COLUMNS FROM documentos_oficiales WHERE Field = 'estado';

-- Ver cuántos documentos hay en cada estado
SELECT estado, COUNT(*) as cantidad
FROM documentos_oficiales
GROUP BY estado
ORDER BY cantidad DESC;
