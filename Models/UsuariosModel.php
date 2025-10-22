<?php
class UsuariosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getUsuarios()
    {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.direccion, u.clave, u.rol, u.perfil, u.fecha, 
                       u.id_oficina, u.cargo, o.nombre as nombre_oficina
        FROM usuarios u
        LEFT JOIN oficinas o ON u.id_oficina = o.id
        WHERE u.estado = 1";
        return $this->selectAll($sql);
    }


    public function getVerificar($item, $nombre, $id)
    {
        if ($id > 0) {
            $sql = "SELECT id 
            FROM usuarios 
            WHERE $item = '$nombre' 
            AND id != $id 
            AND estado = 1";
        } else {
            $sql = "SELECT id 
            FROM usuarios 
            WHERE $item = '$nombre' 
            AND estado = 1";
        }
        return $this->select($sql);
    }



    public function registrar($nombre, $apellido, $correo, $telefono, $direccion, $clave, $rol, $id_oficina, $cargo)
    {
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, telefono, direccion, clave, rol, id_oficina, cargo, fecha_alta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $datos = array($nombre, $apellido, $correo, $telefono, $direccion, $clave, $rol, $id_oficina, $cargo);
        return $this->insertar($sql, $datos);
    }

    public function delete($id)
    {
        $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
        $datos = array(0, $id);
        return $this->save($sql, $datos);
    }

    public function getUsuario($id)
    {
        $sql = "SELECT id, nombre, apellido, correo, telefono, direccion, clave, rol, perfil, fecha 
        FROM usuarios 
        WHERE id = $id";
        return $this->select($sql);
    }

    public function modificar($nombre, $apellido, $correo, $telefono, $direccion, $rol, $id_oficina, $cargo, $id)
    {
        $sql = "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=?, direccion=?, rol=?, id_oficina=?, cargo=? WHERE id = ?";
        $datos = array($nombre, $apellido, $correo, $telefono, $direccion, $rol, $id_oficina, $cargo, $id);
        return $this->save($sql, $datos);
    }

    ####ver total archivos compartidos
    public function verificarEstado($correo)
    {
        $sql = "SELECT COUNT(id) AS total
        FROM detalle_archivos
        WHERE correo = '$correo'
        AND estado = 1";
        return $this->select($sql);
    }

    public function cambiarPass($clave, $id)
    {
        $sql = "UPDATE usuarios SET clave=? WHERE id = ?";
        $datos = array($clave, $id);
        return $this->save($sql, $datos);
    }

    public function crearCarpetaRespondidos($id_usuario)
    {
        $sql = "INSERT INTO carpetas (nombre, id_usuario, fecha_create) VALUES ('Respondidos', ?, NOW())";
        $datos = array($id_usuario);
        return $this->insertar($sql, $datos);
    }
}
