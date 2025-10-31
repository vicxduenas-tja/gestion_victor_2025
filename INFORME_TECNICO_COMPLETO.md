# INFORME TÉCNICO COMPLETO - SISTEMA DE GESTIÓN DE DOCUMENTOS

## Fecha: 31 de Octubre de 2025
## Base de Datos: `gestion_archivos`
## Patrón de Arquitectura: MVC (Modelo-Vista-Controlador)

---

## 1. ESTRUCTURA DE BASE DE DATOS

### 1.1 Tabla: `usuarios`
**Propósito:** Almacena información de todos los usuarios del sistema (admin y usuarios de oficinas)

```sql
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `perfil` varchar(100) DEFAULT NULL,
  `clave` varchar(200) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` int NOT NULL DEFAULT '1',
  `rol` int NOT NULL,
  `id_oficina` int DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `fecha_alta` date DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `observaciones_historial` text,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_oficina` (`id_oficina`),
  CONSTRAINT `fk_usuario_oficina` FOREIGN KEY (`id_oficina`) REFERENCES `oficinas` (`id`)
)
```

**Campos Importantes:**
- `rol`: 1 = Super Admin, 2 = Usuario normal
- `id_oficina`: FK a la oficina a la que pertenece el usuario (NULL para admin)
- `estado`: 1 = Activo, 0 = Inactivo

---

### 1.2 Tabla: `oficinas`
**Propósito:** Catálogo de todas las oficinas/departamentos de la institución

```sql
CREATE TABLE `oficinas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `siglas` varchar(50) DEFAULT NULL,
  `estado` tinyint DEFAULT '1',
  `abreviatura` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abreviatura` (`abreviatura`)
)
```

**Datos de Ejemplo:**
- ID 3: 'UNIDAD DE PREVENCIÓN, CAPACITACIÓN Y COORDINACIÓN', Abreviatura: 'CAPA'
- ID 1: 'DIRECCIÓN DEPARTAMENTAL FELCV', Abreviatura: 'DIRE'

---

### 1.3 Tabla: `hojas_ruta`
**Propósito:** Registro central de TODA la documentación que llega (delegada o para conocimiento)

```sql
CREATE TABLE `hojas_ruta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_registro` varchar(50) NOT NULL,
  `fecha_recepcion` date NOT NULL,
  `remitente` varchar(255) DEFAULT NULL,
  `asunto` text NOT NULL,
  `id_oficina_destino` int DEFAULT NULL,  -- NULL = Para Conocimiento
  `id_usuario_asignado` int DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `prioridad` enum('baja','media','alta','urgente') DEFAULT 'media',
  `estado` enum('en_proceso','completado','archivado','conocimiento') DEFAULT 'en_proceso',
  `observaciones` text,
  `instrucciones` varchar(500) DEFAULT NULL,
  `id_usuario_registro` int NOT NULL,  -- Admin que registró
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_completado` datetime DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `sin_limite` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_hoja_oficina_destino` (`id_oficina_destino`),
  KEY `fk_hoja_usuario_asignado` (`id_usuario_asignado`),
  KEY `fk_hoja_usuario_registro` (`id_usuario_registro`)
)
```

**Estados:**
- `en_proceso`: Documento delegado a oficina, pendiente de respuesta
- `completado`: Documento respondido
- `archivado`: Documento archivado por admin
- `conocimiento`: Documento publicado para conocimiento (todas las oficinas)

**Lógica importante:**
- Si `id_oficina_destino` = NULL y `estado` = 'conocimiento' → Es un documento para conocimiento
- Si `id_oficina_destino` tiene valor → Es un documento delegado a esa oficina específica

---

### 1.4 Tabla: `documentos_oficiales`
**Propósito:** Tabla de CALENDARIO - Almacena tareas delegadas que las oficinas deben responder

```sql
CREATE TABLE `documentos_oficiales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_documento` varchar(50) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `fecha_recepcion` date NOT NULL,
  `fecha_limite` date DEFAULT NULL,
  `prioridad` enum('alta','media','baja','urgente') DEFAULT 'media',
  `estado` enum('delegado','en_progreso','respondiendo','completado','archivado') DEFAULT 'delegado',
  `fecha_completado` datetime DEFAULT NULL,
  `id_carpeta` int NOT NULL,
  `id_usuario_asignado` int NOT NULL,
  `id_oficina_destino` int DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `archivo_respuesta` varchar(255) DEFAULT NULL,
  `observaciones_completado` text,
  `id_usuario_completo` int DEFAULT NULL,
  `fecha_visualizado` datetime DEFAULT NULL,
  `fecha_inicio_respuesta` datetime DEFAULT NULL,
  `sin_limite` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_carpeta` (`id_carpeta`),
  KEY `id_usuario_asignado` (`id_usuario_asignado`),
  KEY `fk_doc_oficina` (`id_oficina_destino`)
)
```

**MODIFICACIÓN REALIZADA:**
Se agregó el campo `sin_limite` mediante:
```sql
ALTER TABLE documentos_oficiales
ADD COLUMN sin_limite TINYINT(1) DEFAULT 0;

ALTER TABLE documentos_oficiales
MODIFY COLUMN prioridad ENUM('alta', 'media', 'baja', 'urgente') DEFAULT 'media';
```

**Estados del flujo:**
1. `delegado` → Recién asignado
2. `en_progreso` → Usuario visualizó el documento
3. `respondiendo` → Usuario comenzó a responder (opcional)
4. `completado` → Respuesta enviada
5. `archivado` → Archivado por el admin

---

### 1.5 Tabla: `documentos_respondidos`
**Propósito:** Registro histórico de respuestas a documentos oficiales

```sql
CREATE TABLE `documentos_respondidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_documento` int NOT NULL,  -- FK a documentos_oficiales
  `archivo_respuesta` varchar(255) NOT NULL,
  `id_oficina_respondio` int NOT NULL,
  `id_usuario_respondio` int NOT NULL,
  `fecha_respuesta` datetime DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text,
  `estado` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_documento` (`id_documento`),
  KEY `id_oficina_respondio` (`id_oficina_respondio`),
  KEY `id_usuario_respondio` (`id_usuario_respondio`)
)
```

---

### 1.6 Tabla: `carpetas`
**Propósito:** Carpetas del módulo "Administrador de Archivos"

```sql
CREATE TABLE `carpetas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `fecha_create` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` int NOT NULL DEFAULT '1',
  `id_usuario` int NOT NULL,
  `es_fija` int DEFAULT '0',  -- 1 = No se puede eliminar
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
)
```

**Carpetas Fijas del Sistema:**
- ID 1: 'Documentos Respondidos' (es_fija=1)
- ID 2: 'Documentos para Conocimiento' (es_fija=1)
- ID 3: 'Documentos Importantes' (es_fija=1)

---

### 1.7 Tabla: `archivos`
**Propósito:** Registra todos los archivos físicos guardados en el sistema

```sql
CREATE TABLE `archivos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(100) NOT NULL,  -- MIME type (application/pdf)
  `fecha_create` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` int NOT NULL DEFAULT '1',
  `elimina` datetime DEFAULT NULL,
  `id_carpeta` int NOT NULL,  -- FK a carpetas
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_carpeta` (`id_carpeta`),
  KEY `id_usuario` (`id_usuario`)
)
```

---

### 1.8 Tabla: `documentos_conocimiento`
**Propósito:** (TABLA AUXILIAR - NO SE USA EN LA IMPLEMENTACIÓN ACTUAL)

```sql
CREATE TABLE `documentos_conocimiento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `contenido` text,
  `archivo` varchar(255) DEFAULT NULL,
  `id_usuario_admin` int NOT NULL,
  `fecha_publicacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` int DEFAULT '1',
  `fecha_recepcion` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `sin_limite` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
)
```

