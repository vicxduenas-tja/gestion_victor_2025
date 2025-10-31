-- =====================================================
-- SCRIPT SQL PARA SISTEMA DE NOTIFICACIONES
-- =====================================================
-- Ejecuta este script en phpMyAdmin o tu gestor de BD
-- =====================================================

-- Tabla para registrar qué usuario vio qué documento de conocimiento
CREATE TABLE IF NOT EXISTS `conocimiento_visto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_documento_conocimiento` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha_vista` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_documento_conocimiento` (`id_documento_conocimiento`),
  KEY `id_usuario` (`id_usuario`),
  UNIQUE KEY `unico_usuario_documento` (`id_documento_conocimiento`, `id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
COMMENT='Registra qué usuarios vieron cada documento de conocimiento';

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
-- Notas:
-- 1. La tabla documentos_oficiales ya tiene el campo fecha_visualizado
-- 2. Esta nueva tabla es solo para documentos de conocimiento
-- 3. UNIQUE KEY previene duplicados (un usuario solo puede ver un doc una vez)
-- =====================================================
