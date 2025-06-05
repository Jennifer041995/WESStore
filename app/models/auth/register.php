<?php
session_start();
require_once __DIR__ . '/usuarioModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$model = new UsuarioModel();

// Validaciones básicas
if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre, email y contraseña son obligatorios']);
    exit;
}

try {
    // Verifico que no exista el email
    if ($model->obtenerPorEmail($data['email'])) {
        http_response_code(409);
        echo json_encode(['error' => 'El email ya está registrado']);
        exit;
    }

     $model->crearUsuario(
        $data['nombre'],
        $data['apellido']   ?? '',
        $data['email'],
        $data['password']
    );

    // Auto-login tras registro
    $usuario = $model->obtenerPorEmail($data['email']); // Sospechoso 1
    $_SESSION['usuario'] = [ // Sospechoso 2: Manipulación de sesión
        'id'    => $usuario['id_usuario'],
        'nombre'=> $usuario['nombre'],
        'rol_id'=> $usuario['rol_id']
    ];

    echo json_encode(['success' => true]); // Si llega aquí sin excepción, es exitoso.
} catch (Exception $e) { // La excepción se lanza en algún punto de arriba
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor']);
}