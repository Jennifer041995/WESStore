<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php';

class UsuarioModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * Crea un usuario y registra la acción en la bitácora.
     * $password ahora es el hash SHA-256 recibido del cliente.
     * @return int|false   ID del usuario recién creado, o false si falla.
     */
    public function crearUsuario($nombre, $apellido, $email, $password_sha256_cliente, $rol_id = 2) { // <- Cambia el nombre del parámetro para claridad
        // ¡NUEVO! El $password_sha256_cliente es el hash SHA-256 que viene del cliente.
        // HASHENAMOS ESTE HASH SHA-256 con PASSWORD_DEFAULT (bcrypt) para almacenarlo de forma segura.
        $hashSeguroParaDB = password_hash($password_sha256_cliente, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, apellido, email, contrasena, rol_id) 
                VALUES (:nombre, :apellido, :email, :contrasena, :rol_id)";
        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute([
            ':nombre'     => $nombre,
            ':apellido'   => $apellido,
            ':email'      => $email,
            ':contrasena' => $hashSeguroParaDB, // ¡Almacenamos el hash bcrypt del SHA-256!
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
         return intval($this->pdo->lastInsertId());
    }

    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}
