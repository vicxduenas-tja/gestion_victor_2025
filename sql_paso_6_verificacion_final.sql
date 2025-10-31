-- ============================================================================
-- PASO 6: VERIFICACIÓN FINAL
-- ============================================================================

USE gestion_archivos;

-- 1. Verificar que documentos_respondidos existe
SELECT 'VERIFICANDO TABLA documentos_respondidos:' AS '';
SELECT COUNT(*) as total_registros FROM documentos_respondidos;

-- 2. Verificar estados de hojas_ruta
SELECT 'VERIFICANDO ESTADOS DE hojas_ruta:' AS '';
SELECT COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'hojas_ruta'
AND COLUMN_NAME = 'estado';

-- 3. Verificar estados de documentos_oficiales
SELECT 'VERIFICANDO ESTADOS DE documentos_oficiales:' AS '';
SELECT COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'documentos_oficiales'
AND COLUMN_NAME = 'estado';

-- 4. Verificar campo sin_limite en ambas tablas
SELECT 'VERIFICANDO CAMPO sin_limite:' AS '';
SELECT
    'hojas_ruta' as tabla,
    COUNT(*) as campo_existe
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'hojas_ruta' AND COLUMN_NAME = 'sin_limite'
UNION ALL
SELECT
    'documentos_oficiales' as tabla,
    COUNT(*) as campo_existe
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'documentos_oficiales' AND COLUMN_NAME = 'sin_limite';

-- 5. Verificar abreviaturas en oficinas
SELECT 'VERIFICANDO ABREVIATURAS DE OFICINAS:' AS '';
SELECT id, nombre, abreviatura FROM oficinas;

-- 6. Resumen final
SELECT 'TODAS LAS VERIFICACIONES COMPLETADAS' AS Resultado;
