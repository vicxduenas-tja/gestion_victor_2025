-- ============================================================================
-- PASO 4: MODIFICAR TABLA documentos_oficiales
-- ============================================================================

USE gestion_archivos;

-- 1. Verificar estructura actual
DESCRIBE documentos_oficiales;

-- 2. Agregar campo sin_limite si no existe
ALTER TABLE `documentos_oficiales`
ADD COLUMN IF NOT EXISTS `sin_limite` TINYINT(1) DEFAULT 0;

-- 3. Modificar ENUM de prioridad para incluir 'urgente'
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `prioridad` ENUM('alta','media','baja','urgente') DEFAULT 'media';

-- 4. Modificar ENUM de estado para incluir 'respondido_retraso'
-- Verificar estados actuales
SELECT COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'documentos_oficiales'
AND COLUMN_NAME = 'estado';

-- Agregar nuevos estados
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `estado` ENUM('delegado','en_progreso','respondiendo','completado','archivado','respondido_retraso')
DEFAULT 'delegado';

-- 5. Permitir NULL en id_oficina_destino
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `id_oficina_destino` INT DEFAULT NULL;

-- Verificar cambios
SELECT 'Tabla documentos_oficiales modificada exitosamente' AS Resultado;
DESCRIBE documentos_oficiales;
