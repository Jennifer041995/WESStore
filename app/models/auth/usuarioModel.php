<?php
require_once __DIR__ . '/../sql/conexion.php';

class UsuarioModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    public function crearUsuario($nombre, $apellido, $email, $password, $rol_id = 2) {
        // Rol 2 = Cliente por defecto
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, apellido, email, contrasena, rol_id) 
                VALUES (:nombre, :apellido, :email, :contrasena, :rol_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre'     => $nombre,
            ':apellido'   => $apellido,
            ':email'      => $email,
            ':contrasena' => $hash,
            ':rol_id'     => $rol_id
        ]);
    }

    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}
