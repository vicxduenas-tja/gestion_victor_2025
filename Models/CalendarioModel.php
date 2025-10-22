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
            // SUPER ADMIN: Ve TODAS las tareas
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
                d.id,
                d.numero_documento,
                d.asunto,
                d.fecha_recepcion,
                d.fecha_limite,
                d.fecha_completado,
                d.prioridad,
                d.estado,  
                c.nombre as carpeta,
                COALESCE(CONCAT(u.nombre, ' ', u.apellido), o.nombre) as usuario_asignado
            FROM documentos_oficiales d
            LEFT JOIN carpetas c ON d.id_carpeta = c.id
            LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
            LEFT JOIN oficinas o ON d.id_oficina_destino = o.id
            WHERE (d.id_usuario_asignado = $id_usuario 
                   OR d.id_oficina_destino = {$usuario['id_oficina']})
            AND d.estado IN ('delegado', 'en_progreso', 'respondiendo', 'completado', 'archivado')
            ORDER BY d.fecha_limite ASC";
        }

        return $this->selectAll($sql);
    }

    // Contar documentos pendientes
    public function contarPendientes($id_usuario)
    {
        $sql = "SELECT COUNT(*) as total
                FROM documentos_oficiales 
                WHERE id_usuario_asignado = $id_usuario 
                AND estado = 'pendiente'";

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
