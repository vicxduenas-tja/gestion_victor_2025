<?php
class CalendarioModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // ============ GESTIÓN DE DOCUMENTOS ============
    
    public function getDocumentosCalendario($id_usuario)
    {
        $sql = "SELECT d.*, c.nombre as carpeta_nombre,
                CONCAT(u.nombre, ' ', u.apellido) as usuario_asignado,
                CONCAT(ur.nombre, ' ', ur.apellido) as usuario_registro
                FROM documentos_oficiales d
                LEFT JOIN carpetas c ON d.id_carpeta = c.id
                LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
                LEFT JOIN usuarios ur ON d.id_usuario_registro = ur.id
                WHERE (d.id_usuario_asignado = $id_usuario OR d.id_usuario_registro = $id_usuario)
                AND d.estado != 'ELIMINADO'
                ORDER BY d.fecha_limite";
        return $this->selectAll($sql);
    }
    
    public function getDocumento($id)
    {
        $sql = "SELECT d.*, c.nombre as carpeta_nombre,
                CONCAT(u.nombre, ' ', u.apellido) as usuario_asignado,
                CONCAT(ur.nombre, ' ', ur.apellido) as usuario_registro
                FROM documentos_oficiales d
                LEFT JOIN carpetas c ON d.id_carpeta = c.id
                LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
                LEFT JOIN usuarios ur ON d.id_usuario_registro = ur.id
                WHERE d.id = $id";
        return $this->select($sql);
    }
    
    public function verificarNumeroDocumento($numero, $id = null)
    {
        $sql = "SELECT id FROM documentos_oficiales WHERE numero_documento = '$numero'";
        if ($id != null) {
            $sql .= " AND id != $id";
        }
        return $this->select($sql);
    }
    
    public function crearDocumento($data)
    {
        $sql = "INSERT INTO documentos_oficiales (
                    numero_documento, asunto, remitente, destinatario, 
                    fecha_recepcion, fecha_limite, prioridad, tipo_documento, 
                    observaciones, id_carpeta, id_usuario_asignado, 
                    id_usuario_registro, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')";
        
        $datos = array(
            $data['numero_documento'],
            $data['asunto'],
            $data['remitente'],
            $data['destinatario'],
            $data['fecha_recepcion'],
            $data['fecha_limite'],
            $data['prioridad'],
            $data['tipo_documento'],
            $data['observaciones'],
            $data['id_carpeta'],
            $data['id_usuario_asignado'],
            $data['id_usuario_registro']
        );
        
        return $this->insertar($sql, $datos);
    }
    
    public function actualizarDocumento($id, $data)
    {
        $sql = "UPDATE documentos_oficiales SET
                numero_documento = ?, asunto = ?, remitente = ?, destinatario = ?,
                fecha_recepcion = ?, fecha_limite = ?, prioridad = ?, 
                tipo_documento = ?, observaciones = ?, id_carpeta = ?,
                id_usuario_asignado = ?
                WHERE id = ?";
        
        $datos = array(
            $data['numero_documento'],
            $data['asunto'],
            $data['remitente'],
            $data['destinatario'],
            $data['fecha_recepcion'],
            $data['fecha_limite'],
            $data['prioridad'],
            $data['tipo_documento'],
            $data['observaciones'],
            $data['id_carpeta'],
            $data['id_usuario_asignado'],
            $id
        );
        
        return $this->save($sql, $datos);
    }
    
    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE documentos_oficiales SET estado = ? WHERE id = ?";
        return $this->save($sql, array($estado, $id));
    }
    
    public function eliminarDocumento($id)
    {
        $sql = "UPDATE documentos_oficiales SET estado = 'ELIMINADO' WHERE id = ?";
        return $this->save($sql, array($id));
    }
    
    // ============ SEGUIMIENTO DE DOCUMENTOS ============
    
    public function getSeguimiento($id_documento)
    {
        $sql = "SELECT s.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM seguimiento_documentos s
                INNER JOIN usuarios u ON s.id_usuario = u.id
                WHERE s.id_documento = $id_documento
                ORDER BY s.fecha DESC";
        return $this->selectAll($sql);
    }
    
    public function registrarSeguimiento($id_documento, $id_usuario, $accion, $comentario = '')
    {
        $sql = "INSERT INTO seguimiento_documentos (id_documento, id_usuario, accion, comentario) 
                VALUES (?, ?, ?, ?)";
        return $this->insertar($sql, array($id_documento, $id_usuario, $accion, $comentario));
    }
    
    // ============ NOTIFICACIONES ============
    
    public function crearNotificacion($id_usuario, $id_documento, $tipo, $mensaje)
    {
        $sql = "INSERT INTO notificaciones (id_usuario, id_documento, tipo, mensaje) 
                VALUES (?, ?, ?, ?)";
        return $this->insertar($sql, array($id_usuario, $id_documento, $tipo, $mensaje));
    }
    
    public function getNotificacionesNoLeidas($id_usuario)
    {
        $sql = "SELECT n.*, d.numero_documento
                FROM notificaciones n
                LEFT JOIN documentos_oficiales d ON n.id_documento = d.id
                WHERE n.id_usuario = $id_usuario AND n.leido = 0
                ORDER BY n.fecha DESC
                LIMIT 10";
        return $this->selectAll($sql);
    }
    
    public function marcarNotificacionLeida($id)
    {
        $sql = "UPDATE notificaciones SET leido = 1 WHERE id = ?";
        return $this->save($sql, array($id));
    }
    
    public function getTotalNotificacionesNoLeidas($id_usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM notificaciones 
                WHERE id_usuario = $id_usuario AND leido = 0";
        $result = $this->select($sql);
        return $result['total'] ?? 0;
    }
    
    // ============ ESTADÍSTICAS ============
    
    public function getTotalDocumentosPorEstado($estado, $id_usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM documentos_oficiales 
                WHERE estado = '$estado' 
                AND (id_usuario_asignado = $id_usuario OR id_usuario_registro = $id_usuario)";
        $result = $this->select($sql);
        return $result['total'] ?? 0;
    }
    
    public function getTotalUrgentesHoy($id_usuario)
    {
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM documentos_oficiales 
                WHERE prioridad = 'URGENTE' 
                AND DATE(fecha_limite) = '$hoy'
                AND estado NOT IN ('COMPLETADO', 'ELIMINADO')
                AND (id_usuario_asignado = $id_usuario OR id_usuario_registro = $id_usuario)";
        $result = $this->select($sql);
        return $result['total'] ?? 0;
    }
    
    public function getDocumentosProximosVencer($id_usuario, $dias = 3)
    {
        $hoy = date('Y-m-d');
        $fecha_limite = date('Y-m-d', strtotime("+$dias days"));
        
        $sql = "SELECT * FROM documentos_oficiales 
                WHERE DATE(fecha_limite) BETWEEN '$hoy' AND '$fecha_limite'
                AND estado NOT IN ('COMPLETADO', 'VENCIDO', 'ELIMINADO')
                AND (id_usuario_asignado = $id_usuario OR id_usuario_registro = $id_usuario)
                ORDER BY fecha_limite";
        return $this->selectAll($sql);
    }
    
    public function getDocumentosDelDia($fecha, $id_usuario)
    {
        $sql = "SELECT d.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_asignado
                FROM documentos_oficiales d
                LEFT JOIN usuarios u ON d.id_usuario_asignado = u.id
                WHERE DATE(d.fecha_limite) = '$fecha'
                AND d.estado != 'ELIMINADO'
                AND (d.id_usuario_asignado = $id_usuario OR d.id_usuario_registro = $id_usuario)
                ORDER BY d.prioridad DESC, d.fecha_limite";
        return $this->selectAll($sql);
    }
    
    // ============ USUARIOS Y CARPETAS ============
    
    public function getCarpetas()
    {
        $sql = "SELECT id, nombre FROM carpetas WHERE estado = 1 ORDER BY nombre";
        return $this->selectAll($sql);
    }
    
    public function getUsuariosActivos()
    {
        $sql = "SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo, correo
                FROM usuarios WHERE estado = 1 ORDER BY nombre";
        return $this->selectAll($sql);
    }
    
    public function verificarEstado($correo)
    {
        $sql = "SELECT * FROM compartidos WHERE correo_destino = '$correo' AND estado = 1";
        return $this->selectAll($sql);
    }
    
    // ============ ACTUALIZACIÓN AUTOMÁTICA ============
    
    public function actualizarDocumentosVencidos()
    {
        $hoy = date('Y-m-d H:i:s');
        $sql = "UPDATE documentos_oficiales 
                SET estado = 'VENCIDO' 
                WHERE fecha_limite < '$hoy' 
                AND estado NOT IN ('COMPLETADO', 'VENCIDO', 'ELIMINADO')";
        return $this->save($sql, array());
    }
    
    public function getDocumentosParaNotificar()
    {
        $manana = date('Y-m-d', strtotime('+1 day'));
        $sql = "SELECT d.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre, u.correo
                FROM documentos_oficiales d
                INNER JOIN usuarios u ON d.id_usuario_asignado = u.id
                WHERE DATE(d.fecha_limite) = '$manana'
                AND d.estado NOT IN ('COMPLETADO', 'VENCIDO', 'ELIMINADO')";
        return $this->selectAll($sql);
    }
    
    // ============ REPORTES ============
    
    public function getEstadisticasMensuales($mes, $anio, $id_usuario)
    {
        $sql = "SELECT estado, COUNT(*) as total
                FROM documentos_oficiales
                WHERE MONTH(fecha_recepcion) = $mes
                AND YEAR(fecha_recepcion) = $anio
                AND (id_usuario_asignado = $id_usuario OR id_usuario_registro = $id_usuario)
                GROUP BY estado";
        return $this->selectAll($sql);
    }
    
    public function getDocumentosPorPrioridad($id_usuario)
    {
        $sql = "SELECT prioridad, COUNT(*) as total
                FROM documentos_oficiales
                WHERE estado NOT IN ('COMPLETADO', 'ELIMINADO')
                AND (id_usuario_asignado = $id_usuario OR id_usuario_registro = $id_usuario)
                GROUP BY prioridad";
        return $this->selectAll($sql);
    }
}
?>