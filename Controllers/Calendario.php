<?php
class Calendario extends Controller
{
    private $id_usuario;
    
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
    }
    
    // Vista principal del calendario
    public function index()
    {
        $data['title'] = 'Calendario';
        $data['menu'] = 'calendario';
        $data['shares'] = ['total' => 0];
        $data['docs_pendientes'] = $this->model->contarPendientes($this->id_usuario);
        
        $this->views->getView('calendario', 'index', $data);
    }
    
    // Obtener documentos pendientes (para prueba)
    public function listarPendientes()
    {
        
        // Llamar al modelo usando la propiedad
        $data = $this->model->getDocumentosPendientes($this->id_usuario);
        
        echo json_encode($data);
    }

    // Guardar nuevo documento
    public function guardar()
    {
        $numero = $_POST['numero'];
        $asunto = $_POST['asunto'];
        $fecha_limite = $_POST['fecha_limite'];
        $prioridad = $_POST['prioridad'];
        
        $resultado = $this->model->crearDocumento($numero, $asunto, $fecha_limite, $prioridad, $this->id_usuario);
        
        if ($resultado > 0) {
            $res = array('tipo' => 'success', 'mensaje' => 'Documento creado correctamente');
        } else {
            $res = array('tipo' => 'error', 'mensaje' => 'Error al crear documento');
        }
        
        echo json_encode($res);
    }

    // Completar documento
    public function completar()
    {
        $id = $_POST['id'];
        
        $resultado = $this->model->completarDocumento($id);
        
        if ($resultado > 0) {
            $res = array('tipo' => 'success', 'mensaje' => 'Documento completado');
        } else {
            $res = array('tipo' => 'error', 'mensaje' => 'Error al completar documento');
        }
        
        echo json_encode($res);
    }
}
?>