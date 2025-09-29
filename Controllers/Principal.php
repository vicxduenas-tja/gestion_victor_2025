<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Principal extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
    }
    public function index()
    {
        $data['title'] = 'Iniciar sesion';
        $this->views->getView('principal', 'index', $data);
    }


    ### login ###
    public function validar()
    {
        $correo = $_POST['correo'];
        $clave = $_POST['clave'];
        $data = $this->model->getUsuario($correo);

        if (!empty($data)) {
            if (password_verify($clave, $data['clave'])) {
                $_SESSION['id'] = $data['id'];
                $_SESSION['correo'] = $data['correo'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['rol'] = $data['rol'];
                $res = array('tipo' => 'success', 'mensaje' => 'Bienvenido al sistema gestor de archivos');
            } else {
                $res = array('tipo' => 'warning', 'mensaje' => 'La contraseña es incorrecta');
            }
        } else {
            $res = array('tipo' => 'warning', 'mensaje' => 'El correo no existe');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    ##resetar clave
    public function enviarCorreo($correo)
    {
        $consulta = $this->model->getUsuario($correo);
        if (!empty($consulta)) {
            $mail = new PHPMailer(true);

            try {
                $token = md5(date('YmdHis'));
                $this->model->updateToken($token,$correo);
                //Server settings
                $mail->SMTPDebug = 0;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'vicxduenas@gmail.com';                     //SMTP username
                $mail->Password   = 'maorpsjzpovimffr';                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('vicxduenas@gmail.com', 'Victor Sullcata');
                $mail->addAddress($correo);     //Add a recipient

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Restablecer contraseña';
                $mail->Body    = 'Has pedido restablecer contraseña, si no has sido tu, omite este mensaje</b><a href="' . BASE_URL . 'principal/reset/'.$token.'">Click aqui para cambiar</a>';

                $mail->send();
                $res = array('tipo' => 'success', 'mensaje' => 'Correo enviado con un token de seguridad');
            } catch (Exception $e) {
                $res = array('tipo' => 'success', 'mensaje' => $mail->ErrorInfo);
            }
        } else {
            $res = array('tipo' => 'warning', 'mensaje' => 'El correo no existe');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();

    }

    public function reset($token){
        $data['title'] = 'Restablecer clave';
        $data['usuario'] = $this->model->getToken($token);
        if ($data['usuario']['token'] == $token) {
            $this->views->getView('principal', 'reset', $data);
        }else{
            header('Location: ' . BASE_URL . 'errors');
        }
    }
    
        public function cambiarPass()
    {
        $nueva = $_POST['clave_nueva'];
        $confirmar = $_POST['clave_confirmar'];
        $token = $_POST['token'];
        if (empty($nueva) || empty($confirmar) || empty($token)) {
            $res = array('tipo' => 'warning', 'mensaje' => 'Todos los campos son requeridos');
        } else {
            if ($nueva != $confirmar) {
                $res = array('tipo' => 'warning', 'mensaje' => 'Las contraseñas no coinciden');
            } else {
                $result = $this->model->getToken($token);
                if ($token == $result['token']) {
                    $hash = password_hash($nueva, PASSWORD_DEFAULT);
                    $data = $this->model->cambiarPass($hash, $token);
                    if ($data == 1) {
                        $res = array('tipo' => 'success', 'mensaje' => 'Contraseña Restablecida');
                    } else {
                        $res = array('tipo' => 'error', 'mensaje' => 'Error al restablecer la contraseña');
                    }
                }             
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }




}