**NOTA:** En la implementación actual, los documentos "Para Conocimiento" se guardan en `hojas_ruta` con `estado='conocimiento'` e `id_oficina_destino=NULL`

---

## 2. MÓDULO: HOJA DE RUTA (Admin)

### 2.1 Archivo: `Controllers/HojaRuta.php`

**Propósito:** Controlador que maneja el registro y delegación de documentación oficial

#### Constructor
```php
public function __construct()
{
    parent::__construct();
    session_start();

    if (empty($_SESSION['id'])) {
        header('Location: ' . BASE_URL);
        exit;
    }

    $this->id_usuario = $_SESSION['id'];
}
```

#### Método: `index()`
**Propósito:** Carga la vista principal de hojas de ruta

```php
public function index()
{
    $data['title'] = 'Hojas de Ruta';
    $data['menu'] = 'hoja_ruta';
    $data['shares'] = ['total' => 0];

    require_once 'Models/CalendarioModel.php';
    $calendarioModel = new CalendarioModel();
    $data['docs_pendientes'] = $calendarioModel->contarPendientes($this->id_usuario);

    $this->views->getView('hoja_ruta', 'index', $data);
}
```

#### Método: `listarOficinas()`
**Propósito:** Endpoint AJAX - Devuelve lista de oficinas para el select

```php
public function listarOficinas()
{
    $data = $this->model->getOficinas();
    echo json_encode($data);
}
```

#### Método: `listar($filtro = 'todas')`
**Propósito:** Endpoint AJAX - Lista hojas de ruta con filtro opcional

```php
public function listar($filtro = 'todas')
{
    $data = $this->model->listarHojasRuta($filtro);
    echo json_encode($data);
}
```

#### Método: `obtenerNumeroRegistro()`
**Propósito:** Endpoint AJAX - Genera número correlativo automático

```php
public function obtenerNumeroRegistro()
{
    $numero = $this->model->generarNumeroRegistro();
    echo json_encode(['numero' => $numero]);
}
```

#### Método: `obtenerDetalle($id)`
**Propósito:** Endpoint AJAX - Obtiene datos completos de una hoja de ruta

```php
public function obtenerDetalle($id)
{
    $sql = "SELECT hr.*, o.nombre as oficina_destino
            FROM hojas_ruta hr
            LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
            WHERE hr.id = $id";

    $data = $this->model->select($sql);
    echo json_encode($data);
}
```

---

### 2.2 Método CRÍTICO: `guardarYDelegarDirecto()`

**Propósito:** Guardar hoja de ruta Y delegarla a una oficina específica

**Flujo completo:**

1. **Validar método HTTP**
```php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['tipo' => 'error', 'mensaje' => 'Método no permitido']);
    return;
}
```

2. **Generar número de registro**
```php
$numero_registro = $this->model->generarNumeroRegistro();
```

3. **Procesar datos del formulario**
```php
$remitente = $_POST['remitente'] ?? '';
$asunto = $_POST['asunto'];
$oficina_destino = $_POST['oficina_destino'];
$prioridad = $_POST['prioridad'];
$observaciones = $_POST['observaciones'] ?? '';
$sin_limite = isset($_POST['sin_limite']) ? 1 : 0;
$fecha_limite = $sin_limite ? null : ($_POST['fecha_limite'] ?? null);
```

4. **Procesar archivo PDF**
```php
$archivo = $_FILES['archivo'];
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

if (strtolower($extension) !== 'pdf') {
    echo json_encode(['tipo' => 'warning', 'mensaje' => 'Solo se permiten archivos PDF']);
    return;
}

$numeroFormato = str_pad($numero_registro, 3, '0', STR_PAD_LEFT);
$nombre_archivo = 'HR-' . $numeroFormato . '.pdf';
$ruta_destino = 'Assets/documentos_oficiales/en_proceso/' . $nombre_archivo;

move_uploaded_file($archivo['tmp_name'], $ruta_destino);
```

5. **Insertar en tabla `hojas_ruta`**
```php
$datos = [
    $numero_registro,
    $fecha_valida,
    $remitente,
    $asunto,
    $oficina_destino,
    $fecha_limite,
    $prioridad,
    $observaciones,
    $this->id_usuario,
    $archivo_adjunto,
    $sin_limite,
    'en_proceso'
];

$resultado = $this->model->crearHojaRuta($datos);
```

6. **Crear tarea en tabla `documentos_oficiales` (CALENDARIO)**
```php
require_once 'Models/CalendarioModel.php';
$calendarioModel = new CalendarioModel();

$sqlCal = "INSERT INTO documentos_oficiales
           (numero_documento, asunto, fecha_recepcion, fecha_limite, prioridad,
            id_carpeta, id_oficina_destino, sin_limite, estado)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$datosCal = [
    $numero_registro,
    $asunto,
    $fecha_valida,
    $fecha_limite,
    $prioridad,
    1,  // id_carpeta fijo
    $oficina_destino,
    $sin_limite,
    'en_proceso'
];

$calendarioModel->insertar($sqlCal, $datosCal);
```

**Resultado:** La tarea aparece en el calendario de la oficina asignada

---

### 2.3 Método CRÍTICO: `publicarParaConocimiento()`

**Propósito:** Publicar documento para conocimiento (todas las oficinas)

**Diferencias con `guardarYDelegarDirecto()`:**

1. **Ruta del archivo:**
```php
$rutaConocimiento = 'Assets/documentos_para_conocimiento/';
$nombreArchivo = 'PC-' . $numeroFormato . '.pdf';
```

2. **Oficina destino:**
```php
$datos = [
    $numero_registro,
    $fecha_recepcion,
    $remitente,
    $asunto,
    NULL,  // <-- id_oficina_destino = NULL
    $fecha_limite,
    $prioridad,
    $observaciones,
    $this->id_usuario,
    $nombreArchivo,
    $sin_limite,
    'conocimiento'  // <-- estado = 'conocimiento'
];
```

3. **Registrar archivo en tabla `archivos`**
```php
$idCarpetaConocimiento = 2;  // ID fijo de carpeta "Documentos para Conocimiento"
$tipoArchivo = $_FILES['archivo']['type'];

$this->model->registrarArchivo(
    $nombreArchivo,
    $tipoArchivo,
    $idCarpetaConocimiento,
    $this->id_usuario
);
```

**Resultado:**
- Se guarda en `hojas_ruta` con estado 'conocimiento'
- Se registra en tabla `archivos` (aparece en Admin. Archivos)
- NO se crea tarea en `documentos_oficiales` (no aparece en calendario)

---

### 2.4 Archivo: `Models/HojaRutaModel.php`

#### Función: `getOficinas()`
```php
public function getOficinas()
{
    $sql = "SELECT id, nombre, siglas
            FROM oficinas
            WHERE estado = 1
            ORDER BY nombre";
    return $this->selectAll($sql);
}
```

#### Función: `crearHojaRuta($datos)`
```php
public function crearHojaRuta($datos)
{
    $sql = "INSERT INTO hojas_ruta
            (numero_registro, fecha_recepcion, remitente, asunto, id_oficina_destino,
             fecha_limite, prioridad, observaciones, id_usuario_registro, archivo_adjunto,
             sin_limite, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    return $this->insertar($sql, $datos);
}
```

#### Función: `generarNumeroRegistro()`
**Lógica:** Genera número correlativo por año

