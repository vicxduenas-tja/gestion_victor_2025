<?php
class CalendarioModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDocumentosPendientes($id_usuario)
    {
        // Obtener rol del usuario
        $sqlRol = "SELECT rol, id_oficina FROM usuarios WHERE id = $id_usuario";
        $usuario = $this->select($sqlRol);

        if ($usuario['rol'] == 1) {
            // SUPER ADMIN: Ve TODAS las tareas delegadas + documentos de conocimiento
            $sql = "SELECT
                d.id,
                d.numero_documento,
                d.asunto,
                d.fecha_recepcion,
                d.fecha_limite,
                d.fecha_completado,
                d.prioridad,
                d.estado,
                c.nombre as carpeta,
                COALESCE(CONCAT(u.nombre, ' ', u.apellido), o.nombre) as usuario_asignado,
                'tarea' as tipo_documento
            FROM documentos_oficiales d
            LEFT JOIN carpetas c ON d.id_carpeta = c.id
            LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE d.estado IN ('delegado', 'en_progreso', 'respondiendo', 'completado', 'archivado')

            UNION

            SELECT
                hr.id,
                hr.numero_registro as numero_documento,
                hr.asunto,
                hr.fecha_recepcion,
                hr.fecha_limite,
                NULL as fecha_completado,
                hr.prioridad,
                hr.estado,
                'Conocimiento' as carpeta,
                CASE
                    WHEN hr.id_oficina_destino = 0 THEN 'TODAS LAS OFICINAS'
                    ELSE o.nombre
                END as usuario_asignado,
                'conocimiento' as tipo_documento
            FROM hojas_ruta hr
            LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
            WHERE hr.estado = 'conocimiento'

            ORDER BY fecha_limite ASC";
        } else {
            // USUARIO: Ve tareas de SU oficina + documentos de conocimiento (TODAS o su oficina)
            $sql = "SELECT
                d.id,
                d.numero_documento,
                d.asunto,
                d.fecha_recepcion,
                d.fecha_limite,
                d.fecha_completado,
                d.prioridad,
                d.estado,
                c.nombre as carpeta,
                COALESCE(CONCAT(u.nombre, ' ', u.apellido), o.nombre) as usuario_asignado,
                'tarea' as tipo_documento
            FROM documentos_oficiales d
            LEFT JOIN carpetas c ON d.id_carpeta = c.id
            LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE (d.id_usuario_asignado = $id_usuario
                   OR d.id_oficina_destino = {$usuario['id_oficina']})
            AND d.estado IN ('delegado', 'en_progreso', 'respondiendo', 'completado', 'archivado')

            UNION

            SELECT
                hr.id,
                hr.numero_registro as numero_documento,
                hr.asunto,
                hr.fecha_recepcion,
                hr.fecha_limite,
                NULL as fecha_completado,
                hr.prioridad,
                hr.estado,
                'Conocimiento' as carpeta,
                CASE
                    WHEN hr.id_oficina_destino = 0 THEN 'TODAS LAS OFICINAS'
                    ELSE o.nombre
                END as usuario_asignado,
                'conocimiento' as tipo_documento
            FROM hojas_ruta hr
            LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
            WHERE hr.estado = 'conocimiento'
            AND (hr.id_oficina_destino = 0 OR hr.id_oficina_destino = {$usuario['id_oficina']})

            ORDER BY fecha_limite ASC";
        }

        return $this->selectAll($sql);
    }

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
            // ADMIN: Ve TODOS los documentos delegados + conocimiento
            $sql = "SELECT
                        (SELECT COUNT(*) FROM documentos_oficiales
                         WHERE estado = 'delegado') +
                        (SELECT COUNT(*) FROM hojas_ruta
                         WHERE estado = 'conocimiento')
                    AS total";
        } else {
            // USUARIO: Ve delegados de su oficina + documentos para conocimiento (TODAS o su oficina)
            $sql = "SELECT
                        (SELECT COUNT(*) FROM documentos_oficiales
                         WHERE id_oficina_destino = $id_oficina AND estado = 'delegado') +
                        (SELECT COUNT(*) FROM hojas_ruta
                         WHERE estado = 'conocimiento'
                         AND (id_oficina_destino = 0 OR id_oficina_destino = $id_oficina))
                    AS total";
        }

        $resultado = $this->select($sql);
        return $resultado['total'];
    }

    // Crear nuevo documento
    public function crearDocumento($numero, $asunto, $fecha_limite, $prioridad, $id_usuario)
    {
        $fecha_recepcion = date('Y-m-d');
        $estado = 'pendiente';
        $id_carpeta = 1; // Carpeta por defecto

        $sql = "INSERT INTO documentos_oficiales 
                (numero_documento, asunto, fecha_recepcion, fecha_limite, prioridad, estado, id_carpeta, id_usuario_asignado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $datos = array($numero, $asunto, $fecha_recepcion, $fecha_limite, $prioridad, $estado, $id_carpeta, $id_usuario);

        return $this->insertar($sql, $datos);
    }

    // Obtener un documento por ID
    public function getDocumento($id)
    {
        $sql = "SELECT * FROM documentos_oficiales WHERE id = $id";
        return $this->select($sql);
    }

    // Registrar visualización (cambiar a 'en_progreso')
    public function registrarVisualizacion($id)
    {
        $sql = "UPDATE documentos_oficiales 
            SET estado = 'en_progreso',
                fecha_visualizado = NOW()
            WHERE id = ?";

        $datos = array($id);
        return $this->save($sql, $datos);
    }

    // Completar tarea
    public function completarTarea($id, $archivo_respuesta, $comentarios, $id_usuario, $estado_final)
    {
        $sql = "UPDATE documentos_oficiales 
            SET estado = ?,
                fecha_completado = NOW(),
                archivo_respuesta = ?,
                observaciones_completado = ?,
                id_usuario_completo = ?,
                fecha_inicio_respuesta = NOW()
            WHERE id = ?";

        $datos = array($estado_final, $archivo_respuesta, $comentarios, $id_usuario, $id);
        return $this->save($sql, $datos);
    }

    // Obtener ID de la carpeta "Respondidos" del usuario
    public function obtenerCarpetaRespondidos($id_usuario)
    {
        $sql = "SELECT id FROM carpetas WHERE nombre = 'Respondidos' AND id_usuario = $id_usuario LIMIT 1";
        $carpeta = $this->select($sql);
        return $carpeta ? $carpeta['id'] : null;
    }

    public function registrarArchivoRespondido($id_carpeta, $nombre_archivo, $id_usuario)
    {
        $sql = "INSERT INTO archivos (nombre, id_carpeta, id_usuario) 
            VALUES (?, ?, ?)";
        $datos = array($nombre_archivo, $id_carpeta, $id_usuario);
        return $this->insertar($sql, $datos);
    }
}
