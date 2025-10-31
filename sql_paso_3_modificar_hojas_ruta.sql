-- ============================================================================
-- PASO 3: MODIFICAR TABLA hojas_ruta
-- ============================================================================

USE gestion_archivos;

-- 1. Permitir NULL en id_oficina_destino (para documentos "Para Conocimiento")
ALTER TABLE `hojas_ruta`
MODIFY COLUMN `id_oficina_destino` INT DEFAULT NULL;

-- 2. Agregar estado 'conocimiento' al ENUM (si no existe)
-- Primero verificar estados actuales
SELECT COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'hojas_ruta'
AND COLUMN_NAME = 'estado';

-- Agregar nuevo estado
ALTER TABLE `hojas_ruta`
MODIFY COLUMN `estado` ENUM('en_proceso','completado','archivado','conocimiento')
DEFAULT 'en_proceso';

-- 3. Agregar campo sin_limite si no existe
-- Verificar primero si existe
SELECT COUNT(*) as existe_sin_limite
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'hojas_ruta'
AND COLUMN_NAME = 'sin_limite';

-- Agregar si no existe (ignorar error si ya existe)
ALTER TABLE `hojas_ruta`
ADD COLUMN `sin_limite` TINYINT(1) DEFAULT 0;

-- Verificar cambios
SELECT 'Tabla hojas_ruta modificada exitosamente' AS Resultado;
DESCRIBE hojas_ruta;
