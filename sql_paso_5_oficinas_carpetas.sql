-- ============================================================================
-- PASO 5: MODIFICAR TABLA oficinas Y VERIFICAR CARPETAS
-- ============================================================================

USE gestion_archivos;

-- 1. Agregar campo abreviatura a oficinas si no existe
ALTER TABLE `oficinas`
ADD COLUMN IF NOT EXISTS `abreviatura` VARCHAR(4) DEFAULT NULL;

-- Agregar índice único si no existe
-- (Puede dar error si ya existe, es normal)
ALTER TABLE `oficinas`
ADD UNIQUE KEY `idx_abreviatura` (`abreviatura`);

-- 2. Verificar tabla carpetas
DESCRIBE carpetas;

-- 3. Agregar campo es_fija si no existe
ALTER TABLE `carpetas`
ADD COLUMN IF NOT EXISTS `es_fija` INT DEFAULT 0 COMMENT '1 = No se puede eliminar';

-- 4. Ver carpetas existentes
SELECT id, nombre, id_usuario, es_fija
FROM carpetas
ORDER BY id;

SELECT 'Tablas oficinas y carpetas modificadas exitosamente' AS Resultado;
