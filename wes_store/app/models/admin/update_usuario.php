<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

$input = json_decode(file_get_contents('php://input'), true);

$id         = intval($input['id'] ?? 0);
$nombre     = trim($input['nombre'] ?? '');
$apellido   = trim($input['apellido'] ?? '');
$email      = trim($input['email'] ?? '');
$telefono   = trim($input['telefono'] ?? '');
$rol_id     = intval($input['rol_id'] ?? 0);
$estado     = trim($input['estado'] ?? '');

if ($id <= 0 || $nombre === '' || $email === '' || $rol_id <= 0 || $estado === '') {
    echo json_encode([
        'ok' => false,
        'message' => 'ID, Nombre, Email, Rol y Estado son obligatorios.'
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

    // 1) Obtener datos previos para la bitácora
    $stmtPrev = $pdo->prepare("
        SELECT id_usuario, nombre, apellido, email, telefono, rol_id, estado
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ");
    $stmtPrev->execute([$id]);
    $usuarioAnterior = $stmtPrev->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioAnterior) {
        echo json_encode([
            'ok' => false,
            'message' => 'El usuario no existe.'
        ]);
        exit;
    }

    // 2) Verificar email duplicado en otro usuario
    $stmtChk = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ?");
    $stmtChk->execute([$email, $id]);
    if ($stmtChk->fetch()) {
        echo json_encode([
            'ok' => false,
            'message' => 'El correo ya está en uso por otro usuario.'
        ]);
        exit;
    }

    // 3) Actualizar datos (sin cambiar contraseña)
    $stmtUpd = $pdo->prepare("
        UPDATE usuarios
           SET nombre   = ?,
               apellido = ?,
               email    = ?,
               telefono = ?,
               rol_id   = ?,
               estado   = ?
         WHERE id_usuario = ?
    ");
    $ok = $stmtUpd->execute([
        $nombre,
        $apellido,
        $email,
        $telefono,
        $rol_id,
        $estado,
        $id
    ]);

    if ($ok) {
        // 4) Obtener datos posteriores para la bitácora
        $stmtPost = $pdo->prepare("
            SELECT id_usuario, nombre, apellido, email, telefono, rol_id, estado
            FROM usuarios
            WHERE id_usuario = ?
            LIMIT 1
        ");
        $stmtPost->execute([$id]);
        $usuarioPosterior = $stmtPost->fetch(PDO::FETCH_ASSOC);

        // 5) Registrar en la bitácora
        $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
        registrarBitacora(
            'usuarios',
            $id,
            'UPDATE',
            $idUsuarioLogueado,
            $usuarioAnterior,
            $usuarioPosterior
        );

        echo json_encode([
            'ok' => true,
            'message' => 'Usuario actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'message' => 'Error al actualizar el usuario.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
