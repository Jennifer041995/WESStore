<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['ok' => false, 'message' => 'Usuario no autenticado']);
    exit;
}
$userId = (int) $_SESSION['usuario']['id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['ok' => false, 'message' => 'Datos inválidos']);
    exit;
}

$oldPass = trim($input['old_password'] ?? '');
$newPass = trim($input['new_password'] ?? '');
$confirm = trim($input['confirm_password'] ?? '');

// Validaciones
if ($oldPass === '' || $newPass === '' || $confirm === '') {
    echo json_encode(['ok' => false, 'message' => 'Complete todos los campos']);
    exit;
}
if ($newPass !== $confirm) {
    echo json_encode(['ok' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden']);
    exit;
}
if (strlen($newPass) < 6) {
    echo json_encode(['ok' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    $pdo = getConnection();
    // 1) Obtener hash actual
    $stmt1 = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = ?");
    $stmt1->execute([$userId]);
    $row = $stmt1->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $hashActual = $row['contrasena'];
    if (!password_verify($oldPass, $hashActual)) {
        echo json_encode(['ok' => false, 'message' => 'Contraseña actual incorrecta']);
        exit;
    }

    // 2) Guardar nueva contraseña
    $nuevoHash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt2 = $pdo->prepare("
      UPDATE usuarios SET
        contrasena = :ph,
        actualizado_en = NOW()
      WHERE id_usuario = :uid
    ");
    $stmt2->execute([':ph' => $nuevoHash, ':uid' => $userId]);

    echo json_encode(['ok' => true, 'message' => 'Contraseña actualizada con éxito']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
