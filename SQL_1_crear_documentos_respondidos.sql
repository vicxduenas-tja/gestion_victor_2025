-- ================================================
-- SCRIPT 1: Crear tabla documentos_respondidos
-- ================================================
-- Este script crea la tabla histórica para registrar
-- todas las respuestas de los usuarios a las tareas
-- ================================================

CREATE TABLE IF NOT EXISTS `documentos_respondidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documento` int(11) NOT NULL COMMENT 'FK a documentos_oficiales',
  `archivo_respuesta` varchar(255) DEFAULT NULL COMMENT 'Nombre del archivo PDF de respuesta',
  `id_oficina_respondio` int(11) NOT NULL COMMENT 'Oficina que respondió',
  `id_usuario_respondio` int(11) NOT NULL COMMENT 'Usuario que respondió',
  `fecha_respuesta` datetime DEFAULT NULL COMMENT 'Fecha y hora de la respuesta',
  `observaciones` text COMMENT 'Comentarios del usuario al responder',
  PRIMARY KEY (`id`),
  KEY `fk_doc_resp_documento` (`id_documento`),
  KEY `fk_doc_resp_oficina` (`id_oficina_respondio`),
  KEY `fk_doc_resp_usuario` (`id_usuario_respondio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Histórico de documentos respondidos';

-- Verificar que se creó correctamente
SELECT 'Tabla documentos_respondidos creada exitosamente' AS Resultado;
DESCRIBE documentos_respondidos;
