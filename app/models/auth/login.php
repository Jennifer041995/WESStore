<?php
session_start();
require_once __DIR__ . '/usuarioModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$model = new UsuarioModel();

if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y contraseña son obligatorios']);
    exit;
}

$user = $model->obtenerPorEmail($data['email']);

// ¡NUEVO! La contraseña recibida ($data['password']) es el hash SHA-256 del cliente.
// password_verify() ahora comparará este hash SHA-256 con el hash bcrypt guardado en la DB.
// Esto funcionará si el hash bcrypt en la DB fue generado a partir del SHA-256.
if ($user && password_verify($data['password'], $user['contrasena'])) {
    // Guardar datos en sesión
    $_SESSION['usuario'] = [
        'id'    => $user['id_usuario'],
        'nombre'=> $user['nombre'],
        'rol_id'=> $user['rol_id']
    ];
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
}