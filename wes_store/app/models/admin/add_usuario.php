<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

$input = json_decode(file_get_contents('php://input'), true);

$nombre     = trim($input['nombre'] ?? '');
$apellido   = trim($input['apellido'] ?? '');
$email      = trim($input['email'] ?? '');
$telefono   = trim($input['telefono'] ?? '');
$rol_id     = intval($input['rol_id'] ?? 0);
$estado     = trim($input['estado'] ?? 'Activo');
$password   = $input['password'] ?? '';

if ($nombre === '' || $email === '' || $rol_id <= 0 || $password === '') {
    echo json_encode([
        'ok' => false,
        'message' => 'Los campos Nombre, Email, Rol y Contraseña son obligatorios.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'ok' => false,
        'message' => 'Formato de correo inválido.'
    ]);
    exit;
}

try {
    $pdo = getConnection();

    // Verificar email duplicado
    $stmtChk = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmtChk->execute([$email]);
    if ($stmtChk->fetch()) {
        echo json_encode([
            'ok' => false,
            'message' => 'Ya existe un usuario con este correo.'
        ]);
        exit;
    }

    // Insertar nuevo usuario
    $hashPwd = password_hash($password, PASSWORD_DEFAULT);
    $stmtIns = $pdo->prepare("
        INSERT INTO usuarios 
            (nombre, apellido, email, contrasena, rol_id, telefono, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $ok = $stmtIns->execute([
        $nombre,
        $apellido,
        $email,
        $hashPwd,
        $rol_id,
        $telefono,
        $estado
    ]);

    if ($ok) {
        // Obtener el ID recién insertado
        $nuevoId = intval($pdo->lastInsertId());

        // Obtener datos posteriores al INSERT para la bitácora
        $stmtSel = $pdo->prepare("
            SELECT id_usuario, nombre, apellido, email, rol_id, telefono, estado
            FROM usuarios
            WHERE id_usuario = ?
            LIMIT 1
        ");
        $stmtSel->execute([$nuevoId]);
        $usuarioPosterior = $stmtSel->fetch(PDO::FETCH_ASSOC);

        // Registrar en bitácora
        // Si no hay usuario logueado, pasamos null; de lo contrario, $_SESSION['usuario']['id']
        $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
        registrarBitacora(
            'usuarios',            // tabla_afectada
            $nuevoId,              // id_registro_afectado
            'INSERT',              // tipo_operacion
            $idUsuarioLogueado,    // id_usuario que realiza la acción
            null,                  // info_anterior (no existe antes del INSERT)
            $usuarioPosterior      // info_posterior (datos del usuario recién creado)
        );

        echo json_encode([
            'ok' => true,
            'message' => 'Usuario agregado correctamente.'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'message' => 'Error al agregar el usuario.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