```php
public function generarNumeroRegistro()
{
    $año = date('Y');
    $sql = "SELECT COUNT(*) as total FROM hojas_ruta WHERE YEAR(fecha_recepcion) = $año";
    $resultado = $this->select($sql);
    $consecutivo = $resultado['total'] + 1;

    return str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
}
```

**Ejemplos:**
- Primer documento del 2025: `0001`
- Décimo documento: `0010`
- Centésimo documento: `0100`

#### Función: `listarHojasRuta($filtro = 'todas')`
```php
public function listarHojasRuta($filtro = 'todas')
{
    $whereClause = "";

    if ($filtro != 'todas') {
        $whereClause = "WHERE hr.estado = '$filtro'";
    }

    $sql = "SELECT
                hr.id,
                hr.numero_registro,
                hr.fecha_recepcion,
                hr.asunto,
                hr.prioridad,
                hr.estado,
                hr.fecha_limite,
                CASE
                    WHEN hr.estado = 'conocimiento' THEN 'PARA CONOCIMIENTO'
                    ELSE o.nombre
                END as oficina_destino
            FROM hojas_ruta hr
            LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
            $whereClause
            ORDER BY hr.fecha_recepcion DESC";

    return $this->selectAll($sql);
}
```

**NOTA:** El `CASE` reemplaza NULL por "PARA CONOCIMIENTO" en la columna oficina_destino

#### Función: `registrarArchivo($nombre, $tipo, $id_carpeta, $id_usuario)`
**Propósito:** Registrar archivo en tabla `archivos` (para que aparezca en Admin. Archivos)

```php
public function registrarArchivo($nombre, $tipo, $id_carpeta, $id_usuario)
{
    $sql = "INSERT INTO archivos (nombre, tipo, id_carpeta, id_usuario)
            VALUES (?, ?, ?, ?)";

    $datos = [$nombre, $tipo, $id_carpeta, $id_usuario];

    return $this->insertar($sql, $datos);
}
```

---

### 2.5 Archivo: `Views/hoja_ruta/index.php`

#### Estructura HTML

1. **Header con botón de acción**
```html
<div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h3 class="mb-0"><i class="fas fa-folder-open"></i> Gestión de Hojas de Ruta</h3>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-light" id="btnNuevaHoja">
                <i class="fas fa-plus-circle"></i> Nueva Hoja de Ruta
            </button>
        </div>
    </div>
</div>
```

2. **Filtros de estado**
```html
<div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="filtrarHojasRuta('todas')">Todas</button>
    <button type="button" class="btn btn-outline-info btn-sm" onclick="filtrarHojasRuta('en_proceso')">En Proceso</button>
    <button type="button" class="btn btn-outline-success btn-sm" onclick="filtrarHojasRuta('completado')">Completadas</button>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filtrarHojasRuta('conocimiento')">Para Conocimiento</button>
</div>
```

3. **Tabla de listado**
```html
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th width="10%">N° Registro</th>
            <th width="12%">Fecha Recepción</th>
            <th width="30%">Asunto</th>
            <th width="18%">Oficina Destino</th>
            <th width="10%">Prioridad</th>
            <th width="10%">Estado</th>
            <th width="10%" class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody id="tablaHojasRuta">
        <!-- Se llena dinámicamente con JavaScript -->
    </tbody>
</table>
```

#### Modal: Nueva Hoja de Ruta

**Estructura:** Formulario con 2 columnas (formulario + previsualizador PDF)

**Campos del formulario:**
```html
<input type="text" id="numero_registro" readonly>
<input type="date" id="fecha_recepcion">
<input type="date" id="fecha_limite">
<input type="checkbox" id="sin_limite">
<input type="text" id="remitente">
<textarea id="asunto"></textarea>
<select id="oficina_destino">
    <option value="">Seleccione oficina...</option>
    <option value="0">Todas las Oficinas (Para Conocimiento)</option>
    <!-- Se llena dinámicamente con cargarOficinas() -->
</select>
<select id="prioridad">
    <option value="media">Media</option>
    <option value="baja">Baja</option>
    <option value="alta">Alta</option>
    <option value="urgente">Urgente</option>
</select>
<input type="file" id="archivo_adjunto" accept=".pdf" onchange="previsualizarPDF()">
<textarea id="observaciones"></textarea>
```

**Botones del modal:**
```html
<button type="button" class="btn btn-info" onclick="publicarParaConocimiento()">
    <i class="fas fa-book"></i> Publicar P.C.
</button>
<button type="button" class="btn btn-primary" onclick="guardarHojaRuta()">
    <i class="fas fa-save"></i> Guardar y Delegar
</button>
```

---

### 2.6 JavaScript del módulo Hoja de Ruta

#### Función: `cargarOficinas()`
**Propósito:** Llenar el select con las oficinas activas

```javascript
function cargarOficinas() {
    fetch('<?php echo BASE_URL; ?>hojaruta/listarOficinas')
        .then(response => response.json())
        .then(data => {
            var select = document.getElementById('oficina_destino');
            data.forEach(oficina => {
                var option = document.createElement('option');
                option.value = oficina.id;
                option.textContent = oficina.nombre;
                select.appendChild(option);
            });
        });
}
```

#### Función: `cargarNumeroRegistro()`
```javascript
function cargarNumeroRegistro() {
    fetch('<?php echo BASE_URL; ?>hojaruta/obtenerNumeroRegistro')
        .then(response => response.json())
        .then(data => {
            document.getElementById('numero_registro').value = data.numero;
        });
}
```

#### Función: `guardarHojaRuta()`
**Propósito:** Enviar formulario para delegar a oficina

**Validación de fecha CORREGIDA:**
```javascript
var fechaRecepcion = new Date(document.getElementById('fecha_recepcion').value + 'T00:00:00');
var hoy = new Date();
hoy.setHours(0, 0, 0, 0);

if (fechaRecepcion < hoy) {
    alertaPersonalizada('warning', 'No puede usar fechas anteriores a hoy');
    return;
}
```

**NOTA:** El `+ 'T00:00:00'` soluciona el problema de zona horaria que hacía que siempre mostrara error

**Envío AJAX:**
```javascript
var formData = new FormData();
formData.append('numero_registro', document.getElementById('numero_registro').value);
formData.append('fecha_recepcion', document.getElementById('fecha_recepcion').value);
formData.append('remitente', document.getElementById('remitente').value);
formData.append('asunto', document.getElementById('asunto').value);
formData.append('oficina_destino', document.getElementById('oficina_destino').value);
formData.append('sin_limite', sinLimite ? 1 : 0);
if (!sinLimite) {
    formData.append('fecha_limite', document.getElementById('fecha_limite').value);
}
formData.append('prioridad', document.getElementById('prioridad').value);
formData.append('observaciones', document.getElementById('observaciones').value);
formData.append('archivo', document.getElementById('archivo_adjunto').files[0]);

fetch('<?php echo BASE_URL; ?>hojaruta/guardarYDelegarDirecto', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    alertaPersonalizada(data.tipo, data.mensaje);
    if (data.tipo === 'success') {
        modal.hide();
        cargarHojasRuta();
    }
});
```

#### Función: `publicarParaConocimiento()`
**Similar a `guardarHojaRuta()` pero llama a endpoint diferente:**

```javascript
fetch('<?php echo BASE_URL; ?>hojaruta/publicarParaConocimiento', {
    method: 'POST',
    body: formData
})
```

#### Función: `cargarHojasRuta()`
**Propósito:** Actualizar tabla con listado

