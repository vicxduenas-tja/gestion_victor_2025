<?php
class CalendarioModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // Obtener documentos pendientes para calendario
    public function getDocumentosPendientes($id_usuario)
    {
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
                       OR d.id_oficina_destino = (SELECT id_oficina FROM usuarios WHERE id = $id_usuario))
                AND d.estado IN ('pendiente', 'completado')
                ORDER BY d.fecha_limite ASC";
        
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

    // Cambiar estado a completado
    public function completarDocumento($id)
    {
        $fecha_completado = date('Y-m-d H:i:s');
        
        $sql = "UPDATE documentos_oficiales 
                SET estado = 'completado',
                    fecha_completado = ?
                WHERE id = ?";
        
        return $this->save($sql, array($fecha_completado, $id));
    }
}
?>