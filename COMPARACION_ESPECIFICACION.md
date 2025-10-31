# 📊 COMPARACIÓN: ESPECIFICACIÓN vs IMPLEMENTACIÓN

## ❌ PROBLEMAS ENCONTRADOS

### 1. ❌ **contarPendientes() USA ESTADO INCORRECTO**

**ESPECIFICACIÓN DICE:**
> El contador del Calendario cuenta la suma de las tareas en estado 'delegado' y los documentos 'conocimiento' que el usuario aún no ha marcado como vistos.

**IMPLEMENTACIÓN ACTUAL:**
```php
// CalendarioModel.php línea 61-70
public function contarPendientes($id_usuario)
{
    $sql = "SELECT COUNT(*) as total
            FROM documentos_oficiales
            WHERE id_usuario_asignado = $id_usuario
            AND estado = 'pendiente'";  // ❌ ESTADO 'pendiente' NO EXISTE

    $resultado = $this->select($sql);
    return $resultado['total'];
}
```

**PROBLEMA:**
- ❌ Usa estado 'pendiente' que NO existe
- ❌ NO considera estado 'delegado'
- ❌ NO cuenta documentos 'conocimiento'
- ❌ NO considera rol (Admin ve todo, Usuario solo su oficina)

**SOLUCIÓN REQUERIDA:**
```php
public function contarPendientes($id_usuario, $rol = null, $id_oficina = null)
{
    if ($rol == 1) {
        // Admin ve TODOS
        $sql = "SELECT COUNT(*) as total
                FROM documentos_oficiales
                WHERE estado = 'delegado'";
    } else {
        // Usuario ve solo su oficina
        $sql = "SELECT
                    (SELECT COUNT(*) FROM documentos_oficiales
                     WHERE id_oficina_destino = $id_oficina AND estado = 'delegado') +
                    (SELECT COUNT(*) FROM hojas_ruta
                     WHERE estado = 'conocimiento')
                AS total";
    }

    $resultado = $this->select($sql);
    return $resultado['total'];
}
```

---

### 2. ❌ **completarTarea() NO DETECTA RETRASOS**

**ESPECIFICACIÓN DICE:**
> "Solo Completar": La tarea finaliza con estado 'completado' o 'respondido_retraso' (si se pasó la fecha límite).

**IMPLEMENTACIÓN ACTUAL:**
```php
// Calendario.php línea 181
$estado_final = $archivar == '1' ? 'archivado' : 'completado';  // ❌ SIEMPRE 'completado'
```

**PROBLEMA:**
- ❌ NO verifica si la respuesta llegó después de fecha_limite
- ❌ NUNCA asigna estado 'respondido_retraso'

**SOLUCIÓN REQUERIDA:**
```php
// Verificar si llegó con retraso
$enRetraso = false;
if (!empty($documento['fecha_limite']) && $documento['sin_limite'] == 0) {
    $fechaLimite = strtotime($documento['fecha_limite']);
    $ahora = time();
    $enRetraso = ($ahora > $fechaLimite);
}

if ($archivar == '1') {
    $estado_final = $enRetraso ? 'respondido_retraso' : 'completado';
    // Después actualizar hojas_ruta a 'archivado'
} else {
    $estado_final = $enRetraso ? 'respondido_retraso' : 'completado';
}
```

---

### 3. ❌ **NO REGISTRA EN documentos_respondidos**

**ESPECIFICACIÓN DICE (del INFORME_TECNICO_COMPLETO.md):**
> Cuando se completa una tarea se debe INSERT en documentos_respondidos (registro histórico).

**IMPLEMENTACIÓN ACTUAL:**
- ❌ completarTarea() NO hace INSERT en documentos_respondidos

**SOLUCIÓN REQUERIDA:**
```php
// Después de completar, registrar en histórico
require_once 'Models/ReportesModel.php';
$reporteModel = new ReportesModel();

// Obtener datos de la oficina y usuario
$sqlUsuario = "SELECT id_oficina FROM usuarios WHERE id = {$this->id_usuario}";
$usuario = $this->model->select($sqlUsuario);

$sqlInsert = "INSERT INTO documentos_respondidos
              (id_documento, archivo_respuesta, id_oficina_respondio,
               id_usuario_respondio, fecha_respuesta, observaciones)
              VALUES (?, ?, ?, ?, NOW(), ?)";

$datosResp = [
    $id_documento,
    $nombreRespuesta,
    $usuario['id_oficina'],
    $this->id_usuario,
    $comentarios
];

$this->model->insertar($sqlInsert, $datosResp);
```

---

### 4. ❌ **NOMBRE DE ARCHIVO INCORRECTO**

**ESPECIFICACIÓN DICE:**
> El archivo de respuesta se nombra automáticamente con el formato: **RES-Asunto_Limpio-ABREVIATURA_OFICINA-001.pdf**

