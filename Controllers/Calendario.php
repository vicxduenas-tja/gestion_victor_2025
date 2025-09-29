<?php
class Calendario extends Controller
{
    private $id_usuario, $correo, $nombre;
    
    public function __construct()
    {
        parent::__construct();
        session_start();
        
        // Validar sesión
        if (empty($_SESSION['id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $this->id_usuario = $_SESSION['id'];
        $this->correo = $_SESSION['correo'];
        $this->nombre = $_SESSION['nombre'];
        
        // Actualizar documentos vencidos automáticamente
        $this->model->actualizarDocumentosVencidos();
    }
    
    // ============ VISTA PRINCIPAL ============
    
    public function index()
    {
        $data['title'] = 'Calendario - Seguimiento de Documentos';
        $data['script'] = 'calendario.js';
        $data['menu'] = 'calendario';
        $data['shares'] = $this->model->verificarEstado($this->correo);
        
        // Obtener estadísticas para el dashboard
        $data['stats'] = [
            'pendientes' => $this->model->getTotalDocumentosPorEstado('PENDIENTE', $this->id_usuario),
            'en_proceso' => $this->model->getTotalDocumentosPorEstado('EN_PROCESO', $this->id_usuario),
            'completados' => $this->model->getTotalDocumentosPorEstado('COMPLETADO', $this->id_usuario),
            'vencidos' => $this->model->getTotalDocumentosPorEstado('VENCIDO', $this->id_usuario),
            'urgentes' => $this->model->getTotalUrgentesHoy($this->id_usuario),
            'proximos_vencer' => count($this->model->getDocumentosProximosVencer($this->id_usuario, 3))
        ];
        
        // Obtener notificaciones no leídas
        $data['notificaciones'] = $this->model->getNotificacionesNoLeidas($this->id_usuario);
        $data['total_notificaciones'] = $this->model->getTotalNotificacionesNoLeidas($this->id_usuario);
        
        // Obtener carpetas y usuarios
        $data['carpetas'] = $this->model->getCarpetas();
        $data['usuarios'] = $this->model->getUsuariosActivos();
        
        $this->views->getView('calendario', 'index', $data);
    }
    
    // ============ GESTIÓN DE DOCUMENTOS ============
    
    public function getEventos()
    {
        $documentos = $this->model->getDocumentosCalendario($this->id_usuario);
        $eventos = [];
        
        foreach ($documentos as $doc) {
            $color = $this->getColorEvento($doc['prioridad'], $doc['estado']);
            
            $eventos[] = [
                'id' => $doc['id'],
                'title' => $doc['numero_documento'] . ' - ' . substr($doc['asunto'], 0, 30),
                'start' => $doc['fecha_limite'],
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'numero' => $doc['numero_documento'],
                    'asunto' => $doc['asunto'],
                    'remitente' => $doc['remitente'],
                    'destinatario' => $doc['destinatario'],
                    'prioridad' => $doc['prioridad'],
                    'estado' => $doc['estado'],
                    'tipo_documento' => $doc['tipo_documento']
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($eventos);
    }
    
    private function getColorEvento($prioridad, $estado)
    {
        // Colores según estado
        if ($estado == 'COMPLETADO') return '#27ae60';
        if ($estado == 'VENCIDO') return '#95a5a6';
        
        // Colores según prioridad
        switch ($prioridad) {
            case 'URGENTE': return '#e74c3c';
            case 'ALTA': return '#f39c12';
            case 'MEDIA': return '#3498db';
            case 'BAJA': return '#95a5a6';
            default: return '#3498db';
        }
    }
    
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['doc_id'] ?? '';
            $numero = trim($_POST['numero_documento']);
            $asunto = trim($_POST['asunto']);
            $remitente = trim($_POST['remitente']);
            $destinatario = trim($_POST['destinatario']);
            $fecha_recepcion = $_POST['fecha_recepcion'];
            $fecha_limite = $_POST['fecha_limite'];
            $prioridad = $_POST['prioridad'];
            $tipo_documento = $_POST['tipo_documento'];
            $observaciones = trim($_POST['observaciones'] ?? '');
            $id_carpeta = $_POST['id_carpeta'];
            $id_usuario_asignado = $_POST['id_usuario_asignado'];
            
            // Validaciones
            if (empty($numero) || empty($asunto) || empty($remitente) || 
                empty($fecha_recepcion) || empty($fecha_limite)) {
                $res = array('msg' => 'Todos los campos obligatorios son requeridos', 'tipo' => 'warning');
                echo json_encode($res);
                die();
            }
            
            // Verificar número de documento duplicado
            $verificar = $this->model->verificarNumeroDocumento($numero, $id);
            if (!empty($verificar)) {
                $res = array('msg' => 'El número de documento ya existe', 'tipo' => 'warning');
                echo json_encode($res);
                die();
            }
            
            $data = [
                'numero_documento' => $numero,
                'asunto' => $asunto,
                'remitente' => $remitente,
                'destinatario' => $destinatario,
                'fecha_recepcion' => $fecha_recepcion,
                'fecha_limite' => $fecha_limite,
                'prioridad' => $prioridad,
                'tipo_documento' => $tipo_documento,
                'observaciones' => $observaciones,
                'id_carpeta' => $id_carpeta,
                'id_usuario_asignado' => $id_usuario_asignado
            ];
            
            if (empty($id)) {
                // Crear nuevo documento
                $data['id_usuario_registro'] = $this->id_usuario;
                $resultado = $this->model->crearDocumento($data);
                
                if ($resultado > 0) {
                    // Registrar en seguimiento
                    $this->model->registrarSeguimiento(
                        $resultado, 
                        $this->id_usuario, 
                        'CREADO', 
                        'Documento creado'
                    );
                    
                    // Crear notificación para el usuario asignado
                    if ($id_usuario_asignado != $this->id_usuario) {
                        $this->model->crearNotificacion(
                            $id_usuario_asignado,
                            $resultado,
                            'ASIGNACION',
                            "Se le ha asignado el documento: $numero"
                        );
                    }
                    
                    $res = array('msg' => 'Documento registrado correctamente', 'tipo' => 'success');
                } else {
                    $res = array('msg' => 'Error al registrar el documento', 'tipo' => 'error');
                }
            } else {
                // Actualizar documento existente
                $resultado = $this->model->actualizarDocumento($id, $data);
                
                if ($resultado) {
                    $this->model->registrarSeguimiento(
                        $id, 
                        $this->id_usuario, 
                        'ACTUALIZADO', 
                        'Documento actualizado'
                    );
                    $res = array('msg' => 'Documento actualizado correctamente', 'tipo' => 'success');
                } else {
                    $res = array('msg' => 'Error al actualizar el documento', 'tipo' => 'error');
                }
            }
            
            echo json_encode($res);
        }
        die();
    }
    
    public function getDocumento($id)
    {
        $documento = $this->model->getDocumento($id);
        if (!empty($documento)) {
            $seguimiento = $this->model->getSeguimiento($id);
            $documento['seguimiento'] = $seguimiento;
            echo json_encode($documento);
        } else {
            echo json_encode(array('error' => 'Documento no encontrado'));
        }
        die();
    }
    
    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['estado'];
            $comentario = $_POST['comentario'] ?? '';
            
            $resultado = $this->model->cambiarEstado($id, $estado);
            
            if ($resultado) {
                // Registrar en seguimiento
                $accion = '';
                switch ($estado) {
                    case 'EN_PROCESO':
                        $accion = 'INICIADO';
                        break;
                    case 'COMPLETADO':
                        $accion = 'COMPLETADO';
                        break;
                    case 'VENCIDO':
                        $accion = 'VENCIDO';
                        break;
                }
                
                $this->model->registrarSeguimiento($id, $this->id_usuario, $accion, $comentario);
                
                $res = array('msg' => 'Estado actualizado correctamente', 'tipo' => 'success');
            } else {
                $res = array('msg' => 'Error al actualizar el estado', 'tipo' => 'error');
            }
            
            echo json_encode($res);
        }
        die();
    }
    
    public function derivar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id_documento'];
            $id_usuario_destino = $_POST['id_usuario_destino'];
            $comentario = $_POST['comentario'] ?? '';
            
            // Obtener documento
            $doc = $this->model->getDocumento($id);
            
            // Cambiar usuario asignado
            $data = [
                'numero_documento' => $doc['numero_documento'],
                'asunto' => $doc['asunto'],
                'remitente' => $doc['remitente'],
                'destinatario' => $doc['destinatario'],
                'fecha_recepcion' => $doc['fecha_recepcion'],
                'fecha_limite' => $doc['fecha_limite'],
                'prioridad' => $doc['prioridad'],
                'tipo_documento' => $doc['tipo_documento'],
                'observaciones' => $doc['observaciones'],
                'id_carpeta' => $doc['id_carpeta'],
                'id_usuario_asignado' => $id_usuario_destino
            ];
            
            $resultado = $this->model->actualizarDocumento($id, $data);
            
            if ($resultado) {
                // Registrar derivación
                $this->model->registrarSeguimiento(
                    $id, 
                    $this->id_usuario, 
                    'DERIVADO', 
                    $comentario
                );
                
                // Notificar al nuevo usuario
                $this->model->crearNotificacion(
                    $id_usuario_destino,
                    $id,
                    'DERIVACION',
                    "Se le ha derivado el documento: {$doc['numero_documento']}"
                );
                
                $res = array('msg' => 'Documento derivado correctamente', 'tipo' => 'success');
            } else {
                $res = array('msg' => 'Error al derivar el documento', 'tipo' => 'error');
            }
            
            echo json_encode($res);
        }
        die();
    }
    
    public function eliminar($id)
    {
        $resultado = $this->model->eliminarDocumento($id);
        
        if ($resultado) {
            $this->model->registrarSeguimiento($id, $this->id_usuario, 'ELIMINADO', 'Documento eliminado');
            $res = array('msg' => 'Documento eliminado correctamente', 'tipo' => 'success');
        } else {
            $res = array('msg' => 'Error al eliminar el documento', 'tipo' => 'error');
        }
        
        echo json_encode($res);
        die();
    }
    
    // ============ DOCUMENTOS DEL DÍA ============
    
    public function getDocumentosDelDia()
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $documentos = $this->model->getDocumentosDelDia($fecha, $this->id_usuario);
        echo json_encode($documentos);
        die();
    }
    
    // ============ NOTIFICACIONES ============
    
    public function marcarLeida($id)
    {
        $resultado = $this->model->marcarNotificacionLeida($id);
        echo json_encode(array('success' => $resultado));
        die();
    }
    
    public function getNotificaciones()
    {
        $notificaciones = $this->model->getNotificacionesNoLeidas($this->id_usuario);
        echo json_encode($notificaciones);
        die();
    }
    
    // ============ REPORTES ============
    
    public function reportes()
    {
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $estadisticas = $this->model->getEstadisticasMensuales($mes, $anio, $this->id_usuario);
        $porPrioridad = $this->model->getDocumentosPorPrioridad($this->id_usuario);
        
        $data = [
            'estadisticas' => $estadisticas,
            'porPrioridad' => $porPrioridad,
            'mes' => $mes,
            'anio' => $anio
        ];
        
        echo json_encode($data);
        die();
    }
}
?>