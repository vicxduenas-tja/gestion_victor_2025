# 📘 GUÍA PASO A PASO: IMPLEMENTACIÓN EN WINDOWS

## ✅ VERIFICACIÓN: ESPECIFICACIONES VS CÓDIGO ACTUAL

### 📋 CHECKLIST DE CORRECCIONES

| # | Corrección | Especificación | Estado Código | Archivo |
|---|------------|----------------|---------------|---------|
| 1️⃣ | **Contador Sidebar** | Debe contar 'delegado' + 'conocimiento' | ✅ CORRECTO | CalendarioModel.php:61-88 |
| 2️⃣ | **Detección de Retrasos** | Comparar fecha_limite vs NOW() | ✅ CORRECTO | Calendario.php:128-135 |
| 3️⃣ | **Nombre de Archivo** | RES-Asunto-ABREV-001.pdf | ✅ CORRECTO | Calendario.php:138-159 |
| 4️⃣ | **Registro Histórico** | INSERT en documentos_respondidos | ✅ CORRECTO | Calendario.php:224-239 |
| 5️⃣ | **Actualizar Hojas de Ruta** | UPDATE estado='archivado' | ✅ CORRECTO | Calendario.php:242-249 |

### 🎯 RESULTADO DE LA VERIFICACIÓN

✅ **TODAS LAS 5 CORRECCIONES ESTÁN IMPLEMENTADAS CORRECTAMENTE**

El código actual cumple 100% con las especificaciones originales:

1. ✅ El contador del sidebar funciona correctamente
   - Admin: Cuenta TODOS los 'delegado'
   - Usuario: Cuenta 'delegado' de su oficina + 'conocimiento' globales

2. ✅ La detección de retrasos funciona
   - Compara `fecha_limite` vs `NOW()`
   - Asigna estado `'respondido_retraso'` si pasó el plazo
   - Asigna estado `'completado'` si llegó a tiempo

3. ✅ El nombre de archivo es correcto
   - Formato: `RES-Asunto_Limpio-ABREV-001.pdf`
   - Incluye abreviatura de 4 caracteres de la oficina
   - Numeración correlativa por oficina

4. ✅ Se registra en tabla histórica
   - INSERT en `documentos_respondidos`
   - Guarda: id_documento, archivo_respuesta, id_oficina, id_usuario, fecha, observaciones

5. ✅ Se actualiza hojas_ruta al archivar
   - UPDATE `estado = 'archivado'` cuando checkbox archivar está marcado
   - Registra `fecha_completado = NOW()`

---

## 🚀 GUÍA DE IMPLEMENTACIÓN PASO A PASO

### 📁 FASE 1: PREPARACIÓN Y RESPALDO (15 minutos)

#### PASO 1: Crear Respaldo Completo

**En tu servidor Windows con WAMP:**

```powershell
# Abre PowerShell en: C:\wamp64\www\gestion

# 1. Respaldar base de datos
# Abrir phpMyAdmin en tu navegador:
# http://localhost/phpmyadmin
```

**En phpMyAdmin:**
1. Selecciona la base de datos `gestion_archivos`
2. Click en pestaña **"Exportar"**
3. Método: **Rápido**
4. Formato: **SQL**
5. Click **"Continuar"**
6. Guarda el archivo como: `backup_gestion_archivos_ANTES.sql` en tu carpeta de Descargas

```powershell
# 2. Respaldar archivos PHP
cd C:\wamp64\www

# Crear carpeta de respaldo
mkdir respaldo_gestion_$(Get-Date -Format 'yyyyMMdd_HHmmss')

# Copiar todo el proyecto
Copy-Item -Path "gestion" -Destination "respaldo_gestion_$(Get-Date -Format 'yyyyMMdd_HHmmss')\gestion" -Recurse
```

**RESULTADO:**
- ✅ Tienes backup de la base de datos
- ✅ Tienes backup de los archivos PHP

---

