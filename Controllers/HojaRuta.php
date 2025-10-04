<?php
class HojaRuta extends Controller
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

    // Vista principal
    public function index()
    {
        $data['title'] = 'Hojas de Ruta';
        $data['menu'] = 'hoja_ruta';
        $data['shares'] = ['total' => 0];

        // Contar documentos pendientes
        require_once 'Models/CalendarioModel.php';
        $calendarioModel = new CalendarioModel();
        $data['docs_pendientes'] = $calendarioModel->contarPendientes($this->id_usuario);

        $this->views->getView('hoja_ruta', 'index', $data);
    }

    // Obtener oficinas para el formulario
    public function listarOficinas()
    {
        $data = $this->model->getOficinas();
        echo json_encode($data);
    }


    // Listar hojas de ruta
    public function listar($filtro = 'todas')
    {
        $data = $this->model->listarHojasRuta($filtro);
        echo json_encode($data);
    }

    // Obtener siguiente número de registro
    public function obtenerNumeroRegistro()
    {
        $numero = $this->model->generarNumeroRegistro();
        echo json_encode(['numero' => $numero]);
    }

    // Obtener detalle de una hoja de ruta
    public function obtenerDetalle($id)
    {
        $sql = "SELECT hr.*, o.nombre as oficina_destino
                FROM hojas_ruta hr
                LEFT JOIN oficinas o ON hr.id_oficina_destino = o.id
                WHERE hr.id = $id";

        $data = $this->model->select($sql);
        echo json_encode($data);
    }

    public function guardarYDelegarDirecto()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Método no permitido']);
            return;
        }

        $numero_registro = $_POST['numero_registro'];
        $fecha_recepcion = $_POST['fecha_recepcion'];
        $remitente = $_POST['remitente'];
        $asunto = $_POST['asunto'];
        $oficina_destino = $_POST['oficina_destino'];
        $fecha_limite = $_POST['fecha_limite'];
        $prioridad = $_POST['prioridad'];
        $observaciones = $_POST['observaciones'];

        if (empty($numero_registro) || empty($asunto) || empty($oficina_destino) || empty($fecha_limite)) {
            echo json_encode(['tipo' => 'warning', 'mensaje' => 'Complete todos los campos obligatorios']);
            return;
        }

        // Procesar archivo
        $archivo_adjunto = null;

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
            $archivo = $_FILES['archivo'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

            if (strtolower($extension) !== 'pdf') {
                echo json_encode(['tipo' => 'warning', 'mensaje' => 'Solo se permiten archivos PDF']);
                return;
            }

            $nombre_archivo = time() . '_' . $numero_registro . '.pdf';
            $ruta_destino = 'Assets/documentos_oficiales/en_proceso/' . $nombre_archivo;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                $archivo_adjunto = $nombre_archivo;
            } else {
                echo json_encode(['tipo' => 'error', 'mensaje' => 'Error al subir el archivo']);
                return;
            }
        } else {
            echo json_encode(['tipo' => 'warning', 'mensaje' => 'Debe adjuntar un archivo PDF']);
            return;
        }

        // Guardar en hojas_ruta
        $datos = array(
            $numero_registro,
            $fecha_recepcion,
            $remitente,
            $asunto,
            $oficina_destino,
            $fecha_limite,
            $prioridad,
            $observaciones,
            $this->id_usuario,
            $archivo_adjunto
        );

        try {
            $resultado = $this->model->crearHojaRuta($datos);

            if ($resultado > 0) {
                // Crear tarea en calendario
                require_once 'Models/CalendarioModel.php';
                $calendarioModel = new CalendarioModel();
                
                $sqlCal = "INSERT INTO documentos_oficiales 
                    (numero_documento, asunto, fecha_recepcion, fecha_limite, prioridad, id_carpeta, id_oficina_destino) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $datosCal = array(
                    $numero_registro,
                    $asunto,
                    $fecha_recepcion,
                    $fecha_limite,
                    $prioridad,
                    1,
                    $oficina_destino
                );
                
                $calendarioModel->insertar($sqlCal, $datosCal);
                
                echo json_encode(['tipo' => 'success', 'mensaje' => 'Hoja de Ruta creada y delegada correctamente']);
            } else {
                echo json_encode(['tipo' => 'error', 'mensaje' => 'Error al crear Hoja de Ruta']);
            }
        } catch (Exception $e) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
}
    