**IMPLEMENTACIÓN ACTUAL:**
```php
// Calendario.php línea 128
$nombreRespuesta = 'RESP_' . $documento['numero_documento'] . '.pdf';
// ❌ Genera: RESP_0001.pdf
```

**PROBLEMA:**
- ❌ NO incluye asunto limpio
- ❌ NO incluye abreviatura de la oficina
- ❌ NO tiene contador correlativo

**SOLUCIÓN REQUERIDA:**
```php
// Obtener abreviatura de la oficina del usuario
$sqlOficina = "SELECT abreviatura FROM oficinas o
               INNER JOIN usuarios u ON u.id_oficina = o.id
               WHERE u.id = {$this->id_usuario}";
$oficina = $this->model->select($sqlOficina);
$abrev = $oficina['abreviatura'] ?? 'SIN';

// Limpiar asunto (quitar caracteres especiales)
$asuntoLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $documento['asunto']);
$asuntoLimpio = substr($asuntoLimpio, 0, 30); // Máximo 30 caracteres

// Generar número correlativo por oficina
$sqlCount = "SELECT COUNT(*) as total FROM documentos_respondidos
             WHERE id_oficina_respondio = {$usuario['id_oficina']}";
$count = $this->model->select($sqlCount);
$numero = str_pad($count['total'] + 1, 3, '0', STR_PAD_LEFT);

$nombreRespuesta = "RES-{$asuntoLimpio}-{$abrev}-{$numero}.pdf";
// ✅ Genera: RES-Informe_Mensual_Actividades-CAPA-001.pdf
```

---

### 5. ❌ **NO ACTUALIZA hojas_ruta AL ARCHIVAR**

**ESPECIFICACIÓN DICE:**
> "Archivar y Completar": el estado final de la hojas_ruta pasa a 'archivado'.

**IMPLEMENTACIÓN ACTUAL:**
- ❌ completarTarea() NO actualiza tabla hojas_ruta

**SOLUCIÓN REQUERIDA:**
```php
if ($archivar == '1') {
    // Actualizar documentos_oficiales
    $estado_final = $enRetraso ? 'respondido_retraso' : 'completado';
    $this->model->completarTarea($id_documento, $nombreRespuesta, $comentarios, $_SESSION['id'], $estado_final);

    // Actualizar hojas_ruta a 'archivado'
    $sqlUpdateHR = "UPDATE hojas_ruta
                    SET estado = 'archivado', fecha_completado = NOW()
                    WHERE numero_registro = ?";
    $this->model->save($sqlUpdateHR, [$documento['numero_documento']]);
}
```

---

## 📋 RESUMEN DE CORRECCIONES NECESARIAS

| # | Problema | Archivo | Línea | Prioridad |
|---|----------|---------|-------|-----------|
| 1 | Contador pendientes incorrecto | CalendarioModel.php | 61-70 | 🔴 CRÍTICO |
| 2 | No detecta retrasos | Calendario.php | 181 | 🔴 CRÍTICO |
| 3 | No registra en documentos_respondidos | Calendario.php | 183 | 🔴 CRÍTICO |
| 4 | Nombre archivo incorrecto | Calendario.php | 128 | 🟡 IMPORTANTE |
| 5 | No actualiza hojas_ruta | Calendario.php | 182 | 🟡 IMPORTANTE |

---

## ✅ LO QUE SÍ ESTÁ CORRECTO

| Característica | Estado |
|----------------|--------|
| ✅ Módulo Hoja de Ruta | Funcional |
| ✅ Delegación a oficinas | Funcional |
| ✅ Publicar Para Conocimiento | Funcional |
| ✅ Visualización de documentos | Funcional |
| ✅ Estructura de directorios | Creada |
| ✅ Reportes con 3 tablas | Implementado |
| ✅ Modal interactivo | Funcional |
| ✅ Base de datos | Script SQL listo |

---

## 🚀 PRÓXIMOS PASOS

1. ✅ **Corregir contarPendientes()** en CalendarioModel.php
2. ✅ **Corregir completarTarea()** en Calendario.php:
   - Detectar retrasos
   - Registrar en documentos_respondidos
   - Generar nombre de archivo correcto
   - Actualizar hojas_ruta al archivar
3. ✅ **Probar flujo completo** de delegación → respuesta
4. ✅ **Commit y push** de correcciones

---

## 📌 NOTA IMPORTANTE

El sistema funciona pero con **lógica de negocio incompleta**. Los 5 problemas listados arriba hacen que:

- ❌ El contador del sidebar no funcione
- ❌ No se detecten documentos respondidos con retraso
- ❌ Los reportes de productividad estén vacíos (sin registros en documentos_respondidos)
- ❌ Los archivos de respuesta no tengan nombres descriptivos

**¿Procedo a corregir TODOS estos problemas ahora?**
