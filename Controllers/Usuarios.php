<?php
class Usuarios extends Controller
{
    private $id_usuario, $correo;
    public function __construct()
    {
        parent::__construct();
        session_start();
        $this->id_usuario = $_SESSION['id'];
        $this->correo = $_SESSION['correo'];
        ##validar session
        if (empty($_SESSION['id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function index()
    {
        $data['title'] = 'Gestion de Usuarios';
        $data['script'] = 'usuarios.js';
        $data['menu'] = 'usuarios';
        $data['shares'] = $this->model->verificarEstado($this->correo);

        require_once 'Models/CalendarioModel.php';
        $calendarioModel = new CalendarioModel();
        $data['docs_pendientes'] = $calendarioModel->contarPendientes($this->id_usuario);

        $this->views->getView('usuarios', 'index', $data);
    }

    public function listar()
    {
        $data = $this->model->getUsuarios();

        for ($i = 0; $i < count($data); $i++) {
            // Generar botones de acciones
            if ($data[$i]['id'] == 1) {
                // Super Admin: sin botones de acción
                $data[$i]['acciones'] = '<div class="d-flex">
                <span class="badge bg-secondary">Protegido</span>
            </div>';
            } else {
                // Usuarios normales: editar y eliminar
                $data[$i]['acciones'] = '<div class="d-flex">
                <button class="btn btn-primary btn-sm" type="button" onclick="editar(' . $data[$i]['id'] . ')"><i class="material-icons">edit</i></button>
                <button class="btn btn-danger btn-sm" type="button" onclick="eliminar(' . $data[$i]['id'] . ')"><i class="material-icons">delete</i></button>
            </div>';
            }

            $data[$i]['nombres'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function guardar()
    {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $clave = $_POST['clave'];
        $rol = $_POST['rol'];
        $id_usuario = $_POST['id_usuario'];
        $id_oficina = !empty($_POST['id_oficina']) ? $_POST['id_oficina'] : null;
        $cargo = $_POST['cargo'];

        if (
            empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) ||
            empty($direccion) || empty($clave) || empty($rol)
        ) {
            $res = array('tipo' => 'warning', 'mensaje' => 'Todos los campos son obligatorios');
        } else {
            if ($id_usuario == '') {
                // CREAR NUEVO USUARIO
                $verificarCorreo = $this->model->getVerificar('correo', $correo, 0);
                if (empty($verificarCorreo)) {
                    $verificarTel = $this->model->getVerificar('telefono', $telefono, 0);
                    if (empty($verificarTel)) {
                        $hash = password_hash($clave, PASSWORD_DEFAULT);
                        $data = $this->model->registrar($nombre, $apellido, $correo, $telefono, $direccion, $hash, $rol, $id_oficina, $cargo);
                        if ($data > 0) {
                            $res = array('tipo' => 'success', 'mensaje' => 'Usuario registrado correctamente');
                        } else {
                            $res = array('tipo' => 'error', 'mensaje' => 'Error al registrar el usuario');
                        }
                    } else {
                        $res = array('tipo' => 'warning', 'mensaje' => 'El telefono ya existe');
                    }
                } else {
                    $res = array('tipo' => 'warning', 'mensaje' => 'El correo ya existe');
                }
            } else {
                // MODIFICAR USUARIO EXISTENTE
                $verificarCorreo = $this->model->getVerificar('correo', $correo, $id_usuario);
                if (empty($verificarCorreo)) {
                    $verificarTel = $this->model->getVerificar('telefono', $telefono, $id_usuario);
                    if (empty($verificarTel)) {
                        $data = $this->model->modificar($nombre, $apellido, $correo, $telefono, $direccion, $rol, $id_oficina, $cargo, $id_usuario);
                        if ($data == 1) {
                            $res = array('tipo' => 'success', 'mensaje' => 'Usuario modificado');
                        } else {
                            $res = array('tipo' => 'error', 'mensaje' => 'Error al modificar');
                        }
                    } else {
                        $res = array('tipo' => 'warning', 'mensaje' => 'El telefono ya existe');
                    }
                } else {
                    $res = array('tipo' => 'warning', 'mensaje' => 'El correo ya existe');
                }
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function delete($id)
    {
        // Proteger Super Admin
        if ($id == 1) {
            $res = array('tipo' => 'error', 'mensaje' => 'No se puede eliminar al Super Admin');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            die();
        }

        $data = $this->model->delete($id);
        if ($data == 1) {
            $res = array('tipo' => 'success', 'mensaje' => 'Usuario eliminado');
        } else {
            $res = array('tipo' => 'error', 'mensaje' => 'Error al eliminar el usuario');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($id)
    {
        $data = $this->model->getUsuario($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function profile()
    {
        $data['title'] = 'Perfil del Usuario';
        $data['script'] = 'profile.js';
        $data['menu'] = 'usuarios';
        $data['usuario'] = $this->model->getUsuario($this->id_usuario);
        $data['shares'] = $this->model->verificarEstado($this->correo);
        $this->views->getView('usuarios', 'perfil', $data);
    }

    public function cambiarPass()
    {
        $actual = $_POST['clave_actual'];
        $nueva = $_POST['clave_nueva'];
        $confirmar = $_POST['clave_confirmar'];
        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            $res = array('tipo' => 'warning', 'mensaje' => 'Todos los campos son requeridos');
        } else {
            if ($nueva != $confirmar) {
                $res = array('tipo' => 'warning', 'mensaje' => 'Las contraseñas no coinciden');
            } else {
                $consulta = $this->model->getUsuario($this->id_usuario);
                if (password_verify($nueva, $consulta['clave'])) {
                    $hash = password_hash($nueva, PASSWORD_DEFAULT);
                    $data = $this->model->cambiarPass($hash, $this->id_usuario);
                    if ($data == 1) {
                        $res = array('tipo' => 'success', 'mensaje' => 'Contraseña modificada');
                    } else {
                        $res = array('tipo' => 'error', 'mensaje' => 'Error al modificar');
                    }
                } else {
                    $res = array('tipo' => 'warning', 'mensaje' => 'Contraseñas actual incorrecta');
                }
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function cambiarProfile()
    {
        $correo = $_POST['correo'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];

        if (empty($correo) || empty($nombre) || empty($apellido) || empty($telefono) || empty($direccion)) {
            $res = array('tipo' => 'warning', 'mensaje' => 'Todos los campos son requeridos');
        } else {
            $usuario = $this->model->getUsuario($this->id_usuario);
            $data = $this->model->modificar($nombre, $apellido, $correo, $telefono, $direccion, $usuario['rol'], $this->id_usuario);
            if ($data == 1) {
                $res = array('tipo' => 'success', 'mensaje' => 'Datos Modificados');
            } else {
                $res = array('tipo' => 'error', 'mensaje' => 'Error al modificar');
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function salir()
    {
        session_destroy();
        header('Location: ' . BASE_URL);
    }

    // Listar oficinas para el formulario
    public function listarOficinas()
    {
        $sql = "SELECT id, nombre FROM oficinas WHERE estado = 1 ORDER BY nombre";
        $data = $this->model->selectAll($sql);
        echo json_encode($data);
    }
}
