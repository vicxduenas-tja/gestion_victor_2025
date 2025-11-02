-- ================================================
-- SCRIPT 2: Agregar campo abreviatura a oficinas
-- ================================================
-- Este script agrega el campo abreviatura (4 caracteres)
-- necesario para el formato de nombres de archivos
-- ================================================

-- Agregar columna abreviatura si no existe
ALTER TABLE oficinas
ADD COLUMN IF NOT EXISTS abreviatura VARCHAR(4) DEFAULT NULL AFTER nombre;

-- Verificar columna
SELECT 'Campo abreviatura agregado' AS Resultado;

-- ================================================
-- IMPORTANTE: ACTUALIZAR ABREVIATURAS MANUALMENTE
-- ================================================
-- Ejecuta estos UPDATE según los nombres reales de tus oficinas
-- Cambia las condiciones WHERE según tu base de datos
-- ================================================

-- EJEMPLO: Buscar por palabra clave en el nombre
UPDATE oficinas SET abreviatura = 'CAPA' WHERE nombre LIKE '%CAPACITACION%' OR nombre LIKE '%CAPACITACIÓN%';
UPDATE oficinas SET abreviatura = 'DELI' WHERE nombre LIKE '%DELITOS%';
UPDATE oficinas SET abreviatura = 'PREV' WHERE nombre LIKE '%PREVENCION%' OR nombre LIKE '%PREVENCIÓN%';
UPDATE oficinas SET abreviatura = 'INVE' WHERE nombre LIKE '%INVESTIGACION%' OR nombre LIKE '%INVESTIGACIÓN%';
UPDATE oficinas SET abreviatura = 'ADMI' WHERE nombre LIKE '%ADMINISTRATIVA%';
UPDATE oficinas SET abreviatura = 'RRHH' WHERE nombre LIKE '%RECURSOS%';
UPDATE oficinas SET abreviatura = 'SIST' WHERE nombre LIKE '%SISTEMAS%' OR nombre LIKE '%INFORMÁTICA%';
UPDATE oficinas SET abreviatura = 'JURI' WHERE nombre LIKE '%JURIDICA%' OR nombre LIKE '%JURÍDICA%' OR nombre LIKE '%LEGAL%';
UPDATE oficinas SET abreviatura = 'CONT' WHERE nombre LIKE '%CONTABILIDAD%' OR nombre LIKE '%CONTABLE%';
UPDATE oficinas SET abreviatura = 'ALMG' WHERE nombre LIKE '%ALMACEN%' OR nombre LIKE '%ALMACÉN%';
UPDATE oficinas SET abreviatura = 'TRAN' WHERE nombre LIKE '%TRANSPORTE%';
UPDATE oficinas SET abreviatura = 'COMU' WHERE nombre LIKE '%COMUNICACION%' OR nombre LIKE '%COMUNICACIÓN%';
UPDATE oficinas SET abreviatura = 'PLAN' WHERE nombre LIKE '%PLANIFICACION%' OR nombre LIKE '%PLANIFICACIÓN%';
UPDATE oficinas SET abreviatura = 'AUDT' WHERE nombre LIKE '%AUDITORIA%' OR nombre LIKE '%AUDITORÍA%';
UPDATE oficinas SET abreviatura = 'DIRE' WHERE nombre LIKE '%DIRECCION%' OR nombre LIKE '%DIRECCIÓN%';

-- ================================================
-- ALTERNATIVA: Actualizar por ID específico
-- ================================================
-- Si los LIKE no funcionan, actualiza manualmente por ID:
-- UPDATE oficinas SET abreviatura = 'XXXX' WHERE id = 1;
-- UPDATE oficinas SET abreviatura = 'YYYY' WHERE id = 2;
-- etc...

-- Verificar resultados
SELECT id, nombre, abreviatura
FROM oficinas
ORDER BY id;

-- Verificar cuántas oficinas AÚN NO tienen abreviatura
SELECT COUNT(*) as sin_abreviatura
FROM oficinas
WHERE abreviatura IS NULL OR abreviatura = '';

-- Si hay oficinas sin abreviatura, las mostramos
SELECT id, nombre, 'NECESITA ABREVIATURA' as estado
FROM oficinas
WHERE abreviatura IS NULL OR abreviatura = ''
ORDER BY id;