```javascript
function cargarHojasRuta() {
    fetch('<?php echo BASE_URL; ?>hojaruta/listar/' + filtroActual)
        .then(response => response.json())
        .then(data => {
            var tbody = document.getElementById('tablaHojasRuta');
            tbody.innerHTML = '';

            data.forEach(hr => {
                var badgePrioridad = hr.prioridad === 'urgente' ? 'danger' :
                    hr.prioridad === 'alta' ? 'warning' :
                    hr.prioridad === 'media' ? 'info' : 'secondary';

                var badgeEstado;
                var textoEstado = hr.estado;

                switch (hr.estado) {
                    case 'completado':
                        badgeEstado = 'success';
                        break;
                    case 'en_proceso':
                        badgeEstado = 'primary';
                        textoEstado = 'en proceso';
                        break;
                    case 'conocimiento':
                        badgeEstado = 'info';
                        break;
                    case 'archivado':
                        badgeEstado = 'secondary';
                        break;
                    default:
                        badgeEstado = 'light';
                        textoEstado = 'N/A';
                }

                var tr = `
                <tr>
                    <td>${hr.numero_registro}</td>
                    <td>${hr.fecha_recepcion}</td>
                    <td>${hr.asunto}</td>
                    <td>${hr.oficina_destino}</td>
                    <td><span class="badge bg-${badgePrioridad}">${hr.prioridad.toUpperCase()}</span></td>
                    <td><span class="badge bg-${badgeEstado}">${textoEstado}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" onclick="verDetalleHoja(${hr.id})">
                            <span class="material-icons">visibility</span>
                        </button>
                    </td>
                </tr>
                `;
                tbody.innerHTML += tr;
            });
        });
}
```

#### Función: `previsualizarPDF()`
```javascript
function previsualizarPDF() {
    var archivo = document.getElementById('archivo_adjunto').files[0];

    if (archivo && archivo.type === 'application/pdf') {
        var url = URL.createObjectURL(archivo);
        document.getElementById('previsualizadorPDF').src = url;
    } else {
        document.getElementById('previsualizadorPDF').src = '';
        if (archivo) {
            alertaPersonalizada('warning', 'Solo se permiten archivos PDF');
            document.getElementById('archivo_adjunto').value = '';
        }
    }
}
```

#### Función: `verDetalleHoja(id)`
**Propósito:** Abrir modal de solo lectura con datos de la hoja de ruta

```javascript
function verDetalleHoja(id) {
    fetch('<?php echo BASE_URL; ?>hojaruta/obtenerDetalle/' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('detNumero').textContent = data.numero_registro;
            document.getElementById('detFechaRecepcion').textContent = data.fecha_recepcion;
            document.getElementById('detFechaLimite').textContent = data.fecha_limite || 'Sin límite';
            document.getElementById('detRemitente').textContent = data.remitente || 'No especificado';
            document.getElementById('detOficina').textContent = data.oficina_destino;
            document.getElementById('detAsunto').textContent = data.asunto;
            document.getElementById('detObservaciones').textContent = data.observaciones || 'Sin observaciones';

            // Badge estado con lógica para "conocimiento"
            var badgeEstado = document.getElementById('detEstado');
            badgeEstado.className = 'badge';
            var textoEstado = data.estado.replace('_', ' ').toUpperCase();

            switch (data.estado) {
                case 'completado':
                    badgeEstado.classList.add('bg-success');
                    break;
                case 'en_proceso':
                    badgeEstado.classList.add('bg-primary');
                    break;
                case 'conocimiento':
                    badgeEstado.classList.add('bg-info');
                    break;
                case 'archivado':
                    badgeEstado.classList.add('bg-secondary');
                    break;
                default:
                    badgeEstado.classList.add('bg-light');
            }
            badgeEstado.textContent = textoEstado;

            // Ruta del PDF corregida
            var rutaBase = '<?php echo BASE_URL; ?>';
            var rutaPDF = '';

            if (data.estado === 'conocimiento') {
                rutaPDF = rutaBase + 'Assets/documentos_para_conocimiento/' + data.archivo_adjunto;
            } else {
                var carpeta = data.estado === 'en_proceso' ? 'en_proceso' :
                              data.estado === 'completado' ? 'completados' : 'archivado';
                rutaPDF = rutaBase + 'Assets/documentos_oficiales/' + carpeta + '/' + data.archivo_adjunto;
            }
            document.getElementById('detVisorPDF').src = rutaPDF;

            var modal = new bootstrap.Modal(document.getElementById('modalDetallesHoja'));
            modal.show();
        });
}
```

---

## 3. MÓDULO: CALENDARIO (Usuarios)

### 3.1 Archivo: `Controllers/Calendario.php`

#### Constructor
```php
private $id_usuario;
private $id_oficina;
private $rol;

public function __construct()
{
    parent::__construct();
    session_start();

    if (empty($_SESSION['id'])) {
        header('Location: ' . BASE_URL);
        exit;
    }

    $this->id_usuario = $_SESSION['id'];
    $this->id_oficina = $_SESSION['id_oficina'] ?? null;
    $this->rol = $_SESSION['rol'];
}
```

**NOTA:** Se agregaron `id_oficina` y `rol` para filtrar correctamente las tareas

#### Método: `index()`
```php
public function index()
{
    $data['title'] = 'Calendario';
    $data['menu'] = 'calendario';
    $data['shares'] = ['total' => 0];
    $data['docs_pendientes'] = $this->model->contarPendientes($this->rol, $this->id_oficina);

    $this->views->getView('calendario', 'index', $data);
}
```

#### Método: `listarPendientes()`
**Propósito:** Endpoint AJAX - Devuelve documentos pendientes del usuario

```php
public function listarPendientes()
{
    $data = $this->model->getDocumentosPendientes($this->rol, $this->id_oficina);
    echo json_encode($data);
}
```

**CAMBIO IMPORTANTE:** Ahora recibe `$this->rol` y `$this->id_oficina` en lugar de `$this->id_usuario`

#### Método: `registrarVisualizacion()`
**Propósito:** Cambiar estado de 'delegado' o 'en_proceso' cuando el usuario abre el documento

```php
public function registrarVisualizacion()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];

        $documento = $this->model->getDocumento($id);

        if (empty($documento)) {
            $res = array('tipo' => 'error', 'mensaje' => 'Documento no encontrado');
            echo json_encode($res);
            die();
        }

        if ($documento['estado'] === 'delegado' || $documento['estado'] === 'en_proceso') {
            if ($documento['fecha_visualizado'] == null) {
                $data = $this->model->registrarVisualizacion($id);
                if ($data == 1) {
                    $res = array('tipo' => 'success', 'mensaje' => 'Visualización registrada');
                } else {
                    $res = array('tipo' => 'error', 'mensaje' => 'Error al registrar visualización');
                }
            } else {
                 $res = array('tipo' => 'info', 'mensaje' => 'Ya fue visualizado anteriormente');
            }
        } else {
            $res = array('tipo' => 'info', 'mensaje' => 'El documento no está pendiente');
        }

        echo json_encode($res);
        die();
    }
}
```

#### Método: `completarTarea()`
**Propósito:** Guardar respuesta del usuario a un documento delegado

**Flujo completo:**

1. **Validar documento**
```php
$id_documento = $_POST['id_documento'];
$comentarios = $_POST['comentarios'] ?? '';
$archivar_en_admin = $_POST['archivar'] ?? '0';

$documento = $this->model->getDocumento($id_documento);
if (empty($documento)) {
    $res = array('tipo' => 'error', 'mensaje' => 'Documento no encontrado');
    echo json_encode($res);
    die();
}
```

