<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Asegúrate de la ruta correcta

class UsuarioModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * Crea un usuario y registra la acción en la bitácora.
     * @return int|false  ID del usuario recién creado, o false si falla.
     */
    public function crearUsuario($nombre, $apellido, $email, $password, $rol_id = 2) {
        // 1) Generar hash de la contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 2) Insertar usuario
        $sql = "INSERT INTO usuarios (nombre, apellido, email, contrasena, rol_id) 
                VALUES (:nombre, :apellido, :email, :contrasena, :rol_id)";
        $stmt = $this->pdo->prepare($sql);

        // Ejecutar y comprobar
        if (!$stmt->execute([
            ':nombre'     => $nombre,
            ':apellido'   => $apellido,
            ':email'      => $email,
            ':contrasena' => $hash,
            ':rol_id'     => $rol_id
        ])) {
            return false;
        }

        // 3) Obtener el ID recién insertado
        $nuevoId = intval($this->pdo->lastInsertId());

        // 4) Obtener datos posteriores (para auditar)
        $stmt2 = $this->pdo->prepare("SELECT id_usuario, nombre, apellido, email, rol_id, estado FROM usuarios WHERE id_usuario = :id");
        $stmt2->execute([':id' => $nuevoId]);
        $usuarioPosterior = $stmt2->fetch(PDO::FETCH_ASSOC);

        // 5) Registrar en la bitácora (INSERT en 'usuarios')
        registrarBitacora(
            'usuarios',           // tabla afectada
            $nuevoId,             // id_registro_afectado
            'INSERT',             // tipo de operación
            null,                 // id_usuario (no hay sesión todavía)
            null,                 // info_anterior
            $usuarioPosterior     // info_posterior
        );

        // 6) Devolver el nuevo ID
        return $nuevoId;
    }

    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}
