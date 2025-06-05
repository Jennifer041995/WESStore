<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

// Sesión: $_SESSION['usuario']['id'] contiene el id
if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['ok' => false, 'message' => 'Usuario no autenticado']);
    exit;
}
$userId = (int) $_SESSION['usuario']['id'];

try {
    $pdo = getConnection();

    // 1) Datos básicos del usuario (sin incluir "estado")
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.telefono,
            u.ultimo_login,
            r.nombre_rol
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id_rol
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    // 2) Obtener dirección principal
    $stmt2 = $pdo->prepare("
        SELECT 
            du.id_direccion_usuario,
            du.alias,
            du.direccion,
            du.ciudad,
            du.departamento,
            du.codigo_postal,
            du.pais
        FROM direcciones_usuarios du
        WHERE du.usuario_id = ? AND du.principal = 1
        LIMIT 1
    ");
    $stmt2->execute([$userId]);
    $direccion = $stmt2->fetch(PDO::FETCH_ASSOC) ?: null;

    echo json_encode([
        'ok'        => true,
        'usuario'   => $user,
        'direccion' => $direccion
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'ok'      => false,
        'message' => 'Error en servidor: ' . $e->getMessage()
    ]);
}
