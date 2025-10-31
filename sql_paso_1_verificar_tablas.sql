-- ============================================================================
-- PASO 1: VERIFICAR TABLAS EXISTENTES
-- ============================================================================
-- Este script solo VERIFICA qué tablas ya tienes
-- NO modifica nada

USE gestion_archivos;

-- Ver todas las tablas
SHOW TABLES;

-- Ver estructura de las tablas principales
DESCRIBE usuarios;
DESCRIBE oficinas;
DESCRIBE hojas_ruta;
DESCRIBE documentos_oficiales;
DESCRIBE carpetas;
DESCRIBE archivos;

-- Verificar si existe documentos_respondidos
SHOW TABLES LIKE 'documentos_respondidos';
