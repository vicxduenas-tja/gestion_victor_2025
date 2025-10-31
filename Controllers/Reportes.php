<?php
class Reportes extends Controller
{
    private $id_usuario;
    private $id_oficina;
    private $rol;
    protected $calendario_model; // <--- CORRECCIÓN: Declaración

    public function __construct()
    {
        // --- Carga Manual en Orden Correcto ---
        require_once 'Config/App/Views.php'; 
        $this->views = new Views(); 
        
        require_once 'Config/App/Conexion.php'; 
        require_once 'Config/App/Query.php'; 
        
        require_once 'Models/ReportesModel.php'; 
        $this->model = new ReportesModel();
        
        require_once 'Models/CalendarioModel.php';
        $this->calendario_model = new CalendarioModel(); // <--- Instanciación
        // --- Fin de la Carga Manual ---
        
        session_start();

        if (empty($_SESSION['id'])) {
            header('Location: '.BASE_URL);
            exit;
        }

        $this->id_usuario = $_SESSION['id'];
        $this->id_oficina = $_SESSION['id_oficina'] ?? null;
        $this->rol = $_SESSION['rol'];
    }

    // Vista principal del Dashboard (Widgets)
    public function index()
    {
        $data['title'] = 'Reportes y Estadísticas';
        $data['menu'] = 'reportes'; 
        
        // Insignia del menú
        $data['docs_pendientes'] = $this->calendario_model->contarPendientes(
            $this->id_usuario, $this->rol, $this->id_oficina
        );

        // Para los widgets
        $data['en_progreso'] = $this->model->contarDocumentos('en_progreso', $this->rol, $this->id_oficina);
        $data['vencidos'] = $this->model->contarDocumentos('vencidos', $this->rol, $this->id_oficina);
        $data['completados_mes'] = $this->model->contarDocumentos('completados_mes', 1); 
        $data['conocimiento_mes'] = $this->model->contarDocumentos('conocimiento_mes', 1);
        $data['retrasos_mes'] = $this->model->contarDocumentos('retrasos_mes', 1);

        $this->views->getView('reportes', 'index', $data);
    }

    // Endpoint AJAX para el reporte detallado
    public function generarReporteDetallado()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $desde = $_POST['fecha_desde'] ?? date('Y-m-d');
            $hasta = $_POST['fecha_hasta'] ?? date('Y-m-d');
            $estado = $_POST['filtro_estado'] ?? 'todos';

            if ($desde > $hasta) {
                echo json_encode(['error' => 'La fecha de inicio no puede ser mayor a la fecha final.']);
                die();
            }

            $data = $this->model->getReporteDetallado($desde, $hasta, $estado);
            echo json_encode($data);
            die();
        }
    }

    // Vista y lógica para el Reporte de Productividad (Relevo)
    public function productividad()
    {
        $data['title'] = 'Reporte de Productividad';
        $data['menu'] = 'reportes'; 
        
        $data['productividad'] = $this->model->getReporteProductividad();

        // Obtener el inventario de documentos activos para el relevo
        $data['inventario'] = $this->model->getInventarioActivo($this->id_oficina);

        // Para la insignia del menú
        $data['docs_pendientes'] = $this->calendario_model->contarPendientes(
            $this->id_usuario, $this->rol, $this->id_oficina
        );

        $this->views->getView('reportes', 'productividad', $data);
    }
    
    // Endpoint AJAX para obtener el detalle de respuestas de un usuario específico
    public function getDetalleRespuestas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
            $id_usuario = intval($_POST['id_usuario']);

            $data = $this->model->getDetalleDocumentosRespondidos($id_usuario);
            echo json_encode($data);
            die();
        }
    }
}
?>