### 💾 FASE 2: ACTUALIZACIÓN DE BASE DE DATOS (10 minutos)

#### PASO 2: Verificar Estructura de Base de Datos

**Abrir phpMyAdmin** → Base de datos `gestion_archivos`

**Ejecutar este SQL para verificar:**

```sql
-- Verificar que documentos_respondidos existe
SHOW TABLES LIKE 'documentos_respondidos';

-- Verificar estructura
DESCRIBE documentos_respondidos;

-- Verificar que oficinas tiene abreviatura
DESCRIBE oficinas;

-- Verificar estados en documentos_oficiales
SHOW COLUMNS FROM documentos_oficiales WHERE Field = 'estado';

-- Verificar hojas_ruta tiene campo sin_limite
DESCRIBE hojas_ruta;
```

**RESULTADO ESPERADO:**
```
✅ Tabla documentos_respondidos existe
✅ Campo abreviatura existe en oficinas (varchar 4)
✅ Campo sin_limite existe en hojas_ruta (tinyint)
✅ Estado 'respondido_retraso' está en ENUM de documentos_oficiales
```

#### PASO 3: Actualizar Base de Datos (SI FALTA ALGO)

**Si `documentos_respondidos` NO existe:**

```sql
CREATE TABLE IF NOT EXISTS `documentos_respondidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documento` int(11) NOT NULL,
  `archivo_respuesta` varchar(255) DEFAULT NULL,
  `id_oficina_respondio` int(11) NOT NULL,
  `id_usuario_respondio` int(11) NOT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`),
  KEY `fk_doc_resp_documento` (`id_documento`),
  KEY `fk_doc_resp_oficina` (`id_oficina_respondio`),
  KEY `fk_doc_resp_usuario` (`id_usuario_respondio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Si oficinas NO tiene abreviatura:**

```sql
ALTER TABLE oficinas
ADD COLUMN abreviatura VARCHAR(4) DEFAULT NULL AFTER nombre;

-- Agregar abreviaturas a las 15 oficinas existentes
UPDATE oficinas SET abreviatura = 'CAPA' WHERE nombre LIKE '%CAPACITACION%';
UPDATE oficinas SET abreviatura = 'DELI' WHERE nombre LIKE '%DELITOS%';
UPDATE oficinas SET abreviatura = 'PREV' WHERE nombre LIKE '%PREVENCION%';
UPDATE oficinas SET abreviatura = 'INVE' WHERE nombre LIKE '%INVESTIGACION%';
UPDATE oficinas SET abreviatura = 'ADMI' WHERE nombre LIKE '%ADMINISTRATIVA%';
UPDATE oficinas SET abreviatura = 'RRHH' WHERE nombre LIKE '%RECURSOS%';
UPDATE oficinas SET abreviatura = 'SIST' WHERE nombre LIKE '%SISTEMAS%';
UPDATE oficinas SET abreviatura = 'JURI' WHERE nombre LIKE '%JURIDICA%';
UPDATE oficinas SET abreviatura = 'CONT' WHERE nombre LIKE '%CONTABILIDAD%';
UPDATE oficinas SET abreviatura = 'ALMG' WHERE nombre LIKE '%ALMACEN%';
UPDATE oficinas SET abreviatura = 'TRAN' WHERE nombre LIKE '%TRANSPORTE%';
UPDATE oficinas SET abreviatura = 'COMU' WHERE nombre LIKE '%COMUNICACION%';
UPDATE oficinas SET abreviatura = 'PLAN' WHERE nombre LIKE '%PLANIFICACION%';
UPDATE oficinas SET abreviatura = 'AUDT' WHERE nombre LIKE '%AUDITORIA%';
UPDATE oficinas SET abreviatura = 'DIRE' WHERE nombre LIKE '%DIRECCION%';

-- Para oficinas sin match, asignar manualmente
-- UPDATE oficinas SET abreviatura = 'XXXX' WHERE id = [ID];
```

**Si hojas_ruta NO tiene sin_limite:**

```sql
ALTER TABLE hojas_ruta
ADD COLUMN sin_limite TINYINT(1) DEFAULT 0 AFTER fecha_limite;
```

**Si documentos_oficiales NO tiene 'respondido_retraso':**

```sql
ALTER TABLE documentos_oficiales
MODIFY COLUMN estado ENUM('delegado','en_progreso','completado','respondido_retraso','archivado','conocimiento') DEFAULT 'delegado';
```

**RESULTADO:**
- ✅ Base de datos completamente actualizada

---

### 📝 FASE 3: ACTUALIZACIÓN DE ARCHIVOS PHP (20 minutos)

#### PASO 4: Actualizar CalendarioModel.php

**Ubicación:** `C:\wamp64\www\gestion\Models\CalendarioModel.php`

**Acción:** Reemplazar el método `contarPendientes()`

1. Abre el archivo con tu editor de código (VS Code, Notepad++, Sublime, etc.)
2. Busca la línea que dice `public function contarPendientes`
3. Reemplaza todo el método (desde `public function` hasta el cierre `}`) con:

```php
// Contar documentos pendientes (delegados + conocimiento)
public function contarPendientes($id_usuario, $rol = null, $id_oficina = null)
{
    // Si no se pasa rol, obtenerlo del usuario
    if ($rol === null) {
        $sqlUsuario = "SELECT rol, id_oficina FROM usuarios WHERE id = $id_usuario";
        $usuario = $this->select($sqlUsuario);
        $rol = $usuario['rol'];
        $id_oficina = $usuario['id_oficina'];
    }

    if ($rol == 1) {
        // ADMIN: Ve TODOS los documentos delegados
        $sql = "SELECT COUNT(*) as total
                FROM documentos_oficiales
                WHERE estado = 'delegado'";
    } else {
        // USUARIO: Ve delegados de su oficina + documentos para conocimiento
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

**Guardar el archivo** (Ctrl+S)

---

#### PASO 5: Actualizar Calendario.php

**Ubicación:** `C:\wamp64\www\gestion\Controllers\Calendario.php`

**Acción:** Reemplazar el método `completarTarea()`

1. Abre el archivo con tu editor
2. Busca `public function completarTarea()`
3. Reemplaza TODO el método completo con este código:

```php
// Completar tarea
public function completarTarea()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_documento = $_POST['id_documento'];
        $comentarios = $_POST['comentarios'] ?? '';
        $archivar = $_POST['archivar'] ?? '0';

        // Validar que exista el documento
        $documento = $this->model->getDocumento($id_documento);

        if (empty($documento)) {
            $res = array('tipo' => 'error', 'mensaje' => 'Documento no encontrado');
            echo json_encode($res);
            die();
        }

        // Validar que se subió archivo
        if (!isset($_FILES['archivo_respuesta']) || $_FILES['archivo_respuesta']['error'] !== UPLOAD_ERR_OK) {
            $res = array('tipo' => 'error', 'mensaje' => 'Debe adjuntar el archivo de respuesta');
            echo json_encode($res);
            die();
        }

        $archivo = $_FILES['archivo_respuesta'];

        // Validar tipo de archivo (PDF)
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'pdf') {
            $res = array('tipo' => 'error', 'mensaje' => 'El archivo debe ser PDF');
            echo json_encode($res);
            die();
        }

        // ========================================
        // CORRECCIÓN 1: DETECTAR SI HAY RETRASO
        // ========================================
        $enRetraso = false;
        if (!empty($documento['fecha_limite']) && $documento['sin_limite'] == 0) {
            $fechaLimite = strtotime($documento['fecha_limite']);
            $ahora = time();
            $enRetraso = ($ahora > $fechaLimite);
        }

        // ========================================
        // CORRECCIÓN 2: GENERAR NOMBRE CORRECTO
        // Formato: RES-Asunto_Limpio-ABREV-001.pdf
        // ========================================
        // Obtener datos de oficina y usuario
        $sqlUsuario = "SELECT id_oficina FROM usuarios WHERE id = {$this->id_usuario}";
        $usuario = $this->model->select($sqlUsuario);

        $sqlOficina = "SELECT abreviatura FROM oficinas WHERE id = {$usuario['id_oficina']}";
        $oficina = $this->model->select($sqlOficina);
        $abrev = $oficina['abreviatura'] ?? 'SIN';

        // Limpiar asunto (quitar caracteres especiales, máximo 30 caracteres)
        $asuntoLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $documento['asunto']);
        $asuntoLimpio = substr($asuntoLimpio, 0, 30);

        // Generar número correlativo por oficina
        $sqlCount = "SELECT COUNT(*) as total FROM documentos_respondidos
                     WHERE id_oficina_respondio = {$usuario['id_oficina']}";
        $count = $this->model->select($sqlCount);
        $numero = str_pad(($count['total'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        $nombreRespuesta = "RES-{$asuntoLimpio}-{$abrev}-{$numero}.pdf";

        // Rutas
        $rutaEnProceso = 'Assets/documentos_oficiales/en_proceso/';
        $rutaCompletados = 'Assets/documentos_oficiales/completados/';

        // Crear carpeta completados si no existe
        if (!file_exists($rutaCompletados)) {
            mkdir($rutaCompletados, 0777, true);
        }

        // 1. Mover archivo original de en_proceso a completados
        $archivoOriginal = $rutaEnProceso . 'HR-' . str_pad($documento['numero_documento'], 3, '0', STR_PAD_LEFT) . '.pdf';
        $archivoDestino = $rutaCompletados . 'HR-' . str_pad($documento['numero_documento'], 3, '0', STR_PAD_LEFT) . '.pdf';

        if (file_exists($archivoOriginal)) {
            if (!rename($archivoOriginal, $archivoDestino)) {
                // Si falla el rename, continuar (puede que ya esté movido)
            }
        }

        // 2. Guardar archivo de respuesta en completados
        $rutaRespuesta = $rutaCompletados . $nombreRespuesta;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaRespuesta)) {
            $res = array('tipo' => 'error', 'mensaje' => 'Error al guardar archivo de respuesta');
            echo json_encode($res);
            die();
        }

        // 3. Si se archiva, copiar a carpeta "Respondidos" del usuario
        if ($archivar == '1') {
            $id_carpeta_respondidos = $this->model->obtenerCarpetaRespondidos($this->id_usuario);

            if ($id_carpeta_respondidos) {
                // Ruta de la carpeta en Adm. de Archivos
                $rutaArchivosCarpeta = 'Assets/archivos/' . $id_carpeta_respondidos . '/';

                // Crear carpeta si no existe
                if (!file_exists($rutaArchivosCarpeta)) {
                    mkdir($rutaArchivosCarpeta, 0777, true);
                }

                // Copiar archivo de respuesta
                copy($rutaRespuesta, $rutaArchivosCarpeta . $nombreRespuesta);

                // Registrar en la tabla archivos
                $this->model->registrarArchivoRespondido($id_carpeta_respondidos, $nombreRespuesta, $this->id_usuario);
            }
        }

        // ========================================
        // CORRECCIÓN 3: ESTADO SEGÚN RETRASO
        // ========================================
        $estado_final = $enRetraso ? 'respondido_retraso' : 'completado';

        // 4. Actualizar estado en documentos_oficiales
        $data = $this->model->completarTarea($id_documento, $nombreRespuesta, $comentarios, $this->id_usuario, $estado_final);

        if ($data != 1) {
            $res = array('tipo' => 'error', 'mensaje' => 'Error al actualizar el estado');
            echo json_encode($res);
            die();
        }

        // ========================================
        // CORRECCIÓN 4: REGISTRAR EN documentos_respondidos
        // ========================================
        $sqlInsertResp = "INSERT INTO documentos_respondidos
                          (id_documento, archivo_respuesta, id_oficina_respondio,
                           id_usuario_respondio, fecha_respuesta, observaciones)
                          VALUES (?, ?, ?, ?, NOW(), ?)";

        $datosResp = array(
            $id_documento,
            $nombreRespuesta,
            $usuario['id_oficina'],
            $this->id_usuario,
            $comentarios
        );

        $this->model->insertar($sqlInsertResp, $datosResp);

        // ========================================
        // CORRECCIÓN 5: ACTUALIZAR hojas_ruta SI SE ARCHIVA
        // ========================================
        if ($archivar == '1') {
            $sqlUpdateHR = "UPDATE hojas_ruta
                            SET estado = 'archivado', fecha_completado = NOW()
                            WHERE numero_registro = ?";
            $this->model->save($sqlUpdateHR, array($documento['numero_documento']));
        }

        // Respuesta exitosa
        $mensaje = $archivar == '1'
            ? 'Tarea completada y archivada en Respondidos'
            : 'Tarea completada exitosamente';

        if ($enRetraso) {
            $mensaje .= ' (Registrada con retraso)';
        }

        $res = array('tipo' => 'success', 'mensaje' => $mensaje);
        echo json_encode($res);
        die();
    }
}
```

**Guardar el archivo** (Ctrl+S)

---

### 🗂️ FASE 4: VERIFICAR DIRECTORIOS FÍSICOS (5 minutos)

#### PASO 6: Verificar Estructura de Carpetas

**En PowerShell:**

```powershell
cd C:\wamp64\www\gestion

# Verificar que existen los directorios
dir Assets\documentos_oficiales -Recurse
dir Assets\documentos_para_conocimiento
dir Assets\archivos
```

**RESULTADO ESPERADO:**
```
Assets/
├── documentos_oficiales/
│   ├── en_proceso/
│   ├── completados/
│   └── archivado/
├── documentos_para_conocimiento/
└── archivos/
```

**Si alguna carpeta NO existe, crearla:**

```powershell
mkdir -Force Assets\documentos_oficiales\en_proceso
mkdir -Force Assets\documentos_oficiales\completados
mkdir -Force Assets\documentos_oficiales\archivado
mkdir -Force Assets\documentos_para_conocimiento
mkdir -Force Assets\archivos
```

---

### 🧪 FASE 5: PRUEBAS FUNCIONALES (20 minutos)

#### PASO 7: Iniciar WAMP y Acceder al Sistema

1. **Iniciar WAMP**
   - Doble click en el icono de WAMP
   - Esperar a que el icono se ponga VERDE
   - Si está naranja, verificar que Apache y MySQL estén corriendo

2. **Acceder al sistema**
   - Abrir navegador (Chrome/Firefox)
   - Ir a: `http://localhost/gestion`
   - Iniciar sesión con tus credenciales

#### PASO 8: Probar Flujo Completo como ADMIN

**8.1. Crear Hoja de Ruta**

1. Ir a **Hojas de Ruta** en el menú lateral
2. Click en **"+ Nueva Hoja de Ruta"**
3. Llenar el formulario:
   - **Número de Registro:** 001
   - **Asunto:** PRUEBA - Informe Mensual de Actividades
   - **Oficina Destino:** Seleccionar una oficina (ej: CAPACITACION)
   - **Fecha Límite:** Elegir fecha de MAÑANA
   - **Prioridad:** Alta
   - **Tipo:** Tarea Delegada
4. Adjuntar un PDF de prueba
5. Click en **"Guardar"**

**VERIFICACIÓN:**
- ✅ Debe aparecer mensaje: "Hoja de Ruta creada exitosamente"
- ✅ Debe aparecer en la lista de hojas de ruta

**8.2. Verificar Contador del Sidebar**

1. Observar el menú lateral izquierdo
2. Verificar que en **"Calendario"** aparece un badge verde con número **1**

**VERIFICACIÓN:**
- ✅ Badge muestra el número correcto de tareas pendientes

#### PASO 9: Probar Flujo Completo como USUARIO

**9.1. Cambiar a Usuario de Oficina**

1. Cerrar sesión (icono de avatar → Salir)
2. Iniciar sesión con usuario de la oficina que seleccionaste
   - Si no tienes usuario de oficina, créalo primero en **Usuarios**
   - **Rol:** Usuario (no Admin)
   - **Oficina:** La misma que seleccionaste en la hoja de ruta

**9.2. Ver Tarea Pendiente**

1. Ir a **Calendario** en el menú
2. Verificar que aparece la tarea creada
3. Click en la tarea para verla

**VERIFICACIÓN:**
- ✅ La tarea aparece en el calendario
- ✅ Badge del sidebar muestra **1**
- ✅ Al hacer click, se abre el detalle

**9.3. Responder Tarea (A TIEMPO)**

1. En el detalle de la tarea, click en **"Completar"**
2. Seleccionar checkbox **"Solo Completar"** (sin archivar)
3. Escribir observaciones: "Tarea completada a tiempo"
4. Adjuntar un PDF de respuesta
5. Click en **"Enviar Respuesta"**

**VERIFICACIÓN:**
- ✅ Mensaje: "Tarea completada exitosamente"
- ✅ Badge del sidebar ahora muestra **0**
- ✅ La tarea desaparece del calendario

**9.4. Verificar Nombre del Archivo**

En PowerShell:
```powershell
dir C:\wamp64\www\gestion\Assets\documentos_oficiales\completados\RES-*.pdf
```

**VERIFICACIÓN:**
- ✅ Debe existir un archivo con formato: `RES-PRUEBA_Informe_Mensual_de_A-CAPA-001.pdf`
- ✅ Nombre contiene: RES + Asunto limpio + Abreviatura + Número

#### PASO 10: Probar Detección de RETRASO

**10.1. Crear otra Hoja de Ruta (como Admin)**

1. Cambiar a usuario Admin
2. Crear nueva hoja de ruta:
   - **Asunto:** PRUEBA - Reporte Urgente
   - **Fecha Límite:** AYER o hace 2 días (fecha pasada)
   - **Oficina:** Misma oficina
3. Guardar

**10.2. Responder con Retraso (como Usuario)**

1. Cambiar a usuario de oficina
2. Ir a **Calendario**
3. Ver la tarea con fecha vencida
4. Completar la tarea
5. Adjuntar PDF y enviar

**VERIFICACIÓN:**
- ✅ Mensaje: "Tarea completada exitosamente **(Registrada con retraso)**"
- ✅ En phpMyAdmin → `documentos_oficiales` → la tarea tiene estado: `'respondido_retraso'`

#### PASO 11: Verificar Reportes

**Como Admin:**

1. Ir a **Reportes** en el menú
2. Click en **"Ver Productividad"**
3. Buscar el usuario que respondió las tareas

**VERIFICACIÓN:**
- ✅ Aparece el usuario en la tabla
- ✅ **"Respondido por Usuario"** muestra: 2 (las 2 tareas)
- ✅ Click en el botón **"2"** abre modal con detalle
- ✅ En el modal aparecen las 2 respuestas:
  - Primera: "A TIEMPO"
  - Segunda: "RETRASO" (en rojo)

**En phpMyAdmin:**
```sql
-- Verificar registros en tabla histórica
SELECT * FROM documentos_respondidos ORDER BY fecha_respuesta DESC LIMIT 5;
```

**VERIFICACIÓN:**
- ✅ Aparecen las 2 respuestas registradas
- ✅ Columnas tienen datos correctos:
  - `archivo_respuesta`: RES-[asunto]-[abrev]-001.pdf
  - `id_oficina_respondio`: ID de la oficina
  - `id_usuario_respondio`: ID del usuario
  - `fecha_respuesta`: Fecha y hora de respuesta

---

## ✅ CHECKLIST FINAL DE VERIFICACIÓN

Marca cada punto después de probarlo:

### Base de Datos
- [ ] Tabla `documentos_respondidos` existe
- [ ] Campo `abreviatura` en `oficinas` existe
- [ ] Las 15 oficinas tienen abreviaturas de 4 caracteres
- [ ] Campo `sin_limite` en `hojas_ruta` existe
- [ ] Estado `'respondido_retraso'` está en ENUM de `documentos_oficiales`

### Archivos PHP
- [ ] `CalendarioModel.php` actualizado (método contarPendientes)
- [ ] `Calendario.php` actualizado (método completarTarea)

### Directorios
- [ ] Existe `Assets/documentos_oficiales/en_proceso/`
- [ ] Existe `Assets/documentos_oficiales/completados/`
- [ ] Existe `Assets/documentos_oficiales/archivado/`
- [ ] Existe `Assets/documentos_para_conocimiento/`
- [ ] Existe `Assets/archivos/`

### Funcionalidad
- [ ] Badge del sidebar cuenta correctamente
- [ ] Se pueden crear hojas de ruta
- [ ] Se pueden responder tareas
- [ ] Archivos tienen formato: RES-Asunto-ABREV-001.pdf
- [ ] Se detectan retrasos correctamente
- [ ] Se registra en `documentos_respondidos`
- [ ] Reportes muestran el histórico
- [ ] Modal de detalle funciona

---

## 🚨 SOLUCIÓN DE PROBLEMAS COMUNES

### Problema 1: Badge del Sidebar Muestra 0

**Causa:** El método `contarPendientes()` no está actualizado

**Solución:**
1. Verificar que `CalendarioModel.php` tenga el método correcto
2. Limpiar caché del navegador (Ctrl+Shift+Del)
3. Refrescar página (F5)

### Problema 2: No se Guarda el Archivo de Respuesta

**Causa:** Permisos de carpetas en Windows

**Solución:**
```powershell
# Dar permisos completos a las carpetas
icacls "C:\wamp64\www\gestion\Assets\documentos_oficiales" /grant Everyone:F /T
```

### Problema 3: Error al Registrar en documentos_respondidos

**Causa:** La tabla no existe o tiene estructura incorrecta

**Solución:**
1. Abrir phpMyAdmin
2. Ejecutar el script de creación de tabla del PASO 3
3. Verificar con: `DESCRIBE documentos_respondidos;`

### Problema 4: No Aparece en Reportes

**Causa:** No se está insertando en `documentos_respondidos`

**Solución:**
1. Verificar que `Calendario.php` tiene las líneas 224-239 con el INSERT
2. Probar responder una tarea nueva
3. Verificar en phpMyAdmin:
```sql
SELECT * FROM documentos_respondidos ORDER BY id DESC LIMIT 5;
```

---

## 🎉 CONFIRMACIÓN FINAL

Si todos los checks están marcados ✅, tu sistema está funcionando correctamente con todas las especificaciones implementadas:

1. ✅ **Contador del Sidebar**: Funciona correctamente
2. ✅ **Detección de Retrasos**: Detecta y marca retrasos
3. ✅ **Nombres de Archivos**: Formato correcto RES-Asunto-ABREV-001.pdf
4. ✅ **Registro Histórico**: Se guarda en documentos_respondidos
5. ✅ **Actualización de Hojas de Ruta**: Se archivan correctamente
6. ✅ **Reportes**: Muestran histórico completo

---

## 📞 CONTACTO Y SOPORTE

Si encuentras algún problema durante la implementación:
1. Verifica el log de errores de PHP en: `C:\wamp64\logs\php_error.log`
2. Verifica el log de Apache en: `C:\wamp64\logs\apache_error.log`
3. Usa F12 en el navegador → Consola para ver errores JavaScript

---

**Fecha de Creación:** 2025-11-02
**Versión:** 1.0
**Sistema:** FELCV Gestión de Documentos
**Autor:** Claude (Anthropic)
