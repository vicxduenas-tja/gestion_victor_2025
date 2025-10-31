-- ============================================================================
-- PASO 2: CREAR TABLA documentos_respondidos
-- ============================================================================
-- Esta es la tabla MÁS IMPORTANTE para que funcionen los reportes

USE gestion_archivos;

-- Crear tabla documentos_respondidos
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

-- Verificar que se creó
SELECT 'Tabla documentos_respondidos creada exitosamente' AS Resultado;
DESCRIBE documentos_respondidos;
