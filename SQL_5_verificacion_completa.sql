-- ================================================
-- SCRIPT 5: Verificación Completa de Base de Datos
-- ================================================
-- Este script verifica que TODAS las modificaciones
-- se aplicaron correctamente
-- ================================================

-- 1. Verificar que documentos_respondidos existe
SELECT 'Verificando tabla documentos_respondidos...' AS Paso;
SELECT
    CASE
        WHEN COUNT(*) > 0 THEN '✅ Tabla documentos_respondidos EXISTE'
        ELSE '❌ Tabla documentos_respondidos NO EXISTE'
    END AS Resultado
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name = 'documentos_respondidos';

-- Ver estructura de documentos_respondidos
DESCRIBE documentos_respondidos;

-- 2. Verificar campo abreviatura en oficinas
SELECT 'Verificando campo abreviatura en oficinas...' AS Paso;
SELECT
    CASE
        WHEN COUNT(*) > 0 THEN '✅ Campo abreviatura EXISTE en oficinas'
        ELSE '❌ Campo abreviatura NO EXISTE en oficinas'
    END AS Resultado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'oficinas'
AND column_name = 'abreviatura';

-- Ver oficinas y sus abreviaturas
SELECT id, nombre, abreviatura,
    CASE
        WHEN abreviatura IS NULL OR abreviatura = '' THEN '❌ SIN ABREVIATURA'
        ELSE '✅ OK'
    END AS Estado
FROM oficinas
ORDER BY id;

-- Contar oficinas sin abreviatura
SELECT
    COUNT(*) as total_oficinas,
    SUM(CASE WHEN abreviatura IS NULL OR abreviatura = '' THEN 1 ELSE 0 END) as sin_abreviatura,
    SUM(CASE WHEN abreviatura IS NOT NULL AND abreviatura != '' THEN 1 ELSE 0 END) as con_abreviatura
FROM oficinas;

-- 3. Verificar campo sin_limite en hojas_ruta
SELECT 'Verificando campo sin_limite en hojas_ruta...' AS Paso;
SELECT
    CASE
        WHEN COUNT(*) > 0 THEN '✅ Campo sin_limite EXISTE en hojas_ruta'
        ELSE '❌ Campo sin_limite NO EXISTE en hojas_ruta'
    END AS Resultado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'hojas_ruta'
AND column_name = 'sin_limite';

-- 4. Verificar estado 'respondido_retraso' en documentos_oficiales
SELECT 'Verificando estado respondido_retraso...' AS Paso;
SHOW COLUMNS FROM documentos_oficiales WHERE Field = 'estado';

-- 5. Resumen estadístico
SELECT '==================== RESUMEN ESTADÍSTICO ====================' AS '';

SELECT 'DOCUMENTOS OFICIALES' AS Tabla, COUNT(*) AS Total FROM documentos_oficiales
UNION ALL
SELECT 'HOJAS DE RUTA', COUNT(*) FROM hojas_ruta
UNION ALL
SELECT 'DOCUMENTOS RESPONDIDOS', COUNT(*) FROM documentos_respondidos
UNION ALL
SELECT 'OFICINAS', COUNT(*) FROM oficinas
UNION ALL
SELECT 'USUARIOS', COUNT(*) FROM usuarios;

-- 6. Ver estados actuales de documentos
SELECT 'Estados de Documentos Oficiales:' AS '';
SELECT estado, COUNT(*) as cantidad
FROM documentos_oficiales
GROUP BY estado
ORDER BY cantidad DESC;

-- 7. Ver últimas respuestas registradas
SELECT 'Últimas Respuestas Registradas (Top 5):' AS '';
SELECT
    dr.id,
    do.numero_documento,
    do.asunto,
    dr.archivo_respuesta,
    o.nombre as oficina,
    CONCAT(u.nombre, ' ', u.apellido) as usuario,
    dr.fecha_respuesta
FROM documentos_respondidos dr
INNER JOIN documentos_oficiales do ON dr.id_documento = do.id
INNER JOIN oficinas o ON dr.id_oficina_respondio = o.id
INNER JOIN usuarios u ON dr.id_usuario_respondio = u.id
ORDER BY dr.fecha_respuesta DESC
LIMIT 5;

-- 8. Verificación final
SELECT '==================== VERIFICACIÓN FINAL ====================' AS '';
SELECT
    CASE
        WHEN (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'documentos_respondidos') > 0
        AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'oficinas' AND column_name = 'abreviatura') > 0
        AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'hojas_ruta' AND column_name = 'sin_limite') > 0
        THEN '✅✅✅ TODAS LAS MODIFICACIONES APLICADAS CORRECTAMENTE ✅✅✅'
        ELSE '❌ FALTAN ALGUNAS MODIFICACIONES - Revisar arriba'
    END AS RESULTADO_FINAL;
