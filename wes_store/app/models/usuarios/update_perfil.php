<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

// Validar sesión
if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['ok' => false, 'message' => 'Usuario no autenticado']);
    exit;
}
$userId = (int) $_SESSION['usuario']['id'];

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['ok' => false, 'message' => 'Datos inválidos']);
    exit;
}

$nombre   = trim($input['nombre']   ?? '');
$apellido = trim($input['apellido'] ?? '');
$email    = trim($input['email']    ?? '');
$telefono = trim($input['telefono'] ?? '');

// Validaciones
if ($nombre === '' || $email === '') {
    echo json_encode(['ok' => false, 'message' => 'Nombre y correo son obligatorios']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Correo inválido']);
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
      UPDATE usuarios SET
        nombre = :nombre,
        apellido = :apellido,
        email = :email,
        telefono = :telefono,
        actualizado_en = NOW()
      WHERE id_usuario = :uid
    ");
    $stmt->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':email'    => $email,
        ':telefono' => $telefono,
        ':uid'      => $userId
    ]);

    echo json_encode(['ok' => true, 'message' => 'Información personal actualizada']);
} catch (PDOException $e) {
    echo json_encode([
        'ok'      => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
