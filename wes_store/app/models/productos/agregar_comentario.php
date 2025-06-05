<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../sql/conexion.php';
$pdo = getConnection();

header('Content-Type: application/json; charset=utf-8');

$producto_id  = $_POST['producto_id']   ?? null;
$comentario   = trim($_POST['comentario'] ?? '');
$calificacion = $_POST['calificacion'] ?? null;
$usuario_id   = $_SESSION['usuario']['id'] ?? null;

if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Autenticación requerida.']);
    exit;
}
if (!$producto_id || !$comentario || !$calificacion) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}
$cal = (int)$calificacion;
if ($cal < 1 || $cal > 5) {
    echo json_encode(['success' => false, 'message' => 'Calificación fuera de rango.']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO comentarios_productos
        (usuario_id, producto_id, comentario, calificacion, aprobado)
    VALUES (?, ?, ?, ?, 1)
");
$success = $stmt->execute([$usuario_id, $producto_id, $comentario, $cal]);
echo json_encode(['success' => $success]);
