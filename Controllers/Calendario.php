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

            // ========================================
            // CORRECCIÓN 1: DETECTAR SI HAY RETRASO
            // ========================================
            $enRetraso = false;
            if (!empty($documento['fecha_limite']) && $documento['sin_limite'] == 0) {
                $fechaLimite = strtotime($documento['fecha_limite']);
                $ahora = time();
                $enRetraso = ($ahora > $fechaLimite);
            }

            // ========================================
            // CORRECCIÓN 2: GENERAR NOMBRE CORRECTO
            // Formato: RES-Asunto_Limpio-ABREV-001.pdf
            // ========================================
            // Obtener datos de oficina y usuario
            $sqlUsuario = "SELECT id_oficina FROM usuarios WHERE id = {$this->id_usuario}";
            $usuario = $this->model->select($sqlUsuario);

            $sqlOficina = "SELECT abreviatura FROM oficinas WHERE id = {$usuario['id_oficina']}";
            $oficina = $this->model->select($sqlOficina);
            $abrev = $oficina['abreviatura'] ?? 'SIN';

            // Limpiar asunto (quitar caracteres especiales, máximo 30 caracteres)
            $asuntoLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $documento['asunto']);
            $asuntoLimpio = substr($asuntoLimpio, 0, 30);

            // Generar número correlativo por oficina
            $sqlCount = "SELECT COUNT(*) as total FROM documentos_respondidos
                         WHERE id_oficina_respondio = {$usuario['id_oficina']}";
            $count = $this->model->select($sqlCount);
            $numero = str_pad(($count['total'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);

            $nombreRespuesta = "RES-{$asuntoLimpio}-{$abrev}-{$numero}.pdf";

            // Rutas
            $rutaEnProceso = 'Assets/documentos_oficiales/en_proceso/';
            $rutaCompletados = 'Assets/documentos_oficiales/completados/';

            // Crear carpeta completados si no existe
            if (!file_exists($rutaCompletados)) {
                mkdir($rutaCompletados, 0777, true);
            }

            // 1. Mover archivo original de en_proceso a completados
            $archivoOriginal = $rutaEnProceso . 'HR-' . str_pad($documento['numero_documento'], 3, '0', STR_PAD_LEFT) . '.pdf';
            $archivoDestino = $rutaCompletados . 'HR-' . str_pad($documento['numero_documento'], 3, '0', STR_PAD_LEFT) . '.pdf';

            if (file_exists($archivoOriginal)) {
                if (!rename($archivoOriginal, $archivoDestino)) {
                    // Si falla el rename, continuar (puede que ya esté movido)
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
                $id_carpeta_respondidos = $this->model->obtenerCarpetaRespondidos($this->id_usuario);

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
                    $this->model->registrarArchivoRespondido($id_carpeta_respondidos, $nombreRespuesta, $this->id_usuario);
                }
            }

            // ========================================
            // CORRECCIÓN 3: ESTADO SEGÚN RETRASO
            // ========================================
            $estado_final = $enRetraso ? 'respondido_retraso' : 'completado';

            // 4. Actualizar estado en documentos_oficiales
            $data = $this->model->completarTarea($id_documento, $nombreRespuesta, $comentarios, $this->id_usuario, $estado_final);

            if ($data != 1) {
                $res = array('tipo' => 'error', 'mensaje' => 'Error al actualizar el estado');
                echo json_encode($res);
                die();
            }

            // ========================================
            // CORRECCIÓN 4: REGISTRAR EN documentos_respondidos
            // ========================================
            $sqlInsertResp = "INSERT INTO documentos_respondidos
                              (id_documento, archivo_respuesta, id_oficina_respondio,
                               id_usuario_respondio, fecha_respuesta, observaciones)
                              VALUES (?, ?, ?, ?, NOW(), ?)";

            $datosResp = array(
                $id_documento,
                $nombreRespuesta,
                $usuario['id_oficina'],
                $this->id_usuario,
                $comentarios
            );

            $this->model->insertar($sqlInsertResp, $datosResp);

            // ========================================
            // CORRECCIÓN 5: ACTUALIZAR hojas_ruta SI SE ARCHIVA
            // ========================================
            if ($archivar == '1') {
                $sqlUpdateHR = "UPDATE hojas_ruta
                                SET estado = 'archivado', fecha_completado = NOW()
                                WHERE numero_registro = ?";
                $this->model->save($sqlUpdateHR, array($documento['numero_documento']));
            }

            // Respuesta exitosa
            $mensaje = $archivar == '1'
                ? 'Tarea completada y archivada en Respondidos'
                : 'Tarea completada exitosamente';

            if ($enRetraso) {
                $mensaje .= ' (Registrada con retraso)';
            }

            $res = array('tipo' => 'success', 'mensaje' => $mensaje);
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
}
