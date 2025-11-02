<?php
class HojaRutaModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // Obtener todas las oficinas activas
    public function getOficinas()
    {
        $sql = "SELECT id, nombre, abreviatura
                FROM oficinas
                WHERE estado = 1
                ORDER BY nombre";
        return $this->selectAll($sql);
    }
    
    // Crear nueva Hoja de Ruta
    public function crearHojaRuta($datos)
    {
        $sql = "INSERT INTO hojas_ruta 
                (numero_registro, fecha_recepcion, remitente, asunto, id_oficina_destino, 
                 fecha_limite, prioridad, observaciones, id_usuario_registro, archivo_adjunto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->insertar($sql, $datos);
    }
    
    // Generar número de registro automático
    public function generarNumeroRegistro()
    {
        $año = date('Y');
        $sql = "SELECT COUNT(*) as total FROM hojas_ruta WHERE YEAR(fecha_recepcion) = $año";
        $resultado = $this->select($sql);
        $consecutivo = $resultado['total'] + 1;
        
        return str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }

    // Listar todas las hojas de ruta
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
                    o.nombre as oficina_destino
                FROM hojas_ruta hr
                LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
                $whereClause
                ORDER BY hr.fecha_recepcion DESC";
        
        return $this->selectAll($sql);
    }
}
?>