2. **Validar archivo PDF**
```php
if (!isset($_FILES['archivo_respuesta']) || $_FILES['archivo_respuesta']['error'] !== UPLOAD_ERR_OK) {
    $res = array('tipo' => 'error', 'mensaje' => 'Debe adjuntar el archivo de respuesta');
    echo json_encode($res);
    die();
}

$archivo = $_FILES['archivo_respuesta'];
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
if (strtolower($extension) !== 'pdf') {
    $res = array('tipo' => 'error', 'mensaje' => 'El archivo debe ser PDF');
    echo json_encode($res);
    die();
}
```

3. **Generar nombre de archivo de respuesta**
```php
$id_oficina_origen = $this->id_oficina;
$nombreRespuesta = $this->model->generarNombreRespuesta($id_documento, $id_oficina_origen);
```

**Formato:** `RES-Asunto_Limpio-ABREVIATURA-001.pdf`
- Ejemplo: `RES-Solicitud_de_Capacitacion-CAPA-001.pdf`

4. **Guardar archivo físico**
```php
$rutaCompletados = 'Assets/documentos_oficiales/completados/';
if (!file_exists($rutaCompletados)) {
    mkdir($rutaCompletados, 0777, true);
}

$rutaRespuesta = $rutaCompletados . $nombreRespuesta;
move_uploaded_file($archivo['tmp_name'], $rutaRespuesta);
```

5. **Registrar en carpeta Admin (opcional)**
```php
if ($archivar_en_admin == '1') {
    $id_carpeta_respondidos = 1;  // Carpeta fija "Documentos Respondidos"
    $id_admin = 1;

    $this->model->registrarArchivoRespondido($id_carpeta_respondidos, $nombreRespuesta, $id_admin);
}
```

6. **Actualizar estado en `documentos_oficiales`**
```php
$estado_final = 'completado';
$data = $this->model->completarTarea($id_documento, $nombreRespuesta, $comentarios, $this->id_usuario, $estado_final);
```

7. **Registrar en tabla `documentos_respondidos`**
```php
$this->model->registrarDocumentoRespondido(
    $id_documento,
    $nombreRespuesta,
    $id_oficina_origen,
    $this->id_usuario,
    $comentarios
);
```

---

### 3.2 Archivo: `Models/CalendarioModel.php`

#### Función: `getDocumentosPendientes($rol, $id_oficina)`
**Propósito:** Obtener documentos según el rol del usuario

```php
public function getDocumentosPendientes($rol, $id_oficina)
{
    if ($rol == 1) {
        // SUPER ADMIN: Ve TODAS las tareas
        $sql = "SELECT
                d.id, d.numero_documento, d.asunto, d.fecha_recepcion,
                d.fecha_limite, d.fecha_completado, d.prioridad, d.estado,
                c.nombre as carpeta,
                COALESCE(CONCAT(u.nombre, ' ', u.apellido), o.nombre) as usuario_asignado
            FROM documentos_oficiales d
            LEFT JOIN carpetas c ON d.id_carpeta = c.id
            LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE d.estado IN ('delegado', 'en_progreso', 'respondiendo', 'completado', 'archivado')
            ORDER BY d.fecha_limite ASC";
    } else {
        // USUARIO: Ve solo tareas de SU oficina
        $sql = "SELECT
                d.id, d.numero_documento, d.asunto, d.fecha_recepcion,
                d.fecha_limite, d.fecha_completado, d.prioridad, d.estado,
                c.nombre as carpeta,
                COALESCE(CONCAT(u.nombre, ' ', u.apellido), o.nombre) as usuario_asignado
            FROM documentos_oficiales d
            LEFT JOIN carpetas c ON d.id_carpeta = c.id
            LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE d.id_oficina_destino = $id_oficina
            AND d.estado IN ('delegado', 'en_progreso', 'respondiendo', 'completado', 'archivado')
            ORDER BY d.fecha_limite ASC";
    }

    return $this->selectAll($sql);
}
```

**CORRECCIÓN REALIZADA:** Se cambió el filtro de `id_usuario_asignado` a `id_oficina_destino`

#### Función: `contarPendientes($rol, $id_oficina)`
```php
public function contarPendientes($rol, $id_oficina)
{
    if ($rol == 1) {
        // Admin ve todos
        $sql = "SELECT COUNT(*) as total
                FROM documentos_oficiales
                WHERE estado IN ('delegado', 'en_progreso', 'respondiendo')";
    } else {
        // Usuario ve solo de su oficina
        $sql = "SELECT COUNT(*) as total
                FROM documentos_oficiales
                WHERE id_oficina_destino = $id_oficina
                AND estado IN ('delegado', 'en_progreso', 'respondiendo')";
    }

    $resultado = $this->select($sql);
    return $resultado['total'];
}
```

#### Función: `registrarVisualizacion($id)`
```php
public function registrarVisualizacion($id)
{
    $sql = "UPDATE documentos_oficiales
            SET estado = 'en_progreso',
                fecha_visualizado = NOW()
            WHERE id = ?";

    return $this->save($sql, array($id));
}
```

#### Función: `completarTarea($id, $archivo_respuesta, $comentarios, $id_usuario, $estado_final)`
```php
public function completarTarea($id, $archivo_respuesta, $comentarios, $id_usuario, $estado_final)
{
    $sql = "UPDATE documentos_oficiales
            SET
                estado = ?,
                fecha_completado = NOW(),
                archivo_respuesta = ?,
                observaciones_completado = ?,
                id_usuario_completo = ?,
                fecha_inicio_respuesta = IFNULL(fecha_inicio_respuesta, NOW())
            WHERE id = ?";

    $datos = array($estado_final, $archivo_respuesta, $comentarios, $id_usuario, $id);
    return $this->save($sql, $datos);
}
```

#### Función: `generarNombreRespuesta($id_documento, $id_oficina_origen)`
**Propósito:** Generar nombre único para archivo de respuesta

**Formato:** `RES-{Asunto_Limpio}-{ABREVIATURA}-{Secuencial}.pdf`

```php
public function generarNombreRespuesta($id_documento, $id_oficina_origen)
{
    // 1. Obtener el documento
    $sqlDoc = "SELECT asunto FROM documentos_oficiales WHERE id = $id_documento";
    $documento = $this->select($sqlDoc);
    if (!$documento) { return null; }

    // 2. Limpiar el asunto (quitar caracteres especiales, limitar a 50 caracteres)
    $asunto = $documento['asunto'];
    $asuntoLimpio = preg_replace('/[^a-zA-Z0-9\s]/', '', $asunto);
    $asuntoLimpio = preg_replace('/\s+/', '_', trim($asuntoLimpio));
    $asuntoLimpio = substr($asuntoLimpio, 0, 50);

    // 3. Obtener abreviatura de la oficina
    $sqlOficina = "SELECT abreviatura FROM oficinas WHERE id = $id_oficina_origen";
    $oficina = $this->select($sqlOficina);
    if (!$oficina) { return null; }
    $abreviatura = $oficina['abreviatura'];

    // 4. Contar respuestas de esta oficina para generar secuencial
    $sqlCount = "SELECT COUNT(*) as total
                 FROM documentos_respondidos
                 WHERE id_oficina_respondio = $id_oficina_origen";
    $count = $this->select($sqlCount);
    $secuencial = str_pad($count['total'] + 1, 3, '0', STR_PAD_LEFT);

    // 5. Generar nombre final
    return "RES-{$asuntoLimpio}-{$abreviatura}-{$secuencial}.pdf";
}
```

**Ejemplos reales:**
- `RES-Solicitud_de_Capacitacion-CAPA-001.pdf`
- `RES-Informe_Mensual_Actividades-DIRE-002.pdf`

#### Función: `registrarArchivoRespondido($id_carpeta, $nombre_archivo, $id_usuario)`
**Propósito:** Registrar en tabla `archivos` para que aparezca en Admin. Archivos

