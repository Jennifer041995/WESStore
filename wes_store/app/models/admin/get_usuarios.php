<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

$pdo = getConnection();

// Si se pasa id por GET => devolvemos solo ese usuario
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.telefono,
            u.estado,
            u.rol_id,
            r.nombre_rol
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id_rol
        WHERE u.id_usuario = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        echo json_encode([
            'ok' => true,
            'usuario' => $usuario
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'message' => 'Usuario no encontrado.'
        ]);
    }
    exit;
}

// Si no se pasa id => devolvemos listado completo
try {
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.telefono,
            u.estado,
            u.rol_id,
            r.nombre_rol
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id_rol
        ORDER BY u.id_usuario DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} catch (Exception $e) {
    echo json_encode([]);
}