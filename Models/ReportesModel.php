<?php
class ReportesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Función para los WIDGETS
     */
    public function contarDocumentos(string $tipo, int $rol, int $id_oficina = null)
    {
        $where = '';
        $tabla = 'documentos_oficiales';
        $where_rol = '';

        if ($rol != 1) { // Si NO es Admin
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

    /**
     * Función para la TABLA DE REPORTE DETALLADO (General)
     */
    public function getReporteDetallado(string $desde, string $hasta, string $estado)
    {
        $whereEstado = ""; 

        // 1. Lógica para "Para Conocimiento" (busca en hojas_ruta)
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
                    FROM 
                        hojas_ruta hr
                    WHERE 
                        hr.fecha_recepcion BETWEEN '$desde' AND '$hasta'
                        AND hr.estado = 'conocimiento'
                    ORDER BY 
                        hr.fecha_recepcion DESC";

            return $this->selectAll($sql);
        }

        // 2. Lógica para todos los demás estados (buscan en documentos_oficiales)
        
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
                FROM 
                    documentos_oficiales d
                LEFT JOIN 
                    oficinas o ON d.id_oficina_destino = o.id
                WHERE 
                    d.fecha_recepcion BETWEEN '$desde' AND '$hasta'
                    $whereEstado
                ORDER BY 
                    d.fecha_recepcion DESC";

        return $this->selectAll($sql);
    }
    
    /**
     * Reporte de Productividad por Usuario (Quién respondió y sus archivos)
     */
    public function getReporteProductividad()
    {
        $sql = "
            SELECT 
                u.id,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario,
                o.nombre AS oficina_usuario,
                u.cargo,
                
                -- Conteo 1: Total de documentos respondidos por el usuario (DR)
                (SELECT COUNT(*) FROM documentos_respondidos dr WHERE dr.id_usuario_respondio = u.id) AS total_respondidos,
                
                -- Conteo 2: Total de archivos guardados en la carpeta fija 'Documentos Respondidos' del usuario
                (SELECT COUNT(a.id) FROM archivos a
                 LEFT JOIN carpetas c ON a.id_carpeta = c.id
                 WHERE a.id_usuario = u.id AND c.nombre = 'Documentos Respondidos' AND a.estado = 1) AS archivos_respondidos_guardados,
                
                -- Conteo 3: Total de documentos DELEGADOS a la oficina del usuario (para referencia)
                (SELECT COUNT(do.id) FROM documentos_oficiales do 
                 WHERE do.id_oficina_destino = u.id_oficina AND do.estado NOT IN ('conocimiento')) AS total_delegado_oficina
                 
            FROM usuarios u
            LEFT JOIN oficinas o ON u.id_oficina = o.id
            WHERE u.rol != 1 -- Excluir al Super Admin (id=1)
            AND u.estado = 1 -- Solo usuarios activos
            ORDER BY o.nombre, u.apellido
        ";

        return $this->selectAll($sql);
    }

    /**
     * Obtiene el INVENTARIO detallado de documentos activos (tareas y PC) para el relevo.
     */
    public function getInventarioActivo(int $id_oficina = 0)
    {
        $id_oficina_safe = intval($id_oficina);

        // Si es Admin (id_oficina = 0), mostrar de todas las oficinas
        $whereOficina = ($id_oficina_safe > 0) ? "d.id_oficina_destino = $id_oficina_safe AND" : "";

        $sql = "
            -- Bloque 1: Tareas Pendientes (En Progreso) de la oficina
            (SELECT
                d.numero_documento,
                d.asunto,
                d.fecha_recepcion,
                d.fecha_limite,
                d.estado,
                d.sin_limite,
                'Tarea' AS tipo_doc
            FROM documentos_oficiales d
            WHERE
                $whereOficina d.estado = 'en_progreso')

            UNION ALL

            -- Bloque 2: Documentos de Conocimiento (Globales)
            (SELECT
                hr.numero_registro AS numero_documento,
                hr.asunto,
                hr.fecha_recepcion,
                hr.fecha_limite,
                hr.estado,
                hr.sin_limite,
                'Conocimiento' AS tipo_doc
            FROM hojas_ruta hr
            WHERE
                hr.estado = 'conocimiento')

            ORDER BY tipo_doc DESC, fecha_recepcion ASC
        ";

        return $this->selectAll($sql);
    }

    /**
     * Obtiene el detalle de documentos que un usuario en específico respondió.
     */
    public function getDetalleDocumentosRespondidos(int $id_usuario)
    {
        $sql = "
            SELECT 
                dr.id AS id_respuesta,
                do.numero_documento,
                do.asunto,
                dr.archivo_respuesta,
                dr.fecha_respuesta,
                dr.observaciones,
                CASE 
                    WHEN do.fecha_limite < dr.fecha_respuesta THEN 'RESPONDIDO CON RETRASO'
                    ELSE 'RESPONDIDO A TIEMPO'
                END AS cumplimiento
            FROM 
                documentos_respondidos dr
            INNER JOIN 
                documentos_oficiales do ON dr.id_documento = do.id
            WHERE 
                dr.id_usuario_respondio = $id_usuario
            ORDER BY 
                dr.fecha_respuesta DESC
        ";

        return $this->selectAll($sql);
    }
}