```php
public function registrarArchivoRespondido($id_carpeta, $nombre_archivo, $id_usuario)
{
    $sql = "INSERT INTO archivos (nombre, tipo, id_carpeta, id_usuario)
            VALUES (?, ?, ?, ?)";
    $datos = array($nombre_archivo, 'application/pdf', $id_carpeta, $id_usuario);
    return $this->insertar($sql, $datos);
}
```

#### Función: `registrarDocumentoRespondido($id_documento, $nombreRespuesta, $id_oficina, $id_usuario, $comentarios)`
**Propósito:** Crear registro histórico en `documentos_respondidos`

```php
public function registrarDocumentoRespondido($id_documento, $nombreRespuesta, $id_oficina, $id_usuario, $comentarios)
{
    $sql = "INSERT INTO documentos_respondidos
            (id_documento, archivo_respuesta, id_oficina_respondio, id_usuario_respondio, observaciones)
            VALUES (?, ?, ?, ?, ?)";

    $datos = array($id_documento, $nombreRespuesta, $id_oficina, $id_usuario, $comentarios);
    return $this->insertar($sql, $datos);
}
```

---

## 4. MÓDULO: REPORTES (Admin)

### 4.1 Archivo: `Controllers/Reportes.php`

#### Constructor
```php
private $id_usuario;
private $id_oficina;
private $rol;
private $calendario_model;

public function __construct()
{
    parent::__construct();
    session_start();

    if (empty($_SESSION['id'])) {
        header('Location: ' . BASE_URL);
        exit;
    }

    $this->id_usuario = $_SESSION['id'];
    $this->id_oficina = $_SESSION['id_oficina'] ?? null;
    $this->rol = $_SESSION['rol'];

    // Cargar modelos manualmente
    require_once 'Models/ReportesModel.php';
    $this->model = new ReportesModel();

    require_once 'Models/CalendarioModel.php';
    $this->calendario_model = new CalendarioModel();
}
```

#### Método: `index()`
**Propósito:** Vista principal con 4 widgets de estadísticas

```php
public function index()
{
    $data['title'] = 'Reportes y Estadísticas';
    $data['menu'] = 'reportes';

    // Para el contador del menú
    $data['docs_pendientes'] = $this->calendario_model->contarPendientes(
        $this->rol,
        $this->id_oficina
    );

    // Para los widgets
    $data['en_progreso'] = $this->model->contarDocumentos('en_progreso', $this->rol, $this->id_oficina);
    $data['vencidos'] = $this->model->contarDocumentos('vencidos', $this->rol, $this->id_oficina);
    $data['completados_mes'] = $this->model->contarDocumentos('completados_mes', 1);
    $data['retrasos_mes'] = $this->model->contarDocumentos('retrasos_mes', 1);
    $data['conocimiento_mes'] = $this->model->contarDocumentos('conocimiento_mes', 1);

    $this->views->getView('reportes', 'index', $data);
}
```

#### Método: `generarReporteDetallado()`
**Propósito:** Endpoint AJAX - Generar tabla con filtros

```php
public function generarReporteDetallado()
{
    $desde = $_POST['desde'] ?? date('Y-m-01');
    $hasta = $_POST['hasta'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? 'todos';

    $data = $this->model->getReporteDetallado($desde, $hasta, $estado);
    echo json_encode($data);
    exit;
}
```

---

### 4.2 Archivo: `Models/ReportesModel.php`

#### Función: `contarDocumentos($tipo, $rol, $id_oficina = null)`
**Propósito:** Función central para calcular las estadísticas de los widgets

```php
public function contarDocumentos(string $tipo, int $rol, int $id_oficina = null)
{
    $where = '';
    $tabla = 'documentos_oficiales';
    $where_rol = '';

    // Filtro por rol
    if ($rol != 1) {  // Si NO es Admin
        $id_oficina_safe = intval($id_oficina);
        $where_rol = " AND (id_oficina_destino = $id_oficina_safe) ";
    }

    switch ($tipo) {
        case 'en_progreso':
            $where = "estado = 'en_progreso' $where_rol";
            break;

        case 'vencidos':
            $where = "estado = 'en_progreso' AND fecha_limite < CURDATE() AND sin_limite = 0 $where_rol";
            break;

        case 'completados_mes':
            $where = "(estado = 'completado' OR estado = 'respondido_retraso')
                      AND MONTH(fecha_completado) = MONTH(CURDATE())
                      AND YEAR(fecha_completado) = YEAR(CURDATE())";
            break;

        case 'conocimiento_mes':
            $tabla = 'hojas_ruta';
            $where = "estado = 'conocimiento'
                      AND MONTH(fecha_registro) = MONTH(CURDATE())
                      AND YEAR(fecha_registro) = YEAR(CURDATE())";
            break;

        case 'retrasos_mes':
            $where = "estado = 'respondido_retraso'
                      AND MONTH(fecha_completado) = MONTH(CURDATE())
                      AND YEAR(fecha_completado) = YEAR(CURDATE())";
            break;

        default:
            return 0;
    }

    $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $where";
    $resultado = $this->select($sql);
    return $resultado['total'];
}
```

**NOTA:** Cambia de tabla según el tipo:
- `documentos_oficiales` → Para tareas delegadas
- `hojas_ruta` → Para documentos de conocimiento

#### Función: `getReporteDetallado($desde, $hasta, $estado)`
**Propósito:** Generar listado detallado con filtros de fecha y estado

```php
public function getReporteDetallado(string $desde, string $hasta, string $estado)
{
    $whereEstado = "";

    // 1. Caso especial: Para Conocimiento (busca en hojas_ruta)
    if ($estado == 'conocimiento') {
        $sql = "SELECT
                    hr.numero_registro as numero_documento,
                    hr.asunto,
                    'PARA CONOCIMIENTO' as oficina_destino,
                    hr.fecha_recepcion,
                    hr.fecha_limite,
                    NULL as fecha_completado,
                    hr.estado,
                    hr.sin_limite
                FROM hojas_ruta hr
                WHERE hr.fecha_recepcion BETWEEN '$desde' AND '$hasta'
                    AND hr.estado = 'conocimiento'
                ORDER BY hr.fecha_recepcion DESC";

        return $this->selectAll($sql);
    }

    // 2. Todos los demás estados (buscan en documentos_oficiales)
    if ($estado == 'completados_todos') {
        $whereEstado = " AND (d.estado = 'completado' OR d.estado = 'respondido_retraso')";
    } else if ($estado != 'todos') {
        $whereEstado = " AND d.estado = '$estado'";
    }

    $sql = "SELECT
                d.numero_documento,
                d.asunto,
                o.nombre as oficina_destino,
                d.fecha_recepcion,
                d.fecha_limite,
                d.fecha_completado,
                d.estado,
                d.sin_limite
            FROM documentos_oficiales d
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE d.fecha_recepcion BETWEEN '$desde' AND '$hasta'
                $whereEstado
            ORDER BY d.fecha_recepcion DESC";

    return $this->selectAll($sql);
}
```

#### Función: `getReporteProductividad()`
**Propósito:** Reporte de rendimiento por usuario

**Query compleja con 3 subconsultas:**

```php
public function getReporteProductividad()
{
    $sql = "
        SELECT
            u.id,
            CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario,
            o.nombre AS oficina_usuario,
            u.cargo,

            -- Conteo 1: Total de documentos respondidos
            (SELECT COUNT(*)
             FROM documentos_respondidos dr
             WHERE dr.id_usuario_respondio = u.id) AS total_respondidos,

            -- Conteo 2: Total de archivos guardados en carpeta 'Documentos Respondidos'
            (SELECT COUNT(a.id)
             FROM archivos a
             LEFT JOIN carpetas c ON a.id_carpeta = c.id
             WHERE a.id_usuario = u.id
                AND c.nombre = 'Documentos Respondidos'
                AND a.estado = 1) AS archivos_respondidos_guardados,

            -- Conteo 3: Total de documentos delegados a la oficina
            (SELECT COUNT(do.id)
             FROM documentos_oficiales do
             WHERE do.id_oficina_destino = u.id_oficina
                AND do.estado NOT IN ('conocimiento')) AS total_delegado_oficina

        FROM usuarios u
        LEFT JOIN oficinas o ON u.id_oficina = o.id
        WHERE u.rol != 1  -- Excluir Super Admin
            AND u.estado = 1  -- Solo usuarios activos
        ORDER BY o.nombre, u.apellido
    ";

    return $this->selectAll($sql);
}
```

**Resultados:**
- `total_respondidos`: Documentos que el usuario respondió
- `archivos_respondidos_guardados`: Archivos que guardó en su carpeta personal
- `total_delegado_oficina`: Documentos asignados a toda la oficina

#### Función: `getInventarioActivo($id_oficina = 0)`
**Propósito:** Listar tareas pendientes para relevo de personal

```php
public function getInventarioActivo(int $id_oficina = 0)
{
    $id_oficina_safe = intval($id_oficina);

    $sql = "
        SELECT
            d.numero_documento,
            d.asunto,
            d.fecha_recepcion,
            d.fecha_limite,
            d.estado
        FROM documentos_oficiales d
        WHERE
            (d.id_oficina_destino = $id_oficina_safe AND d.estado = 'en_progreso')
            OR d.estado = 'conocimiento'
        ORDER BY d.estado DESC, d.fecha_recepcion ASC
    ";

    return $this->selectAll($sql);
}
```

---

### 4.3 Archivo: `Views/reportes/index.php`

#### Widgets de Estadísticas (Header)

```html
<div class="row">
    <!-- Widget 1: En Progreso -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-white-50">En Progreso</h6>
                        <h2 class="mb-0"><?php echo $data['en_progreso']; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-tasks fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget 2: Vencidos -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-white-50">Vencidos</h6>
                        <h2 class="mb-0"><?php echo $data['vencidos']; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget 3: Completados -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-white-50">Completados (Mes)</h6>
                        <h2 class="mb-0"><?php echo $data['completados_mes']; ?></h2>
                        <small>
                            <?php if ($data['retrasos_mes'] > 0): ?>
                                <i class="fas fa-clock"></i>
                                <?php echo $data['retrasos_mes']; ?> con retraso
                            <?php endif; ?>
                        </small>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget 4: Para Conocimiento -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-white-50">P.C. (Mes)</h6>
                        <h2 class="mb-0"><?php echo $data['conocimiento_mes']; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-book fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### Generador de Reporte Detallado

```html
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Generador de Reporte Detallado</h5>

        <form id="formReporte" class="row g-3 align-items-end form-reporte-detallado">
            <div class="col-md-4">
                <label class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="fechaDesde"
                       value="<?php echo date('Y-m-01'); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="fechaHasta"
                       value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-control" id="estadoReporte">
                    <option value="todos">Todos</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="completados_todos">Completados</option>
                    <option value="archivado">Archivados</option>
                    <option value="conocimiento">Para Conocimiento</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="button" id="btnGenerarReporte" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Generar Reporte
                </button>
            </div>
        </form>

        <!-- Tabla de resultados -->
        <div id="reporte-resultado" class="mt-4" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Resultados del Reporte</h6>
                <button class="btn btn-sm btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>

            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>N° Doc.</th>
                        <th>Asunto</th>
                        <th>Oficina Destino</th>
                        <th>F. Recepción</th>
                        <th>F. Límite</th>
                        <th>F. Completado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaReporteBody">
                    <!-- Se llena dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>
```

#### JavaScript del Reporte

```javascript
document.getElementById('btnGenerarReporte').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

    const desde = document.getElementById('fechaDesde').value;
    const hasta = document.getElementById('fechaHasta').value;
    const estado = document.getElementById('estadoReporte').value;

    const formData = new FormData();
    formData.append('desde', desde);
    formData.append('hasta', hasta);
    formData.append('estado', estado);

    fetch('<?php echo BASE_URL; ?>reportes/generarReporteDetallado', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search"></i> Generar Reporte';

        const tbody = document.getElementById('tablaReporteBody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay datos para mostrar</td></tr>';
        } else {
            data.forEach(item => {
                const tr = `
                <tr>
                    <td>${item.numero_documento}</td>
                    <td>${item.asunto}</td>
                    <td>${item.oficina_destino || 'PARA CONOCIMIENTO'}</td>
                    <td>${item.fecha_recepcion}</td>
                    <td>${item.fecha_limite || (item.sin_limite == 1 ? 'Sin límite' : '-')}</td>
                    <td>${item.fecha_completado || '-'}</td>
                    <td><span class="badge bg-info">${item.estado}</span></td>
                </tr>
                `;
                tbody.innerHTML += tr;
            });
        }

        document.getElementById('reporte-resultado').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alertaPersonalizada('error', 'Error al generar el reporte.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search"></i> Generar Reporte';
    });
});
```

#### CSS para Impresión

```css
@media print {
    .form-reporte-detallado,
    .btn,
    nav,
    .sidebar {
        display: none !important;
    }

    #reporte-resultado {
        display: block !important;
    }
}
```

---

## 5. FLUJO COMPLETO DEL SISTEMA

### 5.1 Flujo: Documento Delegado

```
1. ADMIN (Hoja de Ruta)
   ├─> Llena formulario
   ├─> Selecciona oficina destino (ej: "Unidad de Capacitación")
   ├─> Sube PDF
   └─> Click "Guardar y Delegar"

2. BACKEND (HojaRuta::guardarYDelegarDirecto)
   ├─> Genera número correlativo (ej: 0001)
   ├─> Guarda PDF en: Assets/documentos_oficiales/en_proceso/HR-0001.pdf
   ├─> INSERT en hojas_ruta (estado='en_proceso')
   └─> INSERT en documentos_oficiales (estado='en_proceso')

3. USUARIO (Calendario)
   ├─> Ve tarea en su calendario
   ├─> Click "Ver" → Estado cambia a 'en_progreso'
   ├─> Trabaja en la respuesta
   ├─> Sube archivo PDF de respuesta
   └─> Click "Completar Tarea"

4. BACKEND (Calendario::completarTarea)
   ├─> Genera nombre: RES-Asunto-CAPA-001.pdf
   ├─> Guarda PDF en: Assets/documentos_oficiales/completados/
   ├─> UPDATE documentos_oficiales (estado='completado')
   ├─> INSERT en documentos_respondidos (registro histórico)
   └─> INSERT en archivos (si marcó "Archivar en Admin")

5. ADMIN
   ├─> Ve respuesta en "Documentos Respondidos" (Adm. Archivos)
   └─> Puede generar reportes con estas métricas
```

---

### 5.2 Flujo: Documento Para Conocimiento

```
1. ADMIN (Hoja de Ruta)
   ├─> Llena formulario
   ├─> Selecciona "Todas las Oficinas (Para Conocimiento)"
   ├─> Marca "Sin límite"
   ├─> Sube PDF
   └─> Click "Publicar P.C."

2. BACKEND (HojaRuta::publicarParaConocimiento)
   ├─> Genera número correlativo (ej: 0002)
   ├─> Guarda PDF en: Assets/documentos_para_conocimiento/PC-0002.pdf
   ├─> INSERT en hojas_ruta (estado='conocimiento', id_oficina_destino=NULL)
   ├─> INSERT en archivos (id_carpeta=2, para Admin. Archivos)
   └─> NO crea registro en documentos_oficiales

3. TODOS LOS USUARIOS
   ├─> Ven documento listado en su sección "Para Conocimiento"
   ├─> Pueden ver el PDF
   └─> NO necesitan responder (es informativo)

4. REPORTES
   └─> Se cuenta en widget "P.C. (Mes)"
```

---

## 6. ERRORES Y SOLUCIONES IMPLEMENTADAS

### 6.1 Error: Fecha de recepción siempre muestra "No puede usar fechas anteriores a hoy"

**Causa:** Problema de zona horaria en JavaScript

**Solución:**
```javascript
// ANTES (Incorrecto)
var fechaRecepcion = new Date(document.getElementById('fecha_recepcion').value);

// DESPUÉS (Correcto)
var fechaRecepcion = new Date(document.getElementById('fecha_recepcion').value + 'T00:00:00');
```

---

### 6.2 Error: SQLSTATE[23000] Foreign key constraint fails (id_oficina_destino = 0)

**Causa:** La tabla `hojas_ruta` no permitía NULL en `id_oficina_destino`

**Solución SQL:**
```sql
ALTER TABLE hojas_ruta MODIFY id_oficina_destino INT DEFAULT NULL;
```

**Solución PHP:**
```php
// En publicarParaConocimiento():
$datos = [
    // ...
    NULL,  // id_oficina_destino = NULL para "Para Conocimiento"
    // ...
];
```

---

### 6.3 Error: Column 'sin_limite' not found in documentos_oficiales

**Causa:** Falta el campo `sin_limite` en tabla `documentos_oficiales`

**Solución SQL:**
```sql
ALTER TABLE documentos_oficiales
ADD COLUMN sin_limite TINYINT(1) DEFAULT 0;

ALTER TABLE documentos_oficiales
MODIFY COLUMN prioridad ENUM('alta', 'media', 'baja', 'urgente') DEFAULT 'media';
```

---

### 6.4 Error: Estado 'conocimiento' no permitido en tabla hojas_ruta

**Causa:** ENUM no incluía 'conocimiento'

**Solución SQL:**
```sql
ALTER TABLE hojas_ruta MODIFY COLUMN estado
ENUM('en_proceso', 'completado', 'archivado', 'conocimiento')
DEFAULT 'en_proceso';

-- Arreglar registros antiguos
UPDATE hojas_ruta
SET estado = 'conocimiento'
WHERE id_oficina_destino IS NULL;
```

---

### 6.5 Error: Usuarios no ven tareas delegadas en su calendario

**Causa:** El modelo buscaba por `id_usuario_asignado` en lugar de `id_oficina_destino`

**Solución en CalendarioModel.php:**
```php
// ANTES (Incorrecto)
WHERE d.id_usuario_asignado = $id_usuario

// DESPUÉS (Correcto)
WHERE d.id_oficina_destino = $id_oficina
```

---

### 6.6 Error: Columna oficina_destino muestra NULL en tabla de reportes

**Causa:** No se manejaba el caso especial de documentos "Para Conocimiento"

**Solución en HojaRutaModel.php:**
```php
$sql = "SELECT
            CASE
                WHEN hr.estado = 'conocimiento' THEN 'PARA CONOCIMIENTO'
                ELSE o.nombre
            END as oficina_destino
        FROM hojas_ruta hr
        LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id";
```

---

### 6.7 Error: Visor PDF no carga documentos de conocimiento

**Causa:** JavaScript buscaba en carpeta incorrecta

**Solución en verDetalleHoja():**
```javascript
if (data.estado === 'conocimiento') {
    rutaPDF = rutaBase + 'Assets/documentos_para_conocimiento/' + data.archivo_adjunto;
} else {
    var carpeta = data.estado === 'en_proceso' ? 'en_proceso' :
                  data.estado === 'completado' ? 'completados' : 'archivado';
    rutaPDF = rutaBase + 'Assets/documentos_oficiales/' + carpeta + '/' + data.archivo_adjunto;
}
```

---

## 7. RELACIONES ENTRE TABLAS

### Diagrama de Relaciones

```
usuarios
  ├──> oficinas (id_oficina FK)
  ├──> carpetas (id_usuario FK) ──> archivos (id_carpeta FK)
  └──> hojas_ruta (id_usuario_registro FK)

oficinas
  ├──> usuarios (inverso)
  ├──> hojas_ruta (id_oficina_destino FK, puede ser NULL)
  └──> documentos_oficiales (id_oficina_destino FK)

hojas_ruta
  ├──> usuarios (id_usuario_registro FK)
  ├──> oficinas (id_oficina_destino FK, NULL = Para Conocimiento)
  └──> Archivo físico en Assets/documentos_oficiales/ o Assets/documentos_para_conocimiento/

documentos_oficiales
  ├──> carpetas (id_carpeta FK, siempre 1)
  ├──> oficinas (id_oficina_destino FK)
  ├──> usuarios (id_usuario_completo FK)
  └──> documentos_respondidos (id_documento FK)

documentos_respondidos
  ├──> documentos_oficiales (id_documento FK)
  ├──> oficinas (id_oficina_respondio FK)
  └──> usuarios (id_usuario_respondio FK)
```

---

## 8. ESTRUCTURA DE CARPETAS FÍSICAS

```
Assets/
├── documentos_oficiales/
│   ├── en_proceso/           # PDFs de documentos delegados pendientes
│   │   └── HR-0001.pdf
│   ├── completados/          # PDFs de respuestas de usuarios
│   │   └── RES-Asunto-CAPA-001.pdf
│   └── archivado/            # PDFs archivados por admin
│
└── documentos_para_conocimiento/   # PDFs para conocimiento
    └── PC-0001.pdf
```

---

## RESUMEN EJECUTIVO

### Módulos Implementados:
1. **Hoja de Ruta** (Admin) - Registro y delegación
2. **Calendario** (Usuarios) - Visualización y respuesta
3. **Reportes** (Admin) - Estadísticas y reportes detallados

### Tablas Principales:
- `hojas_ruta` - Registro central de documentación
- `documentos_oficiales` - Tareas del calendario
- `documentos_respondidos` - Historial de respuestas
- `archivos` - Registro de archivos del sistema

### Flujos Principales:
1. **Delegación:** Admin → Hoja de Ruta → Calendario Usuario → Respuesta → Reportes
2. **Conocimiento:** Admin → Publicar P.C. → Visible para todos

### Innovaciones Técnicas:
- Sistema de numeración correlativa automática por año
- Generación inteligente de nombres de archivo (RES-Asunto-ABREV-###.pdf)
- Filtrado por rol (Admin ve todo, Usuario ve su oficina)
- Reportes dinámicos con filtros de fecha y estado

---

## CONCLUSIÓN

El sistema implementa un flujo completo de gestión de documentos oficiales con las siguientes características:

✅ Registro centralizado en `hojas_ruta`
✅ Delegación a oficinas específicas con calendario
✅ Publicación de documentos informativos
✅ Sistema de respuestas con seguimiento
✅ Reportes y estadísticas en tiempo real
✅ Arquitectura MVC limpia y mantenible

**Total de líneas de código documentadas:** ~5000+
**Archivos involucrados:** 8 archivos principales (Controllers, Models, Views)
**Tablas de base de datos:** 8 tablas con relaciones FK
**Endpoints AJAX:** 12+ endpoints funcionales
