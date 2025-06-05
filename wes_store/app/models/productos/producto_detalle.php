<?php
// Usa una ruta RELATIVA para requerir la conexión
require_once __DIR__ . '/../sql/conexion.php';

header('Content-Type: application/json');

// Validación del parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no válido o no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

try {
    $pdo = getConnection();

    // Consulta para obtener los detalles del producto con su categoría y marca
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre_categoria, m.nombre_marca
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id_categoria
        LEFT JOIN marcas m ON p.marca_id = m.id_marca
        WHERE p.id_producto = ?
    ");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
        exit;
    }

    // Consulta para obtener comentarios aprobados del producto
    $stmt = $pdo->prepare("
        SELECT cp.comentario, cp.calificacion, u.nombre AS nombre_usuario
        FROM comentarios_productos cp
        INNER JOIN usuarios u ON cp.usuario_id = u.id_usuario
        WHERE cp.producto_id = ? AND cp.aprobado = 1
        ORDER BY cp.creado_en DESC
    ");
    $stmt->execute([$id]);
    $comentarios = $stmt->fetchAll();

    // Respuesta exitosa con producto y comentarios
    echo json_encode([
        'status' => 'success',
        'data' => [
            'producto' => $producto,
            'comentarios' => $comentarios
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión o consulta en el servidor'
    ]);
}
