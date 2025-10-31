<?php
class Calendario extends Controller
{
    private $id_usuario;
    private $id_oficina;
    private $rol;

    public function __construct()
    {
        // Carga manual de dependencias (evita errores de orden)
        $this->views = new Views();
        require_once 'Config/App/Conexion.php';
        require_once 'Config/App/Query.php';
        require_once 'Models/CalendarioModel.php';
        $this->model = new CalendarioModel();

        session_start();

        // Validar sesión
        if (empty($_SESSION['id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->id_usuario = $_SESSION['id'];
        $this->id_oficina = $_SESSION['id_oficina'] ?? null;
        $this->rol = $_SESSION['rol'];
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

    // Registrar visualización (cambiar estado a 'en_progreso')
    public function registrarVisualizacion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];

            // Verificar que el documento existe y está en estado 'delegado'
            $documento = $this->model->getDocumento($id);

            if (empty($documento)) {
                $res = array('tipo' => 'error', 'mensaje' => 'Documento no encontrado');
                echo json_encode($res);
                die();
            }

            // Solo actualizar si está en 'delegado'
            if ($documento['estado'] === 'delegado') {
                $data = $this->model->registrarVisualizacion($id);

                if ($data == 1) {
                    $res = array('tipo' => 'success', 'mensaje' => 'Visualización registrada');
                } else {
                    $res = array('tipo' => 'error', 'mensaje' => 'Error al registrar visualización');
                }
            } else {
                $res = array('tipo' => 'info', 'mensaje' => 'Ya fue visualizado anteriormente');
            }

            echo json_encode($res);
            die();
        }
    }

    // Completar tarea
    public function completarTarea()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = $_POST['id_documento'];
            $comentarios = $_POST['comentarios'] ?? '';
            $archivar = $_POST['archivar'] ?? '0';

            // Validar que exista el documento
            $documento = $this->model->getDocumento($id_documento);

            if (empty($documento)) {
                $res = array('tipo' => 'error', 'mensaje' => 'Documento no encontrado');
                echo json_encode($res);
                die();
            }

            // Validar que se subió archivo
            if (!isset($_FILES['archivo_respuesta']) || $_FILES['archivo_respuesta']['error'] !== UPLOAD_ERR_OK) {
                $res = array('tipo' => 'error', 'mensaje' => 'Debe adjuntar el archivo de respuesta');
                echo json_encode($res);
                die();
            }

            $archivo = $_FILES['archivo_respuesta'];

            // Validar tipo de archivo (PDF)
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (strtolower($extension) !== 'pdf') {
                $res = array('tipo' => 'error', 'mensaje' => 'El archivo debe ser PDF');
                echo json_encode($res);
                die();
            }

            // Generar nombre único para archivo de respuesta
            $nombreRespuesta = 'RESP_' . $documento['numero_documento'] . '.pdf';

            // Rutas
            $rutaEnProceso = 'Assets/documentos_oficiales/en_proceso/';
            $rutaCompletados = 'Assets/documentos_oficiales/completados/';

            // Crear carpeta completados si no existe
            if (!file_exists($rutaCompletados)) {
                mkdir($rutaCompletados, 0777, true);
            }

            // 1. Mover archivo original de en_proceso a completados
            $archivoOriginal = $rutaEnProceso . $documento['numero_documento'] . '.pdf';
            $archivoDestino = $rutaCompletados . $documento['numero_documento'] . '.pdf';

            if (file_exists($archivoOriginal)) {
                if (!rename($archivoOriginal, $archivoDestino)) {
                    $res = array('tipo' => 'error', 'mensaje' => 'Error al mover archivo original');
                    echo json_encode($res);
                    die();
                }
            }

            // 2. Guardar archivo de respuesta en completados
            $rutaRespuesta = $rutaCompletados . $nombreRespuesta;
            if (!move_uploaded_file($archivo['tmp_name'], $rutaRespuesta)) {
                $res = array('tipo' => 'error', 'mensaje' => 'Error al guardar archivo de respuesta');
                echo json_encode($res);
                die();
            }

            // 3. Si se archiva, copiar a carpeta "Respondidos" del usuario
            if ($archivar == '1') {
                $id_carpeta_respondidos = $this->model->obtenerCarpetaRespondidos($_SESSION['id']);

                if ($id_carpeta_respondidos) {
                    // Ruta de la carpeta en Adm. de Archivos
                    $rutaArchivosCarpeta = 'Assets/archivos/' . $id_carpeta_respondidos . '/';

                    // Crear carpeta si no existe
                    if (!file_exists($rutaArchivosCarpeta)) {
                        mkdir($rutaArchivosCarpeta, 0777, true);
                    }

                    // Copiar archivo de respuesta
                    copy($rutaRespuesta, $rutaArchivosCarpeta . $nombreRespuesta);

                    // Registrar en la tabla archivos
                    $this->model->registrarArchivoRespondido($id_carpeta_respondidos, $nombreRespuesta, $_SESSION['id']);
                }
            }

            // 4. Actualizar estado en BD
            $estado_final = $archivar == '1' ? 'archivado' : 'completado';
            $data = $this->model->completarTarea($id_documento, $nombreRespuesta, $comentarios, $_SESSION['id'], $estado_final);

            if ($data == 1) {
                $mensaje = $archivar == '1' ? 'Tarea completada y archivada en Respondidos' : 'Tarea completada exitosamente';
                $res = array('tipo' => 'success', 'mensaje' => $mensaje);
            } else {
                $res = array('tipo' => 'error', 'mensaje' => 'Error al actualizar el estado');
            }

            echo json_encode($res);
            die();
        }
    }

    public function buscarArchivoPDF($numero, $carpeta)
    {
        $ruta = 'Assets/documentos_oficiales/' . $carpeta . '/';
        $archivos = glob($ruta . '*_' . $numero . '.pdf');

        if (!empty($archivos)) {
            $archivo = basename($archivos[0]);
            echo json_encode(['archivo' => $archivo]);
        } else {
            echo json_encode(['archivo' => false]);
        }
    }

    /**
     * Devuelve una lista de tareas y P.C. que no han sido visualizados
     * Usado para el sistema de notificaciones
     */
    public function listarNotificaciones()
    {
        $data = $this->model->getNotificaciones($this->id_usuario, $this->id_oficina);
        echo json_encode($data);
        die();
    }
}
