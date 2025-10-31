-- ============================================================================
-- SCRIPT DE ACTUALIZACIÓN DE BASE DE DATOS PARA SISTEMA DE REPORTES
-- Base de Datos: gestion_archivos
-- Fecha: 2025-10-31
-- ============================================================================

-- Este script contiene todas las modificaciones necesarias para que el
-- módulo de Reportes funcione correctamente.

-- ============================================================================
-- 1. MODIFICAR TABLA hojas_ruta
-- ============================================================================

-- Permitir NULL en id_oficina_destino (para documentos "Para Conocimiento")
ALTER TABLE `hojas_ruta`
MODIFY COLUMN `id_oficina_destino` INT DEFAULT NULL;

-- Agregar estado 'conocimiento' al ENUM
ALTER TABLE `hojas_ruta`
MODIFY COLUMN `estado` ENUM('en_proceso','completado','archivado','conocimiento')
DEFAULT 'en_proceso';

-- Agregar campo sin_limite si no existe
ALTER TABLE `hojas_ruta`
ADD COLUMN IF NOT EXISTS `sin_limite` TINYINT(1) DEFAULT 0;

-- ============================================================================
-- 2. MODIFICAR TABLA documentos_oficiales
-- ============================================================================

-- Agregar campo sin_limite si no existe
ALTER TABLE `documentos_oficiales`
ADD COLUMN IF NOT EXISTS `sin_limite` TINYINT(1) DEFAULT 0;

-- Agregar prioridad 'urgente' al ENUM
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `prioridad` ENUM('alta','media','baja','urgente') DEFAULT 'media';

-- Agregar estados adicionales al ENUM
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `estado` ENUM('delegado','en_progreso','respondiendo','completado','archivado','respondido_retraso')
DEFAULT 'delegado';

-- Permitir NULL en id_oficina_destino
ALTER TABLE `documentos_oficiales`
MODIFY COLUMN `id_oficina_destino` INT DEFAULT NULL;

-- ============================================================================
-- 3. CREAR/VERIFICAR TABLA documentos_respondidos
-- ============================================================================

CREATE TABLE IF NOT EXISTS `documentos_respondidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_documento` INT NOT NULL COMMENT 'FK a documentos_oficiales',
  `archivo_respuesta` VARCHAR(255) NOT NULL,
  `id_oficina_respondio` INT NOT NULL,
  `id_usuario_respondio` INT NOT NULL,
  `fecha_respuesta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `observaciones` TEXT,
  `estado` INT DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_id_documento` (`id_documento`),
  KEY `idx_id_oficina_respondio` (`id_oficina_respondio`),
  KEY `idx_id_usuario_respondio` (`id_usuario_respondio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. VERIFICAR TABLA oficinas
-- ============================================================================

-- Agregar campo abreviatura si no existe
ALTER TABLE `oficinas`
ADD COLUMN IF NOT EXISTS `abreviatura` VARCHAR(4) DEFAULT NULL,
ADD UNIQUE KEY IF NOT EXISTS `idx_abreviatura` (`abreviatura`);

-- ============================================================================
-- 5. VERIFICAR CARPETAS FIJAS DEL SISTEMA
-- ============================================================================

-- Agregar campo es_fija si no existe
ALTER TABLE `carpetas`
ADD COLUMN IF NOT EXISTS `es_fija` INT DEFAULT 0 COMMENT '1 = No se puede eliminar';

-- Insertar carpetas fijas si no existen
INSERT INTO `carpetas` (`id`, `nombre`, `id_usuario`, `es_fija`, `estado`)
VALUES
(1, 'Documentos Respondidos', 1, 1, 1),
(2, 'Documentos para Conocimiento', 1, 1, 1),
(3, 'Documentos Importantes', 1, 1, 1)
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`),
  `es_fija` = VALUES(`es_fija`);

-- ============================================================================
-- 6. CREAR DIRECTORIOS NECESARIOS (Nota: Ejecutar manualmente si no existen)
-- ============================================================================

/*
DIRECTORIOS A CREAR MANUALMENTE:

Assets/documentos_oficiales/
├── en_proceso/           # PDFs de documentos delegados pendientes
├── completados/          # PDFs de respuestas de usuarios
└── archivado/            # PDFs archivados por admin

Assets/documentos_para_conocimiento/   # PDFs para conocimiento

PERMISOS RECOMENDADOS:
chmod 755 Assets/documentos_oficiales
chmod 755 Assets/documentos_oficiales/en_proceso
chmod 755 Assets/documentos_oficiales/completados
chmod 755 Assets/documentos_oficiales/archivado
chmod 755 Assets/documentos_para_conocimiento
*/

-- ============================================================================
-- 7. DATOS DE EJEMPLO PARA PRUEBAS (OPCIONAL)
-- ============================================================================

-- Ejemplo de oficina (si no existe)
INSERT IGNORE INTO `oficinas` (`id`, `nombre`, `siglas`, `abreviatura`, `estado`)
VALUES
(1, 'DIRECCIÓN DEPARTAMENTAL FELCV', 'DIRE', 'DIRE', 1),
(3, 'UNIDAD DE PREVENCIÓN, CAPACITACIÓN Y COORDINACIÓN', 'CAPA', 'CAPA', 1);

-- ============================================================================
-- 8. ÍNDICES ADICIONALES PARA MEJOR RENDIMIENTO
-- ============================================================================

-- Índices en hojas_ruta
CREATE INDEX IF NOT EXISTS `idx_estado` ON `hojas_ruta`(`estado`);
CREATE INDEX IF NOT EXISTS `idx_fecha_recepcion` ON `hojas_ruta`(`fecha_recepcion`);
CREATE INDEX IF NOT EXISTS `idx_id_oficina_destino` ON `hojas_ruta`(`id_oficina_destino`);

-- Índices en documentos_oficiales
CREATE INDEX IF NOT EXISTS `idx_estado` ON `documentos_oficiales`(`estado`);
CREATE INDEX IF NOT EXISTS `idx_fecha_recepcion` ON `documentos_oficiales`(`fecha_recepcion`);
CREATE INDEX IF NOT EXISTS `idx_fecha_limite` ON `documentos_oficiales`(`fecha_limite`);
CREATE INDEX IF NOT EXISTS `idx_id_oficina_destino` ON `documentos_oficiales`(`id_oficina_destino`);

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================

-- NOTAS IMPORTANTES:
-- 1. Hacer backup de la BD antes de ejecutar este script
-- 2. Ejecutar este script con usuario que tenga permisos ALTER TABLE
-- 3. Verificar que todos los directorios físicos existan
-- 4. Si hay errores, revisar el log de MySQL para detalles

SELECT 'Script de actualización ejecutado correctamente' AS Resultado;
