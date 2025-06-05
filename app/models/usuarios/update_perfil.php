<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Para registrar en bitácora

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

    // 1) Obtener info anterior del usuario
    $stmtOld = $pdo->prepare("SELECT nombre, apellido, email, telefono FROM usuarios WHERE id_usuario = ?");
    $stmtOld->execute([$userId]);
    $oldRow = $stmtOld->fetch(PDO::FETCH_ASSOC);
    if (!$oldRow) {
        echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    $infoAnterior = [
        'id_usuario' => $userId,
        'nombre'     => $oldRow['nombre'],
        'apellido'   => $oldRow['apellido'],
        'email'      => $oldRow['email'],
        'telefono'   => $oldRow['telefono']
    ];

    // 2) Ejecutar actualización
    $stmt = $pdo->prepare("
      UPDATE usuarios SET
        nombre        = :nombre,
        apellido      = :apellido,
        email         = :email,
        telefono      = :telefono,
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

    // 3) Construir info posterior
    $infoPosterior = [
        'id_usuario' => $userId,
        'nombre'     => $nombre,
        'apellido'   => $apellido,
        'email'      => $email,
        'telefono'   => $telefono
    ];

    // 4) Registrar en bitácora
    registrarBitacora(
        'usuarios',
        $userId,
        'UPDATE',
        $userId,
        $infoAnterior,
        $infoPosterior
    );

    echo json_encode(['ok' => true, 'message' => 'Información personal actualizada']);
} catch (PDOException $e) {
    echo json_encode([
        'ok'      => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
