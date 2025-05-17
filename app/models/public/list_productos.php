<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/connection.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query("
        SELECT p.id, p.nombre, p.precio,
               IFNULL(i.url_imagen, 'media/img/default.png') AS url_imagen
        FROM productos p
        LEFT JOIN imagenes_productos i ON i.producto_id = p.id
        ORDER BY p.id DESC LIMIT 6
    ");
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
