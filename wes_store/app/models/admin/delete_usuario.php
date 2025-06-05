<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

$input = json_decode(file_get_contents('php://input'), true);
$id    = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        'ok' => false,
        'message' => 'ID de usuario inválido.'
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

    // 2) Eliminar al usuario
    $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $ok = $stmtDel->execute([$id]);

    if ($ok) {
        // 3) Registrar en la bitácora
        $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
        registrarBitacora(
            'usuarios',            // tabla_afectada
            $id,                   // id_registro_afectado
            'DELETE',              // tipo_operacion
            $idUsuarioLogueado,    // id_usuario que realiza la acción
            $usuarioAnterior,      // info_anterior (datos antes del DELETE)
            null                   // info_posterior (no existe tras el DELETE)
        );

        echo json_encode([
            'ok' => true,
            'message' => 'Usuario eliminado correctamente.'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'message' => 'Error al eliminar el usuario.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
