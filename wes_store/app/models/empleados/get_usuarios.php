<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

try {
    $pdo = getConnection();

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        // Si piden un ID específico y queremos que además tenga rol = 2
        $stmt = $pdo->prepare("
            SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.email,
                u.telefono,
                r.nombre_rol,
                u.estado
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id_rol
            WHERE u.id_usuario = :id_usuario
              AND u.rol_id = 2
              AND (u.estado = 'activo' OR u.estado = 'inactivo')
        ");
        $stmt->execute([':id_usuario' => intval($_GET['id'])]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            echo json_encode(['ok' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado o no tiene rol = 2.']);
        }
    } else {
        // Retorna todos los usuarios cuyo rol sea 2
        $stmt = $pdo->query("
            SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.email,
                u.telefono,
                r.nombre_rol,
                u.estado
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id_rol
            WHERE u.rol_id = 2
              AND (u.estado = 'activo' OR u.estado = 'inactivo')
            ORDER BY u.id_usuario ASC
        ");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuarios);
    }